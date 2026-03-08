# Estágio de build
FROM node:20-alpine as build-stage

WORKDIR /app

COPY package.json yarn.lock ./
RUN yarn install

COPY . .
RUN yarn build

# Estágio de produção
FROM nginx:stable-alpine as production-stage

# Copia os arquivos compilados
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copia configuração personalizada do nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expõe a porta 80
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
