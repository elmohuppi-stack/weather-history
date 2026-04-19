# Vue.js Development Dockerfile
FROM node:18-alpine AS build

# Set working directory
WORKDIR /app

# Make build-time env available to Vite
ARG VITE_API_BASE_URL=http://localhost:8000/api
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

# Copy package files and PrimeVue patch helper first for caching
COPY vue-frontend/package*.json ./
COPY vue-frontend/patch-primevue.js ./

# Install dependencies
RUN npm install --no-audit --progress=false

# Copy source code
COPY vue-frontend .

# Build the application
RUN node patch-primevue.js && npm run build

# Production stage
FROM nginx:alpine

# Copy built files from build stage
COPY --from=build /app/dist /usr/share/nginx/html

# Copy nginx configuration
COPY docker/development/nginx/vue.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
