<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index($goalId)
  {
    $user = Auth::user();
    $goal = Goal::findOrFail($goalId);
    $tasks = Task::where('goal_id', $goalId)->where('user_id', $user->id)->orderBy('order')->get();
    return view('tasks.index', compact('goal', 'tasks'));
  }

  public function store(Request $request, Goal $goal)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'estimated_time' => 'required|numeric|min:0',
      'priority' => 'required|integer|min:1|max:3',
      'start_date' => 'nullable|date',
      'start_time' => 'nullable|date_format:H:i',
    ]);

    $task = new Task([
      'name' => $validatedData['name'],
      'estimated_time' => $validatedData['estimated_time'],
      'priority' => $validatedData['priority'],
    ]);

    // 日付と時間の割り当ては別途行う
    $task->start_date = $validatedData['start_date'] ? Carbon::parse($validatedData['start_date'])->format('Y-m-d') : null;
    $task->start_time = $validatedData['start_time'] ? Carbon::parse($validatedData['start_time'])->format('H:i') : null;

    $task->user_id = Auth::id();
    $task->goal_id = $goal->id;
    $task->save();

    return response()->json(['success' => true, 'task' => $task]);
  }


  public function updateTaskAjax(Request $request, Task $task)
  {
    Log::info('Updating task', ['task_id' => $task->id, 'request_data' => $request->all()]);
    try {
      $validatedData = $request->validate([
        'id' => 'required|exists:tasks,id',
        'name' => 'nullable|string',
        'estimated_time' => 'nullable|numeric',
        'priority' => 'nullable|integer',
        'start_date' => 'nullable|string',
        'start_time' => 'nullable|string',
      ]);

      // 有効な日付かどうかをチェックし、無効ならデフォルト値を設定
      $startDate = $validatedData['start_date'] ? new \DateTime($validatedData['start_date']) : new \DateTime();
      $startTime = $validatedData['start_time'] ? new \DateTime($validatedData['start_time']) : new \DateTime('09:00');

      $task->update([
        'name' => $validatedData['name'],
        'estimated_time' => $validatedData['estimated_time'],
        'priority' => $validatedData['priority'],
        'start_date' => $startDate->format('Y-m-d'),
        'start_time' => $startTime->format('H:i'),
      ]);

      // スケジュールの再計算（変更なし）
      $allTasks = Task::where('goal_id', $task->goal_id)
        ->orderBy('start_date')
        ->orderBy('start_time')
        ->get();

      $currentDateTime = new \DateTime($startDate->format('Y-m-d') . ' ' . $startTime->format('H:i'));
      foreach ($allTasks as $t) {
        if ($t->id === $task->id) {
          continue; // 今更新したタスクはスキップ
        }
        $t->start_date = $currentDateTime->format('Y-m-d');
        $t->start_time = $currentDateTime->format('H:i');
        $t->save();

        // 次の時間枠に移動
        $currentDateTime->modify("+{$t->estimated_time} hours");

        // 午後5時以降の場合は翌日の午前9時に設定
        if ($currentDateTime->format('H') >= 17) {
          $currentDateTime->modify('+1 day')->setTime(9, 0);
        }
      }

      return response()->json(['success' => true, 'message' => 'Task updated successfully']);
    } catch (\Exception $e) {
      Log::error('Error updating task: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Error updating task'], 500);
    }
  }




  public function updateOrder(Request $request)
  {
    $taskOrder = $request->input('taskOrder');
    foreach ($taskOrder as $index => $taskId) {
      Task::where('id', $taskId)->update(['order' => $index]);
    }
    return response()->json(['success' => true]);
  }
  public function show(Task $task)
  {
    $goal = $task->goal;
    return view('tasks.show', compact('task', 'goal'));
  }
  public function edit(Task $task)
  {
    $goal = $task->goal;
    return view('tasks.edit', compact('task', 'goal'));
  }
  public function destroy(Task $task)
  {
    $task->delete();
    return response()->json(['success' => true]);
  }

  public function reorder(Request $request)
  {
    $taskOrder = $request->input('taskOrder');
    foreach ($taskOrder as $index => $taskId) {
      Task::where('id', $taskId)->update(['order' => $index]);
    }
    return response()->json(['success' => true]);
  }

  public function update(Request $request, Task $task)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'estimated_time' => 'nullable|numeric',
      'start_date' => 'nullable|date',
      'start_time' => 'nullable|date_format:H:i',
      'priority' => 'required|integer|min:1|max:3',
    ]);

    if (isset($validatedData['start_time'])) {
      $validatedData['start_time'] = Carbon::parse($validatedData['start_time'])->format('H:i');
    }

    $task->update($validatedData);

    return response()->json([
      'success' => true,
      'task' => $task
    ]);
  }

  public function saveAll(Request $request)
  {
    $events = $request->input('events');
    $notifications = $request->input('notifications');

    foreach ($events as $eventData) {
      $task = Task::find($eventData['id']);
      if ($task) {
        $task->start_date = $eventData['start_date'];
        $task->start_time = $eventData['start_time'];
        $task->end_date = $eventData['end_date'];
        $task->end_time = $eventData['end_time'];
        $task->estimated_time = $eventData['estimated_time'];
        $task->description = $eventData['description'];
        $task->priority = $eventData['priority'];
        $task->save();
      }
      $user = Auth::user();
      $user->email_notifications = $notifications['email'];
      $user->line_notifications = $notifications['line'];
      $user->save();
    }

    return response()->json(['success' => true, 'message' => 'All tasks saved successfully', 'tasks' => $updatedTasks]);
  }


  public function getCalendarEvents($goalId)
  {
    // タスクを取得して配列に変換
    $tasks = Task::where('goal_id', $goalId)->get();

    $calendarEvents = $tasks->map(function ($task) {
      return [
        'id' => $task->id,
        'title' => $task->name,
        'start' => $task->start_date . 'T' . $task->start_time,
        'end' => $task->end_date ? $task->end_date . 'T' . $task->end_time : null,
        'extendedProps' => [
          'description' => $task->description,
          'estimatedTime' => $task->estimated_time,
          'priority' => $task->priority,
        ],
      ];
    })->toArray(); // Collection を配列に変換

    // ログ出力
    Log::info('Number of tasks found:', ['count' => $tasks->count()]);
    Log::info('Goal ID:', ['goalId' => $goalId]);
    Log::alert('Calendar events retrieved successfully', ['calendarEvents' => $calendarEvents]);

    // JSON形式でカレンダーイベントを返す
    return response()->json(['calendarEvents' => $calendarEvents]);
  }
}
