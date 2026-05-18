# Habit Tracker · Laravel 9

Минималистичный трекер привычек с AI-подсказками от Claude.

## Возможности

- Регистрация по логину, email, телефону и паролю; вход по логину и паролю.
- Создание целей (тренировка, чтение, без сахара…) с числовым таргетом.
- Отметка дней через выпадающий список: **+ выполнено · − провал · ∗ форс-мажор**.
- Поля «Что не получилось» и «Как улучшить» для каждого дня.
- Календарь за 90 дней, графики динамики и распределения статусов (Chart.js).
- AI-совет от Claude: анализ ваших провалов и конкретные шаги к улучшению.
- Современный дизайн со светлой / тёмной / авто-темой и плавными анимациями.

## Стек

- **PHP** 8.0+ · **Laravel** 9.x · **MySQL** 5.7+ / 8
- **Blade** + ванильный JS · **Chart.js** через CDN
- **Anthropic Claude API** через Guzzle

## Установка

```bash
# 1. Установка зависимостей
composer install

# 2. Конфигурация окружения
cp .env.example .env
php artisan key:generate

# 3. Откройте .env и пропишите:
#    DB_DATABASE=habit_tracker
#    DB_USERNAME=...
#    DB_PASSWORD=...
#    CLAUDE_API_KEY=sk-ant-...

# 4. Создайте БД и накатите миграции
mysql -uroot -e "CREATE DATABASE habit_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate

# 5. (опционально) демо-пользователь и данные
php artisan db:seed
# логин: demo / пароль: password

# 6. Запуск
php artisan serve
# открыть http://localhost:8000
```

## Получить ключ Claude API

1. Зарегистрируйтесь на https://console.anthropic.com.
2. В разделе API Keys создайте ключ.
3. Положите его в `.env`: `CLAUDE_API_KEY=sk-ant-...`.

Если ключ не задан, всё приложение работает как обычно — кнопка «Сгенерировать совет» вернёт понятную ошибку без падения.

## Структура

```
app/
├── Http/Controllers/
│   ├── Auth/{Login,Register}Controller.php   # регистрация/вход по логину
│   ├── DashboardController.php               # сводный экран
│   ├── HabitController.php                   # CRUD привычек
│   ├── HabitLogController.php                # отметка дней
│   ├── AiSuggestionController.php            # /habits/{id}/ai-suggest
│   └── ThemeController.php                   # сохранение выбора темы
├── Services/ClaudeService.php                # обращение к Anthropic API
└── Models/{User,Habit,HabitLog,AiSuggestion}.php
config/services.php → секция claude.*
database/migrations/                          # users, habits, habit_logs, ai_suggestions
resources/views/                              # auth, dashboard, habits + layouts/app.blade.php
public/{css/app.css, js/app.js}
routes/web.php                                # все маршруты приложения
```

## Маршруты

| Метод     | URL                                  | Назначение                          |
|-----------|--------------------------------------|-------------------------------------|
| GET/POST  | `/register`                          | Регистрация                          |
| GET/POST  | `/login`                             | Вход                                 |
| POST      | `/logout`                            | Выход                                |
| GET       | `/dashboard`                         | Сводка по всем привычкам             |
| GET/POST  | `/habits/create`, `/habits`          | Создание привычки                    |
| GET       | `/habits/{habit}`                    | Карточка привычки + графики          |
| PUT/DELETE| `/habits/{habit}`                    | Изменение / удаление                 |
| POST      | `/habits/{habit}/logs`               | Сохранить отметку дня                |
| POST      | `/habits/{habit}/ai-suggest`         | Запросить совет у Claude             |
| GET       | `/habits/{habit}/ai-history`         | История советов                      |
| POST      | `/theme`                             | Сохранить выбор темы (light/dark/auto)|

## Валидация регистрации

- `login` — 3–64 символа, латиница/цифры/`_`/`-`, уникален.
- `email` — корректный, уникален.
- `phone` — необязательный, регулярка `^\+?[0-9\s\-\(\)]{7,20}$`.
- `password` — ≥ 8 символов, должен совпасть с `password_confirmation`.

## Безопасность

- Пароли хранятся через `Hash::make` (bcrypt).
- CSRF-токен на всех POST-формах.
- Привязка привычки к пользователю проверяется на каждом запросе (403 при чужом ID).
- Сессии — file-driver, можно переключить на Redis/DB.

## Расширения

- Добавить уведомления (Mailgun/Postmark) — выслать утренний пуш-напоминание.
- Перенести AI-вызов в очередь `queue:work` для ускорения UI.
- Сделать публичный API через `routes/api.php` + Sanctum (уже подключён в зависимости).
