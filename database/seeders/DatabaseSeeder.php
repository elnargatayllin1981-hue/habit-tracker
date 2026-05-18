<?php

namespace Database\Seeders;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['login' => 'demo'],
            [
                'email'    => 'demo@example.com',
                'phone'    => '+7 999 000-00-00',
                'password' => Hash::make('password'),
            ]
        );

        if ($user->habits()->count() > 0) {
            return;
        }

        $habit = Habit::create([
            'user_id'      => $user->id,
            'title'        => 'Тренировка',
            'description'  => 'Минимум 30 минут активности в день.',
            'target_value' => 30,
            'unit'         => 'минут',
            'color'        => '#7c5cff',
            'start_date'   => now()->subDays(30),
        ]);

        // Заполним 30 дней псевдо-данных
        $statuses = ['success', 'success', 'success', 'fail', 'skipped'];
        for ($i = 29; $i >= 0; $i--) {
            $status = $statuses[array_rand($statuses)];
            HabitLog::create([
                'habit_id' => $habit->id,
                'log_date' => Carbon::today()->subDays($i),
                'status'   => $status,
                'value'    => $status === 'success' ? rand(20, 45) : null,
                'failure_note' => $status === 'fail'
                    ? 'Слишком устал после работы, не нашёл сил начать.'
                    : null,
                'improvement_note' => $status === 'fail'
                    ? 'Попробую тренироваться утром, до завтрака.'
                    : null,
            ]);
        }
    }
}
