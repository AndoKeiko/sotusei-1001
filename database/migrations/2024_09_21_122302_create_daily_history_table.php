<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('daily_history', function (Blueprint $table) {
          $table->id();
          $table->foreignId('user_id')->constrained()->onDelete('cascade');
          $table->foreignId('task_id')->constrained()->onDelete('cascade');
          $table->date('date');
          $table->integer('study_time'); // この学習セッションの時間（分単位）
          $table->dateTime('start_time');
          $table->dateTime('end_time');
          $table->text('notes')->nullable();
          $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_history');
    }
}