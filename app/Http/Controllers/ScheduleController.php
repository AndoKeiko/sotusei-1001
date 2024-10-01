<?php

namespace App\Http\Controllers;

use App\Jobs\SendLineNotificationJob;
use App\Services\ScheduleGeneratorService;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
  protected $scheduleGenerator;

  public function __construct(ScheduleGeneratorService $scheduleGenerator)
  {
    $this->scheduleGenerator = $scheduleGenerator;
  }

  public function index(Goal $goal)
  {
    $startTime = $goal->work_start_time ?? '09:00';
    $hoursPerDay = $goal->work_hours_per_day ?? 8.0;

    $schedule = $this->scheduleGenerator->generateSchedule(
      $goal,
      $goal->period_start,
      $startTime,
      $hoursPerDay
  );

    // タスクに関連するユーザー情報を事前にロード
    $tasks = Task::where('goal_id', $goal->id)->with('user')->get();

    $calendarEvents = $this->generateCalendarEvents($schedule);

    return view('schedules.index', [
      'goal' => $goal,
      'calendarEvents' => $calendarEvents,
      'initialSchedule' => $schedule,
      'tasks' => $tasks,
    ]);
  }


  private function generateCalendarEvents($schedule)
  {
    return collect($schedule)->flatMap(function ($daySchedule, $date) {
      return collect($daySchedule)->map(function ($task) use ($date) {
        return [
          'title' => $task['name'],
          'start' => $date . 'T' . $task['start_time'],
          'end' => $date . 'T' . $task['end_time'],
        ];
      });
    })->toArray();
  }

  public function show(Goal $goal)
  {
    $scheduleService = new ScheduleGeneratorService();

    // $goal->work_start_time が null の場合、デフォルトの時間 "09:00" を渡す
    $startTime = $goal->work_start_time ?? '09:00';

    // 他の引数と共にサービスに渡す
    $schedule = $scheduleService->generateSchedule($goal, $goal->period_start, $startTime, $goal->work_hours_per_day);

    // スケジュールをカレンダー用のイベントデータに変換
    $calendarEvents = $schedule->map(function ($task) {
      return [
        'title' => $task->name,
        'start' => $task->start_time,  // タスクの開始時刻
        'end' => $task->end_time,      // タスクの終了時刻
      ];
    })->toArray();  // 適切なJSON エンコードを確保するために配列に変換

    // ビューにスケジュールとイベントを渡す
    return view('schedules.show', [
      'schedule' => $schedule,
      'calendarEvents' => $calendarEvents,
      'goal' => $goal,
    ]);
  }


  public function create(Goal $goal)
  {
    return view('schedules.create', compact('goal'));
  }

  public function store(Request $request, Goal $goal)
  {
    $validated = $request->validate([
      'work_start_time' => 'required|date_format:H:i',
      'work_hours_per_day' => 'required|numeric|min:0|max:24',
      'work_period_start' => 'required|date',
    ]);

    $schedule = $this->scheduleGenerator->generateSchedule(
      $goal,
      $validated['work_period_start'],
      $validated['work_start_time'],
      $validated['work_hours_per_day']
    );

    // スケジュールをセッションに保存
    session(['generated_schedule' => $schedule]);

    return redirect()->route('schedules.index', $goal)->with('success', 'スケジュールが生成されました');
  }


  public function generate(Request $request, Goal $goal)
  {
    Log::info('Schedule generation request', $request->all());
    Log::info('Schedule generation initiated for goal: ' . $goal->id);

    $validatedData = $request->validate([
      'work_period_start' => 'required|date',
      'work_start_time' => 'required',
      'work_hours_per_day' => 'required|numeric|min:0|max:24',
    ]);

    $schedule = $this->scheduleGenerator->generateSchedule(
      $goal,
      $validatedData['work_period_start'],
      $validatedData['work_start_time'],
      $validatedData['work_hours_per_day']
  );

    $calendarEvents = $this->scheduleToCalendarEvents($schedule);

    return response()->json([
      'success' => true,
      'schedule' => $schedule,
      'calendarEvents' => $calendarEvents,
    ]);
  }

  private function scheduleToCalendarEvents($schedule)
  {
    $events = [];

    foreach ($schedule as $date => $tasks) {
      foreach ($tasks as $task) {
        $startDateTime  = Carbon::parse($date . ' ' . $task['start_time']);
        $endDateTime = Carbon::parse($date . ' ' . $task['end_time']);

        // LINE通知のスケジュール
        // $userId = $task['user_id'];
        // $messageText = "タスク「{$task['name']}」がもうすぐ始まります！";
        // $delay = $startDateTime->diffInSeconds(now());
        // if ($delay > 0) {
        //   SendLineNotificationJob::dispatch($userId, $messageText)->delay($delay);
        // }

        $events[] = [
          'title' => $task['name'],
          'start' => $startDateTime->toIso8601String(),
          'end' => $endDateTime->toIso8601String(),
          'extendedProps' => [
            'duration' => $task['duration']
          ]
        ];
      }
    }

    return $events;
  }
}
