# Stage 1: Build Vue 3 static assets
FROM node:22-alpine AS builder

WORKDIR /app

# Copy package descriptors
COPY package*.json ./

# Install npm dependencies
RUN npm install --legacy-peer-deps

# Copy application source
COPY . .

# Build production SPA assets to dist/
RUN npm run build

# Stage 2: Minimal Alpine sync container (copies files to shared volume)
FROM alpine:latest

WORKDIR /app

# Copy built files into the container image
COPY --from=builder /app/dist /dist

# On start, sync all static assets to shared Nginx volume and finish
CMD ["sh", "-c", "echo '==> Syncing frontend assets to shared volume...' && cp -r /dist/* /usr/share/nginx/html/ && echo '==> Frontend assets ready!'"]

