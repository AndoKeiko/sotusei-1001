<!-- resources/views/goals/create.blade.php -->

@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h2 class="text-xl font-semibold mb-6">新しい目標を作成</h2>

        <form method="POST" action="{{ route('goals.store') }}" class="space-y-4">
          @csrf
          @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="form-group">
            <label for="name" class="block text-sm font-medium text-gray-700">目標名</label>
            <input type="text" class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="name" name="name" required>
          </div>
          <div class="form-group">
            <label for="current_status">現在の状況</label>
            <textarea class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="current_status" name="current_status" required></textarea>
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">説明</label>
            <textarea name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
          </div>
          <div class="form-group">
            <label for="period_start">開始日</label>
            <input type="date" class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="period_start" name="period_start" required>
          </div>
          <div class="form-group">
            <label for="period_end">終了日</label>
            <input type="date" class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="period_end" name="period_end" required>
          </div>
          <div class="form-group">
            <label for="work_start_time">作業開始時間</label>
            <input type="time" class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="work_start_time" name="work_start_time" min="0" required>
          </div>
          <div class="form-group">
            <label for="work_hours_per_day">1日の作業時間</label>
            <input type="number" class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="work_hours_per_day" name="work_hours_per_day" step="0.5" min="0" max="24" required>
          </div>
          <div class="pt-4">
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