## financeiro.api

### Variáveis de ambiente

- **GEMINI_API_KEY** (obrigatório para IA): API key do Google Gemini para o botão "Categorizar com IA". Obtenha em [Google AI Studio](https://aistudio.google.com/apikey) e adicione no `.env` da API. Se não configurada, ao clicar em "Categorizar com IA" a API retornará erro informando.
- **GEMINI_MODEL** (opcional): Nome do modelo Gemini. Padrão: `gemini-2.5-flash`. Se aparecer erro de cota (quota exceeded), tente `gemini-1.5-flash` ou confira os limites em [Gemini API rate limits](https://ai.google.dev/gemini-api/docs/rate-limits).
- **Consultar consumo da API Gemini**: [Uso / consumo (últimos 28 dias)](https://aistudio.google.com/u/1/usage?project=gen-lang-client-0143854051&timeRange=last-28-days).

### Construir as imagens

```
docker-compose build
```

### Iniciar os containers em background

```
docker-compose up -ddocker-compose up -d
```

### Verificar se os containers estão rodando

```
docker-compose ps
```

### Para parar a aplicação:

```
docker-compose down
```

### Para ver os logs:

```
docker-compose logs -f
```

