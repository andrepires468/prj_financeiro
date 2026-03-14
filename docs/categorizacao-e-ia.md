# Categorização: Manual e Automática com IA

Este documento descreve em detalhe como funciona a categorização dos lançamentos do extrato, com ênfase na **categorização automática via IA (Gemini)**.

---

## 1. Conceito de categorização

Cada lançamento do extrato (`Extrato`) pode ter uma categoria associada (`IdCategoria`). A categoria define o tipo de gasto ou receita (ex.: Alimentação, Transporte, Salário). O sistema usa categorias para:

- Exibir gráficos por categoria no mês e ao longo dos meses.
- Calcular totais mensais por categoria (`FechamentoCategoria`).

Itens sem categoria (`IdCategoria` nulo) aparecem na lista como "não classificados" e são os candidatos à categorização manual ou automática.

---

## 2. Categorização manual

### Onde ocorre

- Na tela principal (Financeiro), na lista do extrato do mês selecionado.
- No **modal de edição** do lançamento: o usuário escolhe uma categoria em um multiselect (na prática, seleção única) e pode opcionalmente editar a descrição personalizada.

### Fluxo técnico

1. **Frontend** (`Financeiro.vue`): ao abrir o modal com um item do extrato, carrega a lista de categorias (`CategoriaLista`) e o item selecionado (`ExtratoSel`). No `ModalExtrato.vue`, ao salvar, emite `@updateExtrato` com os dados alterados.
2. **Chamada**: `PUT /financeiro/update-extrato` com `IdExtrato`, `IdCategoria` (opcional) e `DescricaoPersonalizada` (opcional).
3. **Backend** (`FinanceiroService::UpdateExtrato`):
   - Localiza o registro em `Extrato` pelo `IdExtrato`.
   - Atualiza `IdCategoria` e `DescricaoPersonalizada`.
   - Se a categoria foi alterada (`$categoria_ant != $categoria_atual`), chama `ProcessService::procFechamentoCategoria()` para a categoria antiga e para a nova, usando a data base do mês do lançamento, garantindo que os totais do mês e os gráficos fiquem corretos.

Assim, toda alteração manual de categoria dispara o recálculo do fechamento por categoria daquele mês.

---

## 3. Categorização automática com IA

A categorização automática usa a **API Gemini** (Google) para sugerir uma categoria para cada lançamento ainda sem categoria no mês selecionado. O sistema monta um prompt com as categorias disponíveis, exemplos do histórico e os itens a categorizar; a IA responde com um JSON que é validado e aplicado no banco.

### 3.1 Quando aparece e como disparar

- **Condição**: há pelo menos um item no extrato do mês **sem** categoria.
- **Interface**: na tela Financeiro, um alerta amarelo exibe "Restam X item(s) para classificar" e o botão **"Categorizar com IA"**. O botão fica desabilitado se não houver mês selecionado ou se a requisição já estiver em andamento (`isCategorizandoIA`).
- **Ação**: ao clicar, o frontend chama `processService.categorizarExtrato({ mes: CalendarioSel.Mes, ano: CalendarioSel.Ano })`, que envia `POST /process/categorizar-extrato` com `mes` e `ano` no body.

### 3.2 Requisitos no backend

- **Variáveis de ambiente** (`.env` da API):
  - `GEMINI_API_KEY`: obrigatória para a categorização com IA. Sem ela, o `GeminiService` lança exceção ao ser instanciado/usado.
  - `GEMINI_MODEL`: opcional; padrão `gemini-2.5-flash`. Pode ser alterado em caso de cota (ex.: `gemini-1.5-flash`).
- Obtenção da chave: [Google AI Studio](https://aistudio.google.com/apikey). Limites: [Gemini API rate limits](https://ai.google.dev/gemini-api/docs/rate-limits). Consultar consumo: [Uso da API (últimos 28 dias)](https://aistudio.google.com/u/1/usage?project=gen-lang-client-0143854051&timeRange=last-28-days).

### 3.3 Fluxo no backend (`ProcessService::CategorizarExtrato`)

Resumo do que a API faz ao receber `POST /process/categorizar-extrato` com `mes` e `ano`:

1. **Validação**: exige `mes` e `ano`; usa `idBancoExtrato` do contexto (usuário/conta).
2. **Categorias**: busca todas as categorias (`Categoria::select('Id','CategoriaNome')`) para montar a lista no prompt e validar os IDs retornados pela IA.
3. **Histórico para contexto**: busca transações **já categorizadas** dos **últimos 3 meses**, do mesmo `IdBancoExtrato`, limit 300, ordenadas por data decrescente. Campos usados: `Descricao`, `CategoriaNome` (via join com `Categoria`). Objetivo: dar à IA exemplos do tipo "Descricao X → Categoria Y".
4. **Itens a categorizar**: no **mês selecionado** (`mes`/`ano`), itens com `IdCategoria` nulo, mesmo `IdBancoExtrato`. Campos: `Id`, `Descricao`. Se não houver nenhum, retorna `['atualizados' => 0]` sem chamar a IA.
5. **Montagem do prompt**:
   - Lista de categorias no formato `Id - CategoriaNome`.
   - Exemplos no formato `Descricao: ... -> Categoria: ...`.
   - Lista de itens no formato `Id: ..., Descricao: ...`.
   - Instrução para responder **somente** com um JSON: array de objetos com chaves `Id` (número) e `IdCategoria` (número), ex.: `[{"Id":123,"IdCategoria":1},{"Id":124,"IdCategoria":2}]`.
6. **Chamada à IA**: `GeminiService::generateContent($prompt)`:
   - URL: `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key=...`
   - Corpo: `contents[].parts[].text` = prompt; `generationConfig`: `temperature` 0.1, `maxOutputTokens` 8192, `responseMimeType` `application/json`.
   - Retorno: texto da resposta (JSON esperado).
7. **Tratamento de erros da Gemini**: exceções (rede, cota, chave inválida, etc.) são capturadas e relançadas com mensagem amigável (ex.: orientação sobre cota e `GEMINI_MODEL`).
8. **Parse da resposta**: `parseGeminiCategorizacaoResponse($jsonText)`:
   - Remove possíveis blocos markdown (```json ... ```).
   - Localiza o primeiro `[` e o último `]`; se não houver `]`, trata resposta truncada fechando o array.
   - Decodifica o JSON e retorna array de sugestões; se inválido, retorna `null` e o serviço lança exceção com trecho da resposta para debug.
9. **Aplicação no banco**: para cada objeto no array retornado:
   - Obtém `Id` e `IdCategoria` (aceita tanto camelCase quanto PascalCase).
   - Ignora se o `Id` não estiver na lista de itens enviados ou se `IdCategoria` não for um ID de categoria válido.
   - Busca o registro em `Extrato` pelo `Id` e `IdBancoExtrato`, atualiza `IdCategoria`, salva.
   - Chama `procFechamentoCategoria($idCategoria, $dataBase)` para o mês do lançamento, atualizando o total da categoria.
10. **Retorno**: `['atualizados' => N]`, onde N é a quantidade de itens efetivamente atualizados.

### 3.4 Serviço Gemini (`GeminiService`)

- **Responsabilidade**: enviar um prompt de texto à API Gemini e devolver o texto da resposta.
- **Configuração**: `GEMINI_API_KEY` e `GEMINI_MODEL` lidos no construtor; sem chave, exceção ao construir.
- **Parâmetros de geração**: temperatura baixa (0.1) para respostas estáveis; `responseMimeType: application/json` para incentivar JSON puro.
- **Erros**: respostas com `error` ou HTTP 4xx são tratadas; mensagens que indicam cota/rate limit são reformatadas para orientar o usuário (ver README da API e `formatGeminiErrorMessage`).
- **HTTP**: usa cURL se disponível, senão `file_get_contents` com stream context; timeout 60 s, SSL verificado.

### 3.5 Pós-processamento no frontend

Após sucesso em `categorizarExtrato`:

- O frontend chama novamente a listagem do extrato (`getExtrato`) e atualiza os gráficos do mês e da categoria (`getChartCategoriasMes`, `getChartCategoriaUltimosMeses`).
- Exibe um toast informando quantos itens foram categorizados (ou que não havia itens para categorizar).

### 3.6 Comportamentos importantes

- **Só itens sem categoria**: apenas lançamentos com `IdCategoria` nulo no mês são enviados e alterados.
- **Histórico limitado**: últimos 3 meses e 300 registros, para manter o prompt dentro do tamanho aceito pelo modelo e evitar custo/latência excessivos.
- **Validação rigorosa**: IDs devem pertencer ao conjunto enviado (itens do mês) e ao conjunto de categorias existentes; respostas malformadas ou parciais são descartadas ou tratadas (truncamento com fechamento de `]`).
- **Fechamento por categoria**: cada item categorizado pela IA dispara o recálculo do fechamento da categoria naquele mês, mantendo gráficos e totais consistentes com a categorização manual.

---

## 4. Resumo rápido

| Aspecto | Manual | Automática (IA) |
|--------|--------|------------------|
| Disparo | Usuário edita item no modal e salva | Botão "Categorizar com IA" no mês com itens sem categoria |
| Endpoint | `PUT /financeiro/update-extrato` | `POST /process/categorizar-extrato` |
| Escopo | Um lançamento por vez | Todos os itens sem categoria do mês |
| Fechamento | Recalcula as duas categorias (antiga e nova) do mês | Recalcula cada categoria afetada para o mês |
| Dependência externa | Nenhuma | Gemini API (`GEMINI_API_KEY`, opcionalmente `GEMINI_MODEL`) |

A categorização automática com IA complementa a manual: o usuário pode primeiro rodar "Categorizar com IA" para o mês e depois ajustar manualmente os casos em que a sugestão não for a desejada.
