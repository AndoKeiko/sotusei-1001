<?php
// ファイル名: database/migrations/2024_10_01_000000_update_work_hours_and_start_time_in_goals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWorkHoursAndStartTimeInGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goals', function (Blueprint $table) {
            // work_hours_per_day カラムを更新
            $table->float('work_hours_per_day', 53)->default(8)->change();

            // work_start_time カラムを追加（既に存在しない場合）
            if (!Schema::hasColumn('goals', 'work_start_time')) {
                $table->time('work_start_time')->default('09:00');
            }
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
            // work_hours_per_day のデフォルト値を削除
            $table->float('work_hours_per_day')->nullable()->change();

            // work_start_time カラムを削除（存在する場合）
            if (Schema::hasColumn('goals', 'work_start_time')) {
                $table->dropColumn('work_start_time');
            }
        });
    }
}