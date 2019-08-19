API для авторизации. Надеюсь меня когда-нибудь заменят на что-то легкое.

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

После смены конфига следует запускать

```
php artisan config:cache
```
