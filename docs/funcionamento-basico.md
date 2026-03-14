# Funcionamento Básico do Sistema Financeiro

Este documento descreve a arquitetura e o fluxo geral do sistema de controle financeiro.

## Visão geral

O sistema é composto por duas aplicações:

- **financeiro.app** — frontend em Vue.js que consome a API.
- **financeiro_api** — API em PHP (Lumen) que processa dados, integra com e-mail (IMAP) e com a API Gemini para categorização automática.

O usuário autenticado visualiza extratos por mês, categoriza lançamentos, sincroniza extrato via e-mail (Nubank) e pode usar IA para categorizar itens em lote.

---

## Arquitetura

```
[Usuário] → [financeiro.app (Vue)] → [financeiro_api (Lumen)]
                                           ↓
                                    [Banco de dados]
                                           ↓
                                    [Gmail IMAP]  (sincronizar extrato)
                                    [Gemini API]  (categorizar com IA)
```

- **Autenticação**: login/refresh/logout via `AuthController`; rotas protegidas com middleware `auth:api`.
- **Dados financeiros**: extrato, categorias, fechamentos e gráficos via `FinanceiroController`, `CategoriaController` e `ProcessController`.

---

## Principais entidades

| Entidade | Descrição |
|----------|-----------|
| **BancoExtrato** | Configuração do extrato (ex.: conta Nubank), inclui credenciais Gmail para importação. |
| **Extrato** | Lançamento individual: Data, Valor, Descricao, Identificador, IdCategoria, IdBancoExtrato. |
| **Categoria** | Categoria para classificação (ex.: Alimentação, Transporte), com nome, ícone e cor. |
| **Fechamento** | Saldo fechado por mês (DataBase) por conta. |
| **FechamentoCategoria** | Total por categoria por mês (DataBase), usado nos gráficos. |

---

## Fluxos principais

### 1. Sincronizar extrato (importar do e-mail)

- **Frontend**: botão "Sincronizar Extrato" chama `processService.updateExtrato()` (que na verdade usa o endpoint `POST /process/import-extrato`).
- **Backend**: `ProcessController@ImportExtrato` → `ProcessService::ImportExtrato()`.
- **Processo**: conexão IMAP no Gmail, busca e-mails não lidos do Nubank com anexo CSV do extrato; para cada linha do CSV, se o `Identificador` ainda não existir no banco, cria um novo registro em `Extrato` (sem categoria). Ao final, executa `Fechamento()` para recalcular saldos mensais.

### 2. Listar extrato do mês

- **Frontend**: ao selecionar mês/ano no calendário e aplicar filtro, chama serviços que usam `GET /financeiro/lista-extrato?mes=&ano=` (com paginação opcional).
- **Backend**: `FinanceiroController@ListaExtrato` → `FinanceiroService::ListaExtrato()` — retorna itens do mês com join em `Categoria` (nome, ícone, cor) e indicação de tipo (entrada/saída).

### 3. Editar lançamento (categoria e descrição)

- **Frontend**: modal de extrato permite alterar categoria e descrição personalizada; ao salvar chama `PUT /financeiro/update-extrato`.
- **Backend**: `FinanceiroController@UpdateExtrato` → `FinanceiroService::UpdateExtrato()` — atualiza `IdCategoria` e `DescricaoPersonalizada`; se a categoria mudou, recalcula `FechamentoCategoria` da categoria antiga e da nova para o mês do lançamento.

### 4. Categorizar com IA

- **Frontend**: botão "Categorizar com IA" (visível quando há itens sem categoria no mês) chama `processService.categorizarExtrato({ mes, ano })` → `POST /process/categorizar-extrato`.
- **Backend**: `ProcessController@CategorizarExtrato` → `ProcessService::CategorizarExtrato()` — usa histórico de transações já categorizadas e lista de categorias para montar um prompt e chamar o **Gemini**; a resposta (JSON com Id + IdCategoria) é validada e aplicada nos registros de `Extrato`, e o fechamento por categoria do mês é atualizado. Detalhes em [Categorização e IA](./categorizacao-e-ia.md).

### 5. Fechamentos e gráficos

- **Fechamento geral**: `ProcessService::Fechamento()` percorre os meses desde o início configurado até o mês atual e persiste o saldo fechado em `Fechamento` por `DataBase` e conta.
- **Fechamento por categoria**: `procFechamentoCategoria($idCategoria, $dataBase)` soma os valores do extrato daquela categoria no mês e grava em `FechamentoCategoria`. É chamado após importação, após categorizar com IA e ao alterar categoria de um lançamento.
- **Gráficos**: `GET /financeiro/chart-categorias-mes`, `chart-categoria-ultimos-meses`, `chart-ultimos-meses` leem de `FechamentoCategoria` e `Fechamento` para montar os dados dos gráficos no frontend.

---

## Rotas da API (resumo)

- **Auth**: `POST /auth/login`, `GET /auth/refresh`, `POST /auth/logout` (com middleware auth).
- **Financeiro**: `GET /financeiro/calendario-extrato`, `GET /financeiro/lista-extrato`, `PUT /financeiro/update-extrato`, `GET /financeiro/chart-*`.
- **Categoria**: `GET /categoria/list`, `GET /categoria/{id}`, `POST /categoria`, `PUT /categoria`, `DELETE /categoria`.
- **Processo**: `POST /process/import-extrato`, `POST /process/categorizar-extrato`, `POST /process/fechamento`, `POST /process/fechamento-categoria`, `POST /process/fechamento-todas-categorias`.

---

## Configuração relevante

- **API**: variáveis em `.env` (ex.: `MES_INICIO`, `ANO_INICIO` para o range de meses; `GEMINI_API_KEY` e `GEMINI_MODEL` para categorização com IA). Banco configurado via variáveis de conexão do Lumen.
- **App**: base URL da API e endpoints em `api.endpoints` e no helper de chamadas HTTP.

Para detalhes sobre categorização manual e automática com IA, consulte [Categorização e IA](./categorizacao-e-ia.md).
