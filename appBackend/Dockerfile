# Use the official PHP image
FROM php:8.1-cli

# Set working directory
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install project dependencies using Composer
RUN composer install

# Expose port 8000 for the PHP built-in server
EXPOSE 8000

# Run the PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "."]
