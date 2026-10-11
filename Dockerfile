FROM php:8.3-apache

# Install mysqli dan ekstensi PHP yang dibutuhkan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Set document root ke /var/www/html
WORKDIR /var/www/html

# Copy semua file project
COPY . .

# Konfigurasi Apache agar bisa baca .htaccess
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Port yang dipakai Railway
EXPOSE 80

CMD ["apache2-foreground"]
