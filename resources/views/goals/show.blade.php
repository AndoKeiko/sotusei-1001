@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">目標の詳細: {{ $goal->name }}</h1>

        <div class="mb-4">
          <p class="text-gray-700">現状: {{ $goal->current_status }}</p>
          <p class="text-gray-700">目標期間: {{ $goal->period_start }} ~ {{ $goal->period_end }}</p>
          <!-- 他の目標の詳細情報をここに追加 -->
        </div>

        <h2 class="text-xl font-semibold mb-4">関連タスク</h2>
        @if($goal->tasks->count() > 0)
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200" id="taskTable">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タスク名</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予想時間</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">重要度</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">アクション</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @foreach($goal->tasks as $task)
              <tr class="task-item" data-task-id="{{ $task->id }}">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="task-name-display">{{ $task->name }}</span>
                  <input type="text" class="task-name-input hidden border rounded px-2 py-1 w-full" value="{{ $task->name }}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="task-time-display">{{ $task->estimated_time }}</span>
                  <input type="number" class="task-time-input hidden border rounded px-2 py-1 w-full" value="{{ $task->estimated_time }}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="task-priority-display">
                    @if($task->priority == 1) 低
                    @elseif($task->priority == 2) 中
                    @else 高
                    @endif
                  </span>
                  <select class="task-priority-select hidden border rounded px-2 py-1 w-full">
                    <option value="1" {{ $task->priority == 1 ? 'selected' : '' }}>低</option>
                    <option value="2" {{ $task->priority == 2 ? 'selected' : '' }}>中</option>
                    <option value="3" {{ $task->priority == 3 ? 'selected' : '' }}>高</option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button class="edit-task text-blue-600 hover:text-blue-900 mr-2">編集</button>
                  <button class="update-task text-green-600 hover:text-green-900 mr-2 hidden">更新</button>
                  <button class="delete-task text-red-600 hover:text-red-900">削除</button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <p class="text-gray-500 mt-2">関連するタスクはまだありません。</p>
        @endif

        <div class="mt-6">
          <a href="{{ route('schedules.show', $goal) }}" class="inline-flex px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">スケジュールを作成</a>
        </div>

        <div class="mt-8">
          <a href="{{ route('goals.index') }}" class="text-blue-600 hover:text-blue-800">過去の目標一覧に戻る</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const taskTable = document.getElementById('taskTable');

    taskTable.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-task')) {
            const row = e.target.closest('tr');
            row.querySelectorAll('.task-name-display, .task-time-display, .task-priority-display, .edit-task').forEach(el => el.classList.add('hidden'));
            row.querySelectorAll('.task-name-input, .task-time-input, .task-priority-select, .update-task').forEach(el => el.classList.remove('hidden'));
        }

        if (e.target.classList.contains('update-task')) {
            const row = e.target.closest('tr');
            const taskId = row.dataset.taskId;
            const name = row.querySelector('.task-name-input').value;
            const time = row.querySelector('.task-time-input').value;
            const priority = row.querySelector('.task-priority-select').value;

            fetch(`/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name, estimated_time: time, priority })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.querySelector('.task-name-display').textContent = name;
                    row.querySelector('.task-time-display').textContent = time;
                    row.querySelector('.task-priority-display').textContent = ['低', '中', '高'][priority - 1];

                    row.querySelectorAll('.task-name-input, .task-time-input, .task-priority-select, .update-task').forEach(el => el.classList.add('hidden'));
                    row.querySelectorAll('.task-name-display, .task-time-display, .task-priority-display, .edit-task').forEach(el => el.classList.remove('hidden'));
                }
            });
        }

        if (e.target.classList.contains('delete-task')) {
            if (confirm('本当にこのタスクを削除しますか？')) {
                const row = e.target.closest('tr');
                const taskId = row.dataset.taskId;

                fetch(`/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                    }
                });
            }
        }
    });
});
</script>
@endsection