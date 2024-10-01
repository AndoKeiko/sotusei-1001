<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
          $table->id();
          $table->foreignId('user_id')->constrained()->onDelete('cascade');
          $table->foreignId('goal_id')->constrained()->onDelete('cascade');
          $table->string('name');
          $table->text('description')->nullable();
          $table->integer('elapsed_time')->default(0);
          $table->integer('estimated_time');
          $table->date('start_date')->nullable();
          $table->time('start_time')->nullable();
          $table->unsignedTinyInteger('priority');
          $table->unsignedInteger('order')->default(0);
          $table->enum('review_interval', ['next_day', '7_days', '14_days', '28_days', '56_days', 'completed'])
                ->default('next_day');
          $table->integer('repetition_count')->default(1);
          $table->dateTime('last_notification_sent')->nullable();
          $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}