Панель администратора + авторизационное API

```
git clone

curl -sS https://getcomposer.org/installer | php

php composer.phar install

cp .env.example .env

chmod -R 777 storage bootstrap/cache

php artisan key:generate

Настроить DB_ константы в .env

php artisan migrate
```