FROM node:20-alpine as dev-stage

WORKDIR /app

COPY package.json yarn.lock ./
RUN yarn install

EXPOSE 8080

CMD ["sh", "-c", "yarn install && exec yarn dev"]

# Estágio de build
FROM node:20-alpine as build-stage

ARG VITE_API_BASE_URL
ARG VITE_API_VERSION
ENV VITE_API_BASE_URL=$VITE_API_BASE_URL
ENV VITE_API_VERSION=$VITE_API_VERSION

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
