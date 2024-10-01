<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;

class ScheduleGeneratorService
{
  public function generateSchedule(Goal $goal, string $workPeriodStart, string $startTime, float $hoursPerDay): array
  {
    //スケジュール情報の入った配列を返せる
    $workPeriodStart = Carbon::parse($workPeriodStart);
    $hoursPerDay = $hoursPerDay ?? 8.0;
    $startTime = $startTime ? Carbon::parse($startTime) : Carbon::parse('09:00');
    $endDate = Carbon::parse($goal->period_end);

    $tasks = $goal->tasks()->orderBy('priority', 'desc')->get();
    $schedule = [];

    $currentDate = $workPeriodStart;
    $currentTaskIndex = 0;
    $remainingTaskTime = $tasks->isNotEmpty() ? $tasks[$currentTaskIndex]->estimated_time : 0;

    while ($currentDate <= $endDate && $currentTaskIndex < $tasks->count()) {
      $dailyWorkTime = $hoursPerDay;
      $taskStartTime = clone $startTime; // スタート時間をクローンする
      $dateSchedule = [];

      while ($dailyWorkTime > 0 && $currentTaskIndex < $tasks->count()) {
        $task = $tasks[$currentTaskIndex];
        $timeForTask = min($dailyWorkTime, $remainingTaskTime);

        $dateSchedule[] = [
          'name' => $task->name,
          'duration' => $timeForTask,
          'start_time' => $taskStartTime->format('H:i'),  // 作業の開始時間
          'end_time' => $taskStartTime->copy()->addHours($timeForTask)->format('H:i'),  // 作業の終了時間
        ];

        // 作業終了後、次の作業開始時間を計算
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
