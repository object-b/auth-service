API для авторизации. Надеюсь меня когда-нибудь заменят на что-то легкое.

Данный сервис неразрывно связан с основным API и использует одну и ту же БД.

В основном используется таблица users, миграции для нее есть в API репозитории.

Данные для роутов (их всего три) передаются в JSON. Например

```
{
    "email": "email@domain.com",
    "password": "password"
}
```

Деплой
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
