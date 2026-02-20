FROM thecodingmachine/php:8.4-v4-fpm

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar configuración personalizada de PHP-FPM
COPY docker/php/zzz-custom.conf /etc/php/8.4/fpm/pool.d/zzz-custom.conf

# La imagen ya tiene todo configurado
WORKDIR /var/www

EXPOSE 9000

CMD ["php-fpm"]
