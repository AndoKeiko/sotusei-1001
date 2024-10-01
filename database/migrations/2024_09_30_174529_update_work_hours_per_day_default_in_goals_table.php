<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWorkHoursPerDayDefaultInGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goals', function (Blueprint $table) {
            // work_hours_per_day にデフォルト値 8.0 を設定
            $table->float('work_hours_per_day', 53)->default(8)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goals', function (Blueprint $table) {
            // デフォルト値を削除する
            $table->float('work_hours_per_day')->nullable()->change();
        });
    }
}
