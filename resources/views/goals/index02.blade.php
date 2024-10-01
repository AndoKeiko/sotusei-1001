@extends('layouts.app')

@section('content')
<div class="py-12">

  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">目標管理</h1>

        @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
          <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif
        
        <!-- 新規作成ボタン -->
        <div class="mb-6">
          <a href="{{ route('goals.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
            新しい目標を作成
          </a>
        </div>

        <!-- 既存の目標入力フォーム (変更なし) -->
        <div class="mb-8">
          <h2 class="text-xl font-semibold mb-4">
            {{ $lastGoal ? '目標を更新' : '新しい目標を作成' }}
          </h2>
          <form action="{{ route('goals.store') }}" method="POST" class="space-y-4">
    @csrf
    @if ($lastGoal)
        @method('PUT')
    @endif
    <!-- フォームフィールド -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">目標名</label>
        <input type="text" name="name" id="name" value="{{ old('name', $lastGoal->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
    </div>

    <div>
        <label for="current_status" class="block text-sm font-medium text-gray-700">現状</label>
        <textarea name="current_status" id="current_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('current_status', $lastGoal->current_status ?? '') }}</textarea>
    </div>

    <div>
        <label for="period_start" class="block text-sm font-medium text-gray-700">開始日</label>
        <input type="date" name="period_start" id="period_start" value="{{ old('period_start', $lastGoal->period_start ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
    </div>

    <div>
        <label for="period_end" class="block text-sm font-medium text-gray-700">終了日</label>
        <input type="date" name="period_end" id="period_end" value="{{ old('period_end', $lastGoal->period_end ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
    </div>

    <div>
        <label for="work_hours_per_day" class="block text-sm font-medium text-gray-700">1日の作業時間</label>
        <input type="number" name="work_hours_per_day" id="work_hours_per_day" value="{{ old('work_hours_per_day', $lastGoal->work_hours_per_day ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
    </div>

    <div class="mt-6">
        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            {{ $lastGoal ? '目標を更新してタスクを再生成' : '目標を作成してタスクを生成' }}
        </button>
    </div>
</form>

        </div>

        <!-- 編集可能なタスク一覧 -->
        @if ($lastGoal && $tasks->isNotEmpty())
        <?php $edit_flug = false; ?>
        <div class="mt-8">
          <h2 class="text-xl font-semibold mb-4">タスク一覧</h2>
          <p class="mb-4">予想時間は少な目だと思いますので、考慮の上調整してください</p>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タスク名</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予想時間</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">重要度</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">アクション</th>
                </tr>
              </thead>
              <tbody id="taskList" class="bg-white divide-y divide-gray-200">
                @foreach ($tasks as $task)
                <tr class="task-item" data-task-id="{{ $task->id }}">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="task-name-display">{{ $task->name }}</span>
                    <input type="text" class="task-name-input hidden" value="{{ $task->name }}">
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="task-time-display">{{ $task->estimated_time }}</span>
                    <input type="number" class="task-time-input hidden" value="{{ $task->estimated_time }}">
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="task-priority-display">
                      @if($task->priority == 1)
                      低
                      @elseif($task->priority == 2)
                      中
                      @else
                      高
                      @endif
                    </span>
                    <select class="task-priority-select hidden">
                      <option value="1" {{ $task->priority == 1 ? 'selected' : '' }}>低</option>
                      <option value="2" {{ $task->priority == 2 ? 'selected' : '' }}>中</option>
                      <option value="3" {{ $task->priority == 3 ? 'selected' : '' }}>高</option>
                    </select>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="edit-task text-indigo-600 hover:text-indigo-900 mr-2">編集</button>
                    <button class="update-task text-indigo-600 hover:text-indigo-900 mr-2 hidden">更新</button>
                    <button class="delete-task text-red-600 hover:text-red-900">削除</button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        <!-- 新しいタスク追加フォーム -->
        <div class="mt-8">
          <h3 class="text-lg font-semibold mb-2">新しいタスクを追加</h3>
          <form id="addTaskForm" class="flex items-center space-x-2">
            <input type="text" id="newTaskName" placeholder="タスク名" required class="flex-grow px-3 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <input type="number" id="newTaskTime" placeholder="優先順位" required min="0" step="0.1" class="w-24 px-3 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <select id="newTaskPriority" required class="px-3 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <option value="1">低</option>
              <option value="2">中</option>
              <option value="3">高</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">追加</button>
          </form>
        </div>
        @else
        <p class="text-gray-500 mt-4">タスクはまだありません。</p>
        @endif

        </div>



        <div class="mt-8">
          <a href="{{ route('goals.history') }}" class="text-blue-600 hover:text-blue-800">過去の目標一覧を見る</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="{{ asset('js/task-management.js') }}"></script>
@endpush