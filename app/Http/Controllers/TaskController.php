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
    ]);

    $task = new Task([
      'name' => $validatedData['name'],
      'estimated_time' => $validatedData['estimated_time'],
      'priority' => $validatedData['priority'],
      // 'start_date' => $validatedData['start_date'] ?? Carbon::today()->format('Y-m-d'),
      // 'start_time' => $validatedData['start_time'] ?? '09:00:00',
  ]);
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
        'name' => 'required|string|max:255',
        'estimated_time' => 'required|numeric|min:0',
        'priority' => 'required|integer|min:1|max:3',
        'start_date' => 'required|date',
        'start_time' => 'required|date_format:H:i:s',
      ]);

      $task->update($validatedData);

      // このゴールに関連するすべてのタスクを取得
      $allTasks = Task::where('goal_id', $task->goal_id)
        ->orderBy('start_date')
        ->orderBy('start_time')
        ->get();

      // スケジュールの再計算
      $currentDateTime = Carbon::parse($validatedData['start_date'] . ' ' . $validatedData['start_time']);
      foreach ($allTasks as $t) {
        if ($t->id === $task->id) {
          continue; // 今更新したタスクはスキップ
        }
        $t->start_date = $currentDateTime->toDateString();
        $t->start_time = $currentDateTime->toTimeString();
        $t->save();

        // 次の時間枠に移動
        $currentDateTime->addHours($t->estimated_time);

        // 午後5時以降の場合は翌日の午前9時に設定
        if ($currentDateTime->hour >= 17) {
          $currentDateTime->addDay()->setTime(9, 0, 0);
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
      $isPartialUpdate = $request->input('is_partial_update', false);
  
      $rules = [
          'start_date' => 'required|date',
          'start_time' => 'required|date_format:H:i:s',
      ];
  
      if (!$isPartialUpdate) {
          $rules = array_merge($rules, [
              'name' => 'required|string|max:255',
              'description' => 'nullable|string',
              'estimated_time' => 'required|numeric|min:0',
              'priority' => 'required|in:1,2,3',
          ]);
      }
  
      $validated = $request->validate($rules);
  
      // タスクを更新
      $task->update($validated);
  
      return response()->json(['success' => true, 'task' => $task->fresh()]);
  }
  

  
}
