FROM node:22-alpine AS deps

WORKDIR /app

COPY package*.json ./
RUN npm ci

FROM node:22-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM node:22-alpine

WORKDIR /app

RUN npm install -g next

COPY package*.json ./
RUN npm ci --production

COPY --from=builder /app/.next /app/.next
COPY --from=builder /app/public /app/public
COPY . .

EXPOSE 3000

CMD ["npm", "start"]
