FROM node:18-alpine as build-stage
WORKDIR /app
# Installation des dépendances
COPY package*.json ./
RUN npm ci --only=production
# Build de l'application
COPY . .
RUN npm run build
FROM nginx:alpine as production-stage
COPY --from=build-stage /app/dist /usr/share/nginx/html
# Configuration Nginx personnalisée
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]