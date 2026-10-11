FROM php:8.3-cli

# Install ekstensi PHP yang dibutuhkan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy semua file project
COPY . .

# Railway otomatis set env PORT, kita baca via shell script
EXPOSE 8080

# Jalankan PHP built-in server di port yang Railway tentukan
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /app"]
