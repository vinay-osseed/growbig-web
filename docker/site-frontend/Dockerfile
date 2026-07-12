FROM node:22-alpine AS build

WORKDIR /app

COPY site-frontend/package*.json ./
RUN npm ci

COPY site-frontend/ ./

ARG VITE_API_BASE_URL=https://api.growbigllp.com
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

RUN npm run build

FROM nginx:1.27-alpine

COPY docker/site-frontend/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html
