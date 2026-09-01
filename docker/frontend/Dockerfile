FROM node:22-alpine

WORKDIR /app

# Copy package descriptors
COPY package*.json ./

# Install npm dependencies with legacy peer deps protection
RUN npm install --legacy-peer-deps

# Copy application source
COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
