<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
      'estimated_time' => 'required|numeric|min:0', // estimated_time は時間の小数形式（例: 1.5 = 1時間30分）
      'priority' => 'required|integer|min:1|max:3',
      'start_date' => 'nullable|date',
      'start_time' => 'nullable|date_format:H:i',
    ]);

    $task = new Task([
      'name' => $validatedData['name'],
      'estimated_time' => $validatedData['estimated_time'],
      'priority' => $validatedData['priority'],
    ]);

    // start_date と start_time の割り当て
    $task->start_date = $validatedData['start_date'] ? Carbon::parse($validatedData['start_date'])->format('Y-m-d') : null;
    $task->start_time = $validatedData['start_time'] ? Carbon::parse($validatedData['start_time'])->format('H:i') : null;

    // end_time を計算する
    if ($validatedData['start_time'] && $validatedData['estimated_time']) {
      // Carbon で start_time に estimated_time を加算
      $startTime = Carbon::parse($validatedData['start_time']);
      $hours = floor($validatedData['estimated_time']); // 時間部分
      $minutes = ($validatedData['estimated_time'] - $hours) * 60; // 分部分

      // end_time を計算
      $endTime = $startTime->copy()->addHours($hours)->addMinutes($minutes);
      $task->end_time = $endTime->format('H:i');
    }

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
      'estimated_time' => 'nullable|numeric|min:0',
      'start_date' => 'nullable|date',
      'start_time' => 'nullable|date_format:H:i',
      'priority' => 'required|integer|min:1|max:3',
    ]);

    if (isset($validatedData['start_date']) && isset($validatedData['start_time']) && isset($validatedData['estimated_time'])) {
      $startDateTime = Carbon::parse($validatedData['start_date'] . ' ' . $validatedData['start_time']);
      $estimatedTime = $validatedData['estimated_time'] ?? 1;
      $hours = floor($validatedData['estimated_time']);
      $minutes = ($validatedData['estimated_time'] - $hours) * 60;

      // end_time と end_date を計算
      $endDateTime = $startDateTime->copy()->addHours($hours)->addMinutes($minutes);
      $validatedData['end_time'] = $endDateTime->format('H:i');
      $validatedData['end_date'] = $endDateTime->toDateString();
    } else {
      // 必要な情報が不足している場合は end_time と end_date をnullに設定
      $validatedData['end_time'] = '10:00';  // デフォルトの終了時間を10:00に設定
      $validatedData['end_date'] = $validatedData['start_date'] ?? now()->toDateString();
    }

    // タスクを更新する
    $task->update($validatedData);

    // 更新後のタスクを再取得して確実に最新の状態を取得
    $task = $task->fresh();

    Log::info('Task updated successfully', ['task_id' => $task->id, 'updated_data' => $validatedData]);
    Log::info('Updated task', ['task' => $task->toArray()]);

    return response()->json([
      'success' => true,
      'task' => $task
    ]);
  }

  public function saveAll(Request $request)
  {
      $tasks = $request->input('tasks');
      $notifications = $request->input('notifications');
      
      DB::beginTransaction();
      try {
          $updatedTasks = [];
          foreach ($tasks as $taskData) {
              $task = Task::findOrFail($taskData['id']);
              $task->update($taskData);
              $updatedTasks[] = $task->fresh();
          }
          $user = Auth::user();
          $user->email_notifications = $notifications['email'];
          $user->line_notifications = $notifications['line'];
          $user->save();

          DB::commit();
          return response()->json(['success' => true, 'message' => 'All tasks saved successfully', 'tasks' => $updatedTasks]);
      } catch (\Exception $e) {
          DB::rollBack();
          Log::error('Error saving tasks: ' . $e->getMessage());
          return response()->json(['success' => false, 'message' => 'Error saving tasks: ' . $e->getMessage()], 500);
      }
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
