<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleGeneratorService
{
  public function generateSchedule(Goal $goal, string $workPeriodStart, string $startTime, float $hoursPerDay): array
  {
    // 1. 基本的な変数の設定
    $workPeriodStart = Carbon::parse($workPeriodStart);
    $hoursPerDay = $hoursPerDay ?? 8.0;
    $startTime = $startTime ? Carbon::parse($startTime) : Carbon::parse('09:00');
    $endDate = Carbon::parse($goal->period_end);
    // 2. タスクの取得
    $tasks = $goal->tasks()->orderBy('priority', 'desc')->get();
    Log::info('ScheduleGeneratorService Tasks:', ['tasks' => $tasks]);

    $schedule = [];

    $currentDate = $workPeriodStart;
    $currentTaskIndex = 0;
    $remainingTaskTime = $tasks->isNotEmpty() ? $tasks[$currentTaskIndex]->estimated_time : 1;
    // 3. スケジュール生成のメインループ
    while ($currentDate <= $endDate && $currentTaskIndex < $tasks->count()) {
      $dailyWorkTime = $hoursPerDay;
      $taskStartTime = clone $startTime; // スタート時間をクローンする
      $dateSchedule = [];
      // 4. 1日のスケジュール生成
      while ($dailyWorkTime > 0 && $currentTaskIndex < $tasks->count()) {
        $task = $tasks[$currentTaskIndex];
        $timeForTask = min($dailyWorkTime, $remainingTaskTime);

        $dateSchedule[] = [
          'id' => $task->id,  // タスクのIDを追加
          'name' => $task->name,
          'duration' => $timeForTask,
          'start_time' => $taskStartTime->format('H:i'),
          'end_time' => $taskStartTime->copy()->addHours($timeForTask)->format('H:i'),
          'description' => $task->description,  // タスクの説明を追加
          'priority' => $task->priority,  // タスクの優先度を追加
        ];

        // 5. 次のタスクの準備
        $taskStartTime->addHours($timeForTask);

        $remainingTaskTime -= $timeForTask;
        $dailyWorkTime -= $timeForTask;

        if ($remainingTaskTime <= 0) {
          $currentTaskIndex++;
          if ($currentTaskIndex < $tasks->count()) {
            $remainingTaskTime = $tasks[$currentTaskIndex]->estimated_time;
          }
        }
      }

      $schedule[$currentDate->format('Y-m-d')] = $dateSchedule;
      $currentDate->addDay();
    }

    return $schedule;
  }
}
