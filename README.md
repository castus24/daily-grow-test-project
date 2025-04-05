# Laravel Client Mailing Project

## Установка

### Требования

- PHP 8.0^
- Laravel 10
- MySQL 5.7 (или другой поддерживаемый драйвер базы данных)
- vue 3.5^
- vue-router 4.4^
- vuetify 3.7^
- pinia 3.0^
- vue-toastification 2.0^
- @vitejs/plugin-vue 4.6^
- @mdi/font 7.0^

### Установка

1. Клонируйте репозиторий:

   ```
   git clone https://github.com/castus24/daily-grow-test-project.git

2. Установите зависимости с помощью Composer:

   ```bash
   composer install

3. Скопируйте файл .env.example в .env и настройте параметры подключения к базе данных:
   В .env установите настройки Mail для получения по почте пароля пользователя.
   Работать он будет используя очереди. Установите QUEUE_CONNECTION=database.

   ```
   .env.example .env
   ```

4. Сгенерируйте ключ приложения:

   ```bash
    php artisan key:generate
   ```

5. Установите библиотеку spatie/laravel-query-builder

   ```bash
   composer require spatie/laravel-query-builder
   ```

   ```bash
   php artisan vendor:publish --provider="Spatie\QueryBuilder\QueryBuilderServiceProvider" --tag="query-builder-config"
   ```

6. Установите библиотеку maatwebsite/excel

   ```bash
   composer require maatwebsite/excel
   ```

7. Установите библиотеку bensampo/laravel-enum

   ```bash
   composer require bensampo/laravel-enum
   ```

8. Создайте миграцию для таблицы очередей.

   ```bash
   php artisan queue:table
   ```

9. Запустите миграции для создания таблиц:

   ```bash
   php artisan migrate
   ```

10. Установите npm, vue, vuetify, vue-router и остальное.

```bash
npm install
```

```bash
npm install vue
```

```bash
npm install vuetify
```

```bash
npm install vue-router
```

```bash
npm install pinia
```

```bash
npm install @vitejs/plugin-vue
```

```bash
php artisan @mdi/font
```

11. Запустите cервер artisan и npm, а также воркер для очередей:

```
php artisan serve
```

```
npm run dev
```

```
php artisan queue:work
```





