<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mailings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название рассылки
            $table->text('content'); // Текст рассылки
            
            // Статистика
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            
            // Статус
            $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailings');
    }
};
