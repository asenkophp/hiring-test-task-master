# Панель управления e-commerce — Задание для трудоустройства

Небольшое Laravel-приложение версии 11 с панелью администрирования Filament для управления 
товарами, клиентами и заказами. Обработка заказов выполняется асинхронно через 
Laravel Horizon, а изменения статуса публикуются по WebSocket через Reverb. Проект упакован 
в стек Docker Compose, чтобы вы могли запустить всё одной командой.

## Стек

- PHP 8.2 / Laravel 11
- Filament 4
- Redis + Laravel Horizon
- Laravel Reverb (WebSockets)
- MariaDB 11
- Nginx + PHP-FPM
- Supervisor для воркера очереди

## Подготовка

Создайте GitHub / GitLab репозиторий и загрузите в него данный проект без изменений.
Просьба делать коммиты постепенно (incrementally) и объяснять ваше рассуждение 
в сообщениях коммитов или в коротком файле `NOTES.md`.

## Быстрый старт

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan horizon:install
docker compose exec app php artisan reverb:install
docker compose exec app php artisan vendor:publish --tag=sanctum-migrations
docker compose exec app php artisan migrate --seed
```

Затем панель администрирования будет доступна по адресу **http://localhost:8080/admin**.

Данные входа по умолчанию:

- email: `dev@test.com`
- password: `password`

Horizon dashboard: **http://localhost:8080/horizon**

## Ваши задачи

Относитесь к этому как к ревью кода небольшого продакшн-проекта. Нас интересует 
ваш ход мыслей не меньше, чем сами исправления.

1. **Исправить обработку заказов.** Создайте несколько заказов через панель администратора,
   выполните обработку платежа через действие "Обработать платеж"
   и следите за изменением их статуса при отказах платежного шлюза.
2. **Проанализировать страницу списка заказов.** Страница "Заказы" в Filament загружается медленнее, 
   чем должно быть. Выясните причину и предложите изменение. (_На производительных системах проблема 
   может быть не столь явной, но она есть._)

## Заметки о развертывании

Сервис `nginx` в `docker-compose.yml` монтирует `docker/nginx/default.conf` в `/etc/nginx/conf.d/`. 
Проверьте эту конфигурацию:

- `client_max_body_size` установлен щедро для загрузки изображений товаров.
- `try_files` настроен для контроллера фронтенда Laravel.
- Внутренний сервис PHP-FPM доступен по имени сервиса (`app:9000`) через внутреннюю сеть Docker.

Если вы меняете порты, не забудьте обновить `APP_URL` в `.env` и любые переопределения хоста Reverb. 
Контейнер Reverb открывает порт `:8081` на хосте; браузер подключается к нему напрямую для отправки 
WebSocket-фреймов.

## Полезные команды

```bash
# просмотр логов Horizon в реальном времени
docker compose logs -f horizon

# открыть консоль внутри контейнера приложения
docker compose exec app bash

# выполнить тестовый набор
docker compose exec app php artisan test
```