FROM php:8.1-apache
# Copiar todos tus archivos PHP al servidor
COPY . /var/www/html/
# Dar permisos para que Apache pueda leerlos
RUN chown -R www-data:www-data /var/www/html
# Exponer el puerto 80
EXPOSE 80