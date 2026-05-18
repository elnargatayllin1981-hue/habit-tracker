<?php

namespace App\Services;

class HabitTemplateService
{
    /** Категории шаблонов */
    public static function categories(): array
    {
        return [
            'health'        => ['title' => 'Здоровье и тело',        'icon' => '💪', 'color' => '#2ecc71'],
            'mind'          => ['title' => 'Ум и обучение',          'icon' => '🧠', 'color' => '#21d4fd'],
            'productivity'  => ['title' => 'Продуктивность',         'icon' => '⚡', 'color' => '#f6b73c'],
            'mindfulness'   => ['title' => 'Осознанность и баланс',  'icon' => '🧘', 'color' => '#7c5cff'],
            'social'        => ['title' => 'Социальное',             'icon' => '👥', 'color' => '#ff8c7c'],
            'breakers'      => ['title' => 'Избавиться от привычки', 'icon' => '🚫', 'color' => '#ff5d6c'],
        ];
    }

    /** Каталог шаблонов */
    public static function templates(): array
    {
        return [
            // ---------- Здоровье ----------
            ['slug' => 'workout-30',  'category' => 'health', 'title' => 'Тренировка 30 минут',
             'description' => 'Кардио, силовая или йога — главное чтобы пульс поднялся.',
             'target_value' => 30, 'unit' => 'минут', 'duration_days' => 30, 'schedule' => 'weekdays',
             'color' => '#2ecc71',  'why' => 'Регулярная активность улучшает сон, настроение и фокус.'],

            ['slug' => 'walk-10k', 'category' => 'health', 'title' => '10 000 шагов в день',
             'description' => 'Прогулка пешком вместо транспорта, ходьба по этажам.',
             'target_value' => 10000, 'unit' => 'шагов', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#27ae60',  'why' => 'Самая простая привычка с доказанной пользой для сердца.'],

            ['slug' => 'water-2l', 'category' => 'health', 'title' => '2 литра воды',
             'description' => 'Поставьте бутылку на стол и пейте по ходу дня.',
             'target_value' => 2000, 'unit' => 'мл',     'duration_days' => 21, 'schedule' => 'daily',
             'color' => '#48d1cc',  'why' => 'Лёгкая дегидратация снижает работоспособность на 10–15%.'],

            ['slug' => 'sleep-8',  'category' => 'health', 'title' => '8 часов сна',
             'description' => 'Ложиться до 23:00, выключать экраны за час до сна.',
             'target_value' => 8, 'unit' => 'часов', 'duration_days' => 21, 'schedule' => 'daily',
             'color' => '#3498db', 'why' => 'Сон важнее тренировок и питания для восстановления.'],

            ['slug' => 'stretch-10', 'category' => 'health', 'title' => 'Растяжка 10 минут',
             'description' => 'Короткая растяжка утром или перед сном.',
             'target_value' => 10, 'unit' => 'минут', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#1abc9c', 'why' => 'Снимает мышечный гипертонус от сидячей работы.'],

            ['slug' => 'cold-shower', 'category' => 'health', 'title' => 'Холодный душ',
             'description' => 'Завершайте обычный душ 1–2 минутами холодной воды.',
             'target_value' => 2, 'unit' => 'минут', 'duration_days' => 21, 'schedule' => 'daily',
             'color' => '#0ea5e9', 'why' => 'Тренирует сосуды и быстро повышает энергию утром.'],

            // ---------- Ум ----------
            ['slug' => 'read-20p', 'category' => 'mind', 'title' => 'Чтение 20 страниц',
             'description' => 'Художественная или нон-фикшен — главное, ежедневно.',
             'target_value' => 20, 'unit' => 'страниц', 'duration_days' => 66, 'schedule' => 'daily',
             'color' => '#21d4fd', 'why' => '20 страниц в день = ~25 книг в год.'],

            ['slug' => 'lang-15', 'category' => 'mind', 'title' => 'Иностранный язык 15 минут',
             'description' => 'Duolingo, Anki или просмотр сериала с субтитрами.',
             'target_value' => 15, 'unit' => 'минут', 'duration_days' => 90, 'schedule' => 'daily',
             'color' => '#3b82f6', 'why' => 'Маленькие порции каждый день эффективнее больших раз в неделю.'],

            ['slug' => 'course-30', 'category' => 'mind', 'title' => 'Онлайн-курс 30 минут',
             'description' => 'Один урок Coursera/Stepik/YouTube перед работой.',
             'target_value' => 30, 'unit' => 'минут', 'duration_days' => 60, 'schedule' => 'weekdays',
             'color' => '#6366f1', 'why' => 'Утренние знания лучше укладываются в долгую память.'],

            ['slug' => 'leetcode', 'category' => 'mind', 'title' => 'Решить 1 задачу LeetCode',
             'description' => 'Easy или Medium — что хочется или подсказывает алгоритм спейс-репетишн.',
             'target_value' => 1, 'unit' => 'задачу', 'duration_days' => 90, 'schedule' => 'weekdays',
             'color' => '#7c3aed', 'why' => 'Стабильный путь к собеседованиям в IT-компаниях.'],

            ['slug' => 'podcast-30', 'category' => 'mind', 'title' => 'Подкаст 30 минут',
             'description' => 'Можно во время прогулки или дороги.',
             'target_value' => 30, 'unit' => 'минут', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#0891b2', 'why' => 'Пассивное обучение — отличный способ занять «мёртвое» время.'],

            // ---------- Продуктивность ----------
            ['slug' => 'morning-plan', 'category' => 'productivity', 'title' => 'Утренняя планёрка 5 мин',
             'description' => 'Записать 3 главные задачи на день.',
             'target_value' => 5, 'unit' => 'минут', 'duration_days' => 21, 'schedule' => 'weekdays',
             'color' => '#f6b73c', 'why' => '5 минут планирования экономят час хаотичной работы.'],

            ['slug' => 'pomodoro-4', 'category' => 'productivity', 'title' => '4 цикла Pomodoro',
             'description' => 'Четыре 25-минутных рабочих блока с перерывами.',
             'target_value' => 4, 'unit' => 'цикла', 'duration_days' => 30, 'schedule' => 'weekdays',
             'color' => '#ef4444', 'why' => 'Помогает удерживать фокус и не выгорать.'],

            ['slug' => 'inbox-zero', 'category' => 'productivity', 'title' => 'Inbox Zero',
             'description' => 'Закрыть почту так, чтобы во входящих было пусто.',
             'target_value' => 0, 'unit' => 'писем', 'duration_days' => 30, 'schedule' => 'weekdays',
             'color' => '#f59e0b', 'why' => 'Чистая почта = чистая голова.'],

            ['slug' => 'no-social-am', 'category' => 'productivity', 'title' => 'Без соцсетей до обеда',
             'description' => 'Соцсети открываются только после 13:00.',
             'target_value' => 0, 'unit' => 'минут', 'duration_days' => 30, 'schedule' => 'weekdays',
             'color' => '#fb923c', 'why' => 'Утром мозг наиболее продуктивен — не сжигайте этот ресурс.'],

            ['slug' => 'three-wins', 'category' => 'productivity', 'title' => 'Закрыть 3 ключевые задачи',
             'description' => 'Три приоритета дня, не больше.',
             'target_value' => 3, 'unit' => 'задачи', 'duration_days' => 21, 'schedule' => 'weekdays',
             'color' => '#facc15', 'why' => 'Лучше сделать 3 важных, чем «потыкаться» в 20.'],

            // ---------- Осознанность ----------
            ['slug' => 'meditation-10', 'category' => 'mindfulness', 'title' => 'Медитация 10 минут',
             'description' => 'Headspace, Insight Timer или просто наблюдение за дыханием.',
             'target_value' => 10, 'unit' => 'минут', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#7c5cff', 'why' => 'Снижает тревожность и улучшает концентрацию.'],

            ['slug' => 'gratitude-3', 'category' => 'mindfulness', 'title' => 'Дневник благодарности',
             'description' => 'Записать 3 вещи, за которые благодарны сегодня.',
             'target_value' => 3, 'unit' => 'пункта', 'duration_days' => 21, 'schedule' => 'daily',
             'color' => '#a855f7', 'why' => 'Практика с доказанным эффектом для долговременного счастья.'],

            ['slug' => 'walk-20', 'category' => 'mindfulness', 'title' => 'Прогулка на воздухе 20 мин',
             'description' => 'Без телефона, просто наблюдать вокруг.',
             'target_value' => 20, 'unit' => 'минут', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#8b5cf6', 'why' => 'Свежий воздух + дневной свет = меньше осенней хандры.'],

            ['slug' => 'digital-detox', 'category' => 'mindfulness', 'title' => 'Цифровой детокс перед сном',
             'description' => 'Никаких экранов за час до сна.',
             'target_value' => 60, 'unit' => 'минут', 'duration_days' => 21, 'schedule' => 'daily',
             'color' => '#9333ea', 'why' => 'Синий свет тормозит выработку мелатонина.'],

            // ---------- Социальное ----------
            ['slug' => 'call-family', 'category' => 'social', 'title' => 'Позвонить близким',
             'description' => 'Родители, бабушки, дедушки, друзья — кому давно не звонили.',
             'target_value' => 1, 'unit' => 'звонок', 'duration_days' => 30, 'schedule' => 'weekends',
             'color' => '#ff8c7c', 'why' => 'Близкие связи — главный фактор счастья по данным гарвардского исследования.'],

            ['slug' => 'kindness', 'category' => 'social', 'title' => 'Один жест доброты',
             'description' => 'Комплимент, помощь, подарок — что угодно.',
             'target_value' => 1, 'unit' => 'жест', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#fb7185', 'why' => 'Помощь другим повышает собственное настроение быстрее, чем покупки.'],

            ['slug' => 'journal-day', 'category' => 'social', 'title' => 'Дневник дня',
             'description' => 'Записать впечатления и мысли за день.',
             'target_value' => 1, 'unit' => 'запись', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#f43f5e', 'why' => 'Через месяц вы перестанете говорить «у меня все дни одинаковые».'],

            // ---------- Избавление ----------
            ['slug' => 'no-sugar', 'category' => 'breakers', 'title' => 'Без сахара',
             'description' => 'Никаких сладкого, газировки, выпечки.',
             'target_value' => 0, 'unit' => 'г сахара', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#ff5d6c', 'why' => 'Уже через неделю снизится тяга к сладкому, через месяц — кожа улучшится.'],

            ['slug' => 'no-alcohol', 'category' => 'breakers', 'title' => 'Без алкоголя',
             'description' => 'Сухой месяц или дольше.',
             'target_value' => 0, 'unit' => 'мл', 'duration_days' => 30, 'schedule' => 'daily',
             'color' => '#e11d48', 'why' => 'Лучше сон, ниже вес, яснее голова.'],

            ['slug' => 'no-smoking', 'category' => 'breakers', 'title' => 'Без курения',
             'description' => 'Полный отказ. Используйте никотиновую заместительную терапию, если тяжело.',
             'target_value' => 0, 'unit' => 'сигарет', 'duration_days' => 90, 'schedule' => 'daily',
             'color' => '#dc2626', 'why' => 'Через 3 месяца лёгкие восстанавливаются на 30%.'],

            ['slug' => 'no-scroll-night', 'category' => 'breakers', 'title' => 'Без скроллинга перед сном',
             'description' => 'Не открывать TikTok / Instagram / Reels в постели.',
             'target_value' => 0, 'unit' => 'минут', 'duration_days' => 21, 'schedule' => 'daily',
             'color' => '#be123c', 'why' => 'Снижает уровень тревоги и улучшает качество сна.'],
        ];
    }

    public static function templatesByCategory(): array
    {
        $grouped = [];
        foreach (self::categories() as $key => $cat) {
            $grouped[$key] = ['meta' => $cat, 'items' => []];
        }
        foreach (self::templates() as $t) {
            $grouped[$t['category']]['items'][] = $t;
        }
        return $grouped;
    }

    public static function find(string $slug): ?array
    {
        foreach (self::templates() as $t) {
            if ($t['slug'] === $slug) return $t;
        }
        return null;
    }
}
