# 🐛 Xdebug + Docker + PhpStorm — Guia Completo

> Configuração passo a passo para debugar PHP rodando em container Docker diretamente pelo PhpStorm (ou qualquer IDE JetBrains).

---

## Como funciona

```
PhpStorm (porta 9003) ◄──── Xdebug (dentro do container)
        │                              │
   Ouve conexões               Ao receber requisição,
   de debug                    conecta no host via
                               host.docker.internal
```

O Xdebug roda **dentro do container** e se conecta **de volta** para o PhpStorm na sua máquina. Por isso o PhpStorm precisa estar **ouvindo** antes de você fazer a requisição.

---

## Parte 1 — Configuração do Docker

### 1.1 Dockerfile — Instalar o Xdebug

Adicione o bloco abaixo no seu `Dockerfile`, **após** instalar as extensões PHP normais:

```dockerfile
# Instala e configura Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

> **Por que `host.docker.internal`?**
> É o hostname especial que o Docker usa para apontar para a máquina host (seu Windows/Mac/Linux). O Xdebug precisa saber o IP do PhpStorm para se conectar.

---

### 1.2 docker-compose.yml — Expor o host para o container

No serviço da API, adicione `extra_hosts` e as variáveis de ambiente:

```yaml
services:
  api:
    # ... suas configurações existentes ...
    environment:
      APP_ENV: local
      XDEBUG_MODE: debug          # ativa o modo debug
      XDEBUG_SESSION: PHPSTORM    # identifica a sessão
    extra_hosts:
      - "host.docker.internal:host-gateway"   # necessário no Linux
```

> **Nota:** No Windows e Mac o `host.docker.internal` já funciona por padrão. O `host-gateway` é necessário apenas no Linux.

---

### 1.3 Rebuild e subir os containers

```bash
# Na raiz do projeto (onde está o docker-compose.yml)
docker-compose down
docker-compose build --no-cache api
docker-compose up -d
```

### 1.4 Verificar se o Xdebug está ativo

```bash
docker exec financeiro_api sh -c "php -i | grep 'xdebug.mode'"
```

Saída esperada:
```
xdebug.mode => debug => debug
```

---

## Parte 2 — Configuração do PhpStorm

### 2.1 Configurar o Servidor PHP com Path Mapping

> **Este é o passo mais crítico.** O PhpStorm precisa saber que o código em `apps/financeiro_api/` na sua máquina corresponde a `/var/www` dentro do container.

1. Abra `File > Settings` (ou `Ctrl+Alt+S`)
2. Navegue até **PHP → Servers**
3. Clique em **`+`** para adicionar um novo servidor
4. Preencha os campos:

| Campo | Valor |
|-------|-------|
| Name | `financeiro_api` |
| Host | `localhost` |
| Port | `5082` |
| Debugger | `Xdebug` |

5. Marque a opção **☑ Use path mappings**
6. Na tabela que aparecer, localize a pasta raiz do projeto e defina:

| Pasta local | Caminho no servidor |
|-------------|---------------------|
| `…/apps/financeiro_api` | `/var/www` |

7. Clique em **OK**

---

### 2.2 Criar a Run/Debug Configuration

1. Vá em `Run > Edit Configurations` (ou clique no dropdown de configurações no topo)
2. Clique em **`+`** → selecione **PHP Remote Debug**
3. Preencha:

| Campo | Valor |
|-------|-------|
| Name | `Docker Xdebug` |
| Server | `financeiro_api` *(criado no passo 2.1)* |
| IDE key | `PHPSTORM` |

4. Clique em **OK**

---

### 2.3 Ativar o Listener no PhpStorm

Antes de fazer qualquer requisição, ative o listener:

- Clique no ícone de **telefone** 📞 na barra superior direita do PhpStorm
- Ou use o atalho: `Run > Start Listening for PHP Debug Connections`

> O ícone deve ficar **verde/ativo**. Enquanto estiver ativo, o PhpStorm aceita conexões do Xdebug na porta `9003`.

---

## Parte 3 — Usar o Debug

### Fluxo completo

```
1. Abrir o PhpStorm na pasta apps/financeiro_api
2. Ativar o listener (ícone de telefone verde)
3. Colocar breakpoint no controller/service desejado
4. Fazer a requisição (Postman, browser, frontend)
5. PhpStorm para automaticamente no breakpoint ✅
```

### Colocar um breakpoint

- Clique na **margem esquerda** da linha desejada no editor
- Um círculo vermelho 🔴 aparece indicando o breakpoint

---

## Parte 4 — Solução de Problemas

### ❌ PhpStorm não para no breakpoint

| Causa | Solução |
|-------|---------|
| Listener não está ativo | Verifique se o ícone de telefone está verde |
| Path mapping errado | Confirme que `apps/financeiro_api` → `/var/www` |
| Xdebug não instalado | Execute `docker exec financeiro_api sh -c "php -m \| grep xdebug"` |
| Porta 9003 bloqueada | Verifique o firewall do Windows — libere a porta 9003 |
| IDE key diferente | Confirme `xdebug.idekey=PHPSTORM` no container |

### ❌ `php -r` trava no terminal do container

Isso é **normal** quando o Xdebug está com `start_with_request=yes` e o listener não está ativo — ele fica aguardando a conexão. Significa que o Xdebug está funcionando.

Para executar comandos PHP no container sem travar, use:

```bash
docker exec financeiro_api sh -c "XDEBUG_MODE=off php -r 'echo PHP_VERSION;'"
```

### ❌ Xdebug some após recriar o container

Se o Xdebug foi instalado manualmente (sem rebuild da imagem), ele é perdido ao recriar o container. Para tornar permanente, certifique-se de que o `Dockerfile` tem o bloco de instalação do Xdebug e faça o rebuild:

```bash
docker-compose build api
docker-compose up -d --force-recreate api
```

---

## Referência rápida — Arquivos modificados

| Arquivo | O que foi feito |
|---------|-----------------|
| `apps/financeiro_api/Dockerfile` | Instalação do Xdebug via `pecl` + configuração inline |
| `docker-compose.yml` | `extra_hosts` + variáveis `XDEBUG_MODE` e `XDEBUG_SESSION` |

---

## Configuração do Xdebug (resumo)

```ini
zend_extension=xdebug

[xdebug]
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.idekey=PHPSTORM
xdebug.log_level=0
```

| Diretiva | Significado |
|----------|-------------|
| `mode=debug` | Ativa o modo step-debug |
| `start_with_request=yes` | Inicia debug em **toda** requisição automaticamente |
| `client_host` | IP/host do PhpStorm (sua máquina) |
| `client_port` | Porta que o PhpStorm escuta (padrão Xdebug 3) |
| `idekey` | Identificador da sessão de debug |
| `log_level=0` | Sem logs verbosos do Xdebug |
