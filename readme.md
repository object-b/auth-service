Панель администратора + API для авторизации

Контроллер авторизации пользователей хранится в app/Http/Controllers/API/UserController

В миграциях выполняется создание таблиц: users, password_resets, linked_social_accounts

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
---
*Возможно* потребуется дополнительная настройка laravel passport для oauth2

```
php artisan passport:install
```

После смены конфига следует запускать

```
php artisan config:cache
```

При странных ситуациях 🤯

```
php composer.phar dump-auto
```
