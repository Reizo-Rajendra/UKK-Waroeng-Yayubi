FROM php:8.3-cli

# Install ekstensi PHP yang dibutuhkan (mysqli, pdo_mysql, gd, dll)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy semua file project
COPY . .

# Port yang dipakai Railway
EXPOSE 8080

# Jalankan PHP built-in server (tidak perlu Apache)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app"]
