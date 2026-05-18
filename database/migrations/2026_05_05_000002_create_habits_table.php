<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->unsignedInteger('target_value')->default(0);   // например, 30
            $table->string('unit', 32)->default('минут');           // минут / страниц / раз
            $table->string('color', 16)->default('#7c5cff');
            $table->date('start_date')->nullable();
            // Сколько дней пользователь хочет вести привычку (0 = бессрочно)
            $table->unsignedInteger('duration_days')->default(0);
            // Расписание: daily | weekdays | weekends | alternate
            $table->enum('schedule', ['daily', 'weekdays', 'weekends', 'alternate'])->default('daily');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};
