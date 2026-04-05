# Projeto financeiro

Monorepo com API (PHP/Lumen), frontend (Vite) e MySQL, orquestrados pelo Docker Compose.

## Pré-requisitos

- [Docker Engine](https://docs.docker.com/engine/install/) e [Docker Compose](https://docs.docker.com/compose/install/) (v2+)

## Subir o ambiente

Na raiz do repositório:

```bash
docker compose up -d
```

## Subir o ambiente com build (1a vez)

Na raiz do repositório:

```bash
docker compose up -d --build
```

Na primeira execução a imagem da API e do app são construídas; isso pode levar alguns minutos.

Para acompanhar os logs:

```bash
docker compose logs -f
```

Para parar os containers:

```bash
docker compose down
```

Para parar e remover volumes (apaga dados do MySQL):

```bash
docker compose down -v
```

## Serviços e portas

| Serviço      | Descrição              | URL / porta                          |
|-------------|-------------------------|--------------------------------------|
| **app**     | Frontend (Vite, dev)    | http://localhost:8080              |
| **nginx_api** | API via Nginx         | http://localhost:8099              |
| **mysql**   | Banco de dados          | `localhost:3307` (usuário `root`)   |

O frontend está configurado para chamar a API em `http://localhost:8099` (variáveis `VITE_API_BASE_URL` e `VITE_API_VERSION` no `docker-compose.yml`).

## Banco de dados

Credenciais padrão do MySQL no Compose:

- **Host** (de fora do Docker): `127.0.0.1` ou `localhost`, porta **3307**
- **Host** (de dentro da rede Docker, ex.: API): serviço `mysql`, porta **3306**
- **Usuário**: `root`
- **Senha**: `1234` (padrão)
- **Database**: `home` (padrão)

Para personalizar senha e nome do banco na criação do container, crie um arquivo `.env` na **raiz** do repositório (opcional):

```env
DB_PASSWORD=sua_senha
DB_DATABASE=nome_do_banco
```

Ajuste também `apps/financeiro_api/.env` (`DB_PASSWORD`, `DB_DATABASE`, etc.) para bater com esses valores.

## API: dependências e migrações

O volume `api_vendor` mantém o `vendor` da API dentro do container. Se precisar reinstalar dependências PHP:

```bash
docker compose exec api composer install
```

Se o projeto usar migrações Artisan/Lumen:

```bash
docker compose exec api php artisan migrate
```

(Consulte a documentação da API em `apps/financeiro_api/README.md` se houver passos extras.)

## Estrutura relevante

- `docker-compose.yml` — orquestração dos serviços
- `apps/financeiro_api/` — backend
- `apps/financeiro.app/` — frontend
