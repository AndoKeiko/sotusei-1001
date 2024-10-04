<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Goal $goal)
    {
        $tasks = $goal->tasks()->orderBy('order')->get();
        return view('goal_tasks.index', compact('goal', 'tasks'));
    }

    public function store(Request $request, Goal $goal)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'estimated_time' => 'required|numeric|min:1',
            'priority' => 'required|integer|min:1|max:3',
        ]);

        $task = new Task($validatedData);
        $task->goal_id = $goal->id;
        $task->user_id = Auth::id();
        $task->order = $goal->tasks()->max('order') + 1;
        $task->save();

        return redirect()->route('goals.tasks.index', $goal)->with('success', '新しいタスクが追加されました');
    }

    public function update(Request $request, Goal $goal, Task $task)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'estimated_time' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:1|max:3',
        ]);

        $task->update($validatedData);

        return redirect()->route('goals.tasks.index', $goal)->with('success', 'タスクが更新されました');
    }

    public function destroy(Goal $goal, Task $task)
    {
        $task->delete();
        return redirect()->route('goals.tasks.index', $goal)->with('success', 'タスクが削除されました');
    }
}