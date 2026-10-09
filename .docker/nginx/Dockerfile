FROM nginx:1.27.2-alpine AS web
RUN rm /etc/nginx/conf.d/default.conf
COPY ./.docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80