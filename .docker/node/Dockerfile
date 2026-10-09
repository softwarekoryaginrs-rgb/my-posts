FROM node:alpine AS node
WORKDIR /var/www/app

COPY package.json package-lock.json ./
RUN npm ci

COPY rspack.config.js tsconfig.json ./
COPY assets ./assets

CMD ["npm", "run", "watch"]
