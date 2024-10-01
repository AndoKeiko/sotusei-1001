<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    $task = new Task($validatedData);
    $task->user_id = Auth::id();
    $task->goal_id = $goal->id;
    $task->save();

    return response()->json(['success' => true, 'task' => $task]);
  }

  public function updateTaskAjax(Request $request, Task $task)
  {
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
    $currentDateTime = new \DateTime($validatedData['start_date'] . ' ' . $validatedData['start_time']);
    foreach ($allTasks as $t) {
      if ($t->id === $task->id) {
        continue; // 今更新したタスクはスキップ
      }
      $t->start_date = $currentDateTime->format('Y-m-d');
      $t->start_time = $currentDateTime->format('H:i:s');
      $t->save();

      // 次の時間枠に移動
      $currentDateTime->add(new \DateInterval('PT' . $t->estimated_time . 'H'));
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
      $validated = $request->validate([
          'name' => 'required|string|max:255',
          'description' => 'nullable|string',
          'estimated_time' => 'required|numeric|min:0',
          'start_date' => 'required|date',
          'start_time' => 'required|date_format:H:i:s',
          'priority' => 'required|in:1,2,3',
      ]);
  
      $task->update($validated);
  
      return response()->json(['success' => true, 'task' => $task]);
  }

  public function updateOrder(Request $request)
  {
    $tasks = $request->input('tasks');
    foreach ($tasks as $task) {
      Task::where('id', $task['id'])->update(['order' => $task['order']]);
    }
    return response()->json(['success' => true]);
  }
}
