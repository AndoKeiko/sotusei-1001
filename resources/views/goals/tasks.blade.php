@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">{{ $goal->name }} のタスク一覧</h1>

        <p class="mb-4">目標期間: {{ $goal->period_start }} ~ {{ $goal->period_end }}</p>
        <p class="mb-4">予想時間は少な目だと思いますので、考慮の上調整してください</p>

        @if ($tasks->isEmpty())
        <p class="text-gray-500">タスクはまだありません。</p>
        @else
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
        @endif
          <a href="{{ route('goals.schedule', $goal->id) }}"
            class="inline-flex px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"> スケジュールを表示</a>
        <div class="mt-8">
          <a href="{{ route('goals.index') }}" class="text-blue-600 hover:text-blue-800">過去の目標一覧に戻る</a>
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
