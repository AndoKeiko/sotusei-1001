<!-- resources/views/goals/create.blade.php -->

@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h2 class="text-xl font-semibold mb-6">新しい目標を作成</h2>

        <form action="{{ route('goals.store') }}" method="POST" class="space-y-4">
          @csrf
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">目標名</label>
            <input type="text" name="name" id="name" value="{{ old('name', $lastGoal->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div>
            <label for="current_status" class="block text-sm font-medium text-gray-700">現在の状況</label>
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
            <label for="work_start_time" class="block text-sm font-medium text-gray-700">開始時間</label>
            <input type="time" name="work_start_time" id="work_start_time" value="{{ old('work_start_time', $lastGoal->work_start_time ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div>
            <label for="daily_work_hours" class="block text-sm font-medium text-gray-700">1日の作業時間（時間）</label>
            <input type="number" name="daily_work_hours" id="daily_work_hours" value="{{ old('daily_work_hours', $lastGoal->daily_work_hours ?? '') }}" min="0" step="0.5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <div class="mt-6">
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-4 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
              目標を作成してタスクを生成
            </button>
          </div>
        </form>
        <div class="mt-8">
          <a href="{{ route('goals.index') }}" class="text-black hover:text-black">目標一覧に戻る</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection