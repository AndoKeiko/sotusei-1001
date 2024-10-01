@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">目標を編集</h1>

        <form action="{{ route('goals.update', $goal->id) }}" method="POST" class="space-y-4">
          @csrf
          @method('PUT')

          <!-- フォームフィールド -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">目標名</label>
            <input type="text" name="name" id="name" value="{{ old('name', $goal->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div>
            <label for="current_status" class="block text-sm font-medium text-gray-700">現状</label>
            <textarea name="current_status" id="current_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('current_status', $goal->current_status) }}</textarea>
          </div>

          <div>
            <label for="period_start" class="block text-sm font-medium text-gray-700">開始日</label>
            <input type="date" name="period_start" id="period_start" value="{{ old('period_start', $goal->period_start) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div>
            <label for="period_end" class="block text-sm font-medium text-gray-700">終了日</label>
            <input type="date" name="period_end" id="period_end" value="{{ old('period_end', $goal->period_end) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div>
            <label for="start_time" class="block text-sm font-medium text-gray-700">開始時間</label>
            <input type="time" name="work_start_time" id="work_start_time" value="{{ old('work_start_time', $goal->work_start_time) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div>
            <label for="work_hours_per_day" class="block text-sm font-medium text-gray-700">1日の作業時間</label>
            <input type="number" name="work_hours_per_day" id="work_hours_per_day" value="{{ old('work_hours_per_day', $goal->work_hours_per_day) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div class="mt-6">
            <button type="submit" class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
              目標を更新
            </button>
          </div>
        </form>

        <div class="mt-8">
          <a href="{{ route('goals.index') }}" class="text-blue-600 hover:text-blue-800">過去の目標一覧に戻る</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection