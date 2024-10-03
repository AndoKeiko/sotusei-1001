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

    // スケジュールの生成
    $schedule = $this->scheduleGenerator->generateSchedule(
      $goal,
      $goal->period_start,
      $startTime,
      $hoursPerDay
    );
    Log::info('Generated Schedule:', ['schedule' => $schedule]);

    // タスクを取得し、ユーザー情報を事前にロード
    $tasks = Task::where('goal_id', $goal->id)->with('user')->get();
    Log::info('Calendar Tasks:', ['tasks' => $tasks]);

    // タスクからカレンダーイベントを生成
    $calendarEvents = $tasks->map(function ($task) {
      return $task->calendarEvent;
    })->values();
    Log::info('Calendar Events:', ['events' => $calendarEvents]);
    return view('schedules.index', [
      'goal' => $goal,
      'calendarEvents' => $calendarEvents,
      'initialSchedule' => $schedule,
      'tasks' => $tasks,
      'goalId' => $goal->id,
      'generateScheduleUrl' => route('goals.schedule.generate', ['goal' => $goal->id]),
    ]);
  }

  public function generateSchedule($goal, $startDate, $startTime, $hoursPerDay)
  {
    // 最新のタスク情報を取得
    $tasks = Task::where('goal_id', $goal->id)->orderBy('priority', 'desc')->get();
    Log::info('generateSchedule Tasks:', ['tasks' => $tasks]);

    $schedule = [];

    foreach ($tasks as $task) {
      // タスクに `start_date` と `start_time` が設定されている場合、それを使用
      if ($task->start_date && $task->start_time) {
        $currentDate = Carbon::parse($task->start_date);
        $currentTime = Carbon::parse($task->start_time)->format('H:i');
      } else {
        // タスクに日時が設定されていない場合、デフォルトの開始日時を使用
        $currentDate = Carbon::parse($startDate);
        $currentTime = Carbon::parse($startTime)->format('H:i');
      }

      $taskDuration = $task->estimated_time; // タスクの所要時間

      $schedule[$currentDate->toDateString()][] = [
        'id' => $task->id,
        'name' => $task->name,
        'start_time' => $currentTime,
        'end_time' => Carbon::parse($currentTime)->addHours($taskDuration)->format('H:i'),
        'duration' => $taskDuration,
        'description' => $task->description,
        'estimated_time' => $task->estimated_time,
        'priority' => $task->priority,
      ];

      // 次のタスクの開始時間を調整
      $currentTime = Carbon::parse($currentTime)->addHours($taskDuration)->format('H:i');

      // 1日の作業時間を超えた場合の処理などを追加
    }

    return $schedule;
  }

  public function show(Goal $goal)
  {
    $scheduleService = new ScheduleGeneratorService();

    $workPeriodStart = $goal->period_start;
    $startTime = $goal->work_start_time ?? '09:00';
    $hoursPerDay = $goal->work_hours_per_day ?? 8.0;

    // 他の引数と共にサービスに渡す
    $schedule = $scheduleService->generateSchedule($goal, $workPeriodStart, $startTime, $hoursPerDay);

    $goalId = $goal->id;
    $calendarEvents = $this->generateCalendarEvents($schedule);

    return view('schedules.index', compact('goal', 'schedule', 'goalId', 'calendarEvents'));
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
      'work_start_time' => 'required|date_format:H:i',
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
        $startDateTime = Carbon::parse($date . ' ' . $task['start_time']);
        $endDateTime = Carbon::parse($date . ' ' . $task['end_time']);

        $events[] = [
          'id' => $task['id'],
          'title' => $task['name'],
          'start' => $startDateTime->format('Y-m-d\TH:i'),
          'end' => $endDateTime->format('Y-m-d\TH:i'),
          'extendedProps' => [
            'duration' => $task['duration'],
            'description' => $task['description'] ?? '',
            'estimatedTime' => $task['estimated_time'] ?? 0,
            'priority' => $task['priority'] ?? '2',
          ]
        ];
      }
    }

    return $events;
  }

  private function generateCalendarEvents($schedule)
  {
    $events = [];

    foreach ($schedule as $date => $tasks) {
      foreach ($tasks as $task) {
        $startDateTime = Carbon::parse($date . ' ' . $task['start_time']);
        $endDateTime = Carbon::parse($date . ' ' . $task['end_time']);

        $events[] = [
          'id' => $task['id'] ?? uniqid(),
          'title' => $task['name'],
          'start' => $startDateTime->format('Y-m-d\TH:i'),
          'end' => $endDateTime->format('Y-m-d\TH:i'),
          'extendedProps' => [
            'duration' => $task['duration'],
            'description' => $task['description'] ?? '',
            'estimatedTime' => $task['estimated_time'] ?? 0,
            'priority' => $task['priority'] ?? '2',
          ],
        ];
      }
    }

    return $events;
  }

  public function saveEvents(Request $request)
  {
    try {
      $events = $request->input('events');

      foreach ($events as $event) {
        $task = Task::findOrNew($event['id']);
        $task->name = $event['title'];
        $task->start_date = Carbon::parse($event['start'])->format('Y-m-d');
        $task->start_time = Carbon::parse($event['start'])->format('H:i');
        $task->estimated_time = $event['extendedProps']['duration'];
        $task->description = $event['extendedProps']['description'];
        $task->priority = $event['extendedProps']['priority'];
        $task->save();
      }

      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      Log::error('Error saving events: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'An error occurred while saving events'], 500);
    }
  }
}