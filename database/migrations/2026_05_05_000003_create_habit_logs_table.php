<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            // status: success (+), fail (-), skipped (*)
            $table->enum('status', ['success', 'fail', 'skipped']);
            $table->unsignedInteger('value')->nullable(); // фактическое значение, например 25 минут
            $table->text('failure_note')->nullable();
            $table->text('improvement_note')->nullable();
            $table->timestamps();

            $table->unique(['habit_id', 'log_date']);
            $table->index('log_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
    }
};
