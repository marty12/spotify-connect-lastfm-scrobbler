FROM nginx

ADD nginx.conf /etc/nginx/conf.d/default.conf

RUN usermod -u 1000 www-data

CMD ["nginx", "-g", "daemon off;"]

WORKDIR /var/www/html

EXPOSE 80
