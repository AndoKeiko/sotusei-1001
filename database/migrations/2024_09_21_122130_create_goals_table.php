<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalsTable extends Migration
{
    public function up()
    {
      Schema::create('goals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->text('current_status')->nullable();
        $table->date('period_start');
        $table->date('period_end');
        $table->text('description')->nullable();
        $table->integer('status')->default(0);
        $table->integer('total_time')->default(0)->nullable();
        $table->integer('progress_percentage')->default(0);
        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('goals');
    }
}