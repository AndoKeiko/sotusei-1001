<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
  return Auth::check() ? redirect()->route('goals.index') : redirect()->route('login');
});

Route::get('/dashboard', function () {
  return redirect()->route('goals.index');
})->name('dashboard');

Route::get('/home', function () {
  return view('home');
});

Route::get('/line/login', [LineLoginController::class, 'redirectToLine'])->name('line.login');
Route::get('/callback', [LineLoginController::class, 'handleLineCallback'])->name('line.callback');

Route::middleware(['auth'])->group(function () {
  // Profile routes
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  // 目標関連ルート
  Route::get('goals', [GoalController::class, 'index'])->name('goals.index');
  Route::get('goals/create', [GoalController::class, 'create'])->name('goals.create');
  Route::post('goals', [GoalController::class, 'store'])->name('goals.store');
  Route::get('goals/{goal}', [GoalController::class, 'show'])->name('goals.show');
  Route::get('goals/{goal}/edit', [GoalController::class, 'edit'])->name('goals.edit');
  Route::put('goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
  Route::delete('goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');
  Route::get('goals/history', [GoalController::class, 'history'])->name('goals.history');
  Route::post('goals/{goal}/generate-schedule', [GoalController::class, 'generateSchedule'])->name('goals.generate-schedule');
  Route::post('/goals/store-and-generate-tasks', [GoalController::class, 'storeAndGenerateTasks'])
    ->name('goals.store_and_generate_tasks');


  // タスク関連ルート
  Route::get('/goals/{goal}/tasks', [TaskController::class, 'index'])->name('tasks.index');
  Route::post('/goals/{goal}/tasks', [TaskController::class, 'store'])->name('tasks.store');
  Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
  Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
  Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
  Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');


  // スケジュール関連ルート
  Route::get('/goals/{goal}/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
  Route::post('/goals/{goal}/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
  Route::get('goals/{goal}/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
  Route::post('/goals/{goal}/schedule/generate', [ScheduleController::class, 'generate'])->name('goals.schedule.generate');
  Route::post('/tasks/save-events', [ScheduleController::class, 'saveEvents'])->name('tasks.saveEvents');


  // Task routes
  Route::post('/update-task', [TaskController::class, 'updateTaskAjax'])->name('tasks.update.ajax');
  Route::post('/update-task-order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');

  // Line routes
  Route::get('/line/login', [LineController::class, 'redirectToLine'])->name('line.login');
  Route::get('/line/callback', [LineController::class, 'handleLineCallback'])->name('line.callback');
  Route::post('/line/webhook', [LineWebhookController::class, 'handleWebhook']);
});

require __DIR__ . '/auth.php';

Route::get('/dbtest', function () {
  try {
    DB::connection()->getPdo();
    return "データベース接続成功！";
  } catch (\Exception $e) {
    return "データベース接続エラー: " . $e->getMessage();
  }
});
