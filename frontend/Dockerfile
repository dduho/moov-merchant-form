FROM node:18-alpine

WORKDIR /app

# Installation des dépendances
COPY package*.json ./
RUN npm install

# Copier le code source
COPY . .

# Exposer le port
EXPOSE 3000

# Commande pour le développement
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "3000"]