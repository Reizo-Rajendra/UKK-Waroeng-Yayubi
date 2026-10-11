FROM php:8.3-apache

# Fix Apache MPM conflict — disable event & worker, pakai prefork (kompatibel PHP)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# Install mysqli dan ekstensi PHP yang dibutuhkan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Konfigurasi Apache agar AllowOverride berjalan
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set document root
WORKDIR /var/www/html

# Copy semua file project
COPY . .

# Pastikan permission benar
RUN chown -R www-data:www-data /var/www/html

# Port Railway
EXPOSE 80

CMD ["apache2-foreground"]
