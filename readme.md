Панель администратора + авторизационное API

```
git clone

curl -sS https://getcomposer.org/installer | php

php composer.phar install

cp .env.example .env

chmod -R 777 storage bootstrap/cache

php artisan key:generate

Настроить DB_ и APP_URL константы в .env

php artisan migrate
```

Возможно потребуется настройка laravel passport

```
php artisan passport:install
php artisan migrate
```
---
После смены конфига следует запускать

```
php artisan config:cache
```

При странных ситуациях 🤯

```
php composer.phar dump-auto
```
