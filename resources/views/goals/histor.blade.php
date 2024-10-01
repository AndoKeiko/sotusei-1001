@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">過去の目標一覧</h1>

        @if ($goals->isEmpty())
        <p class="text-gray-500">過去の目標がありません。</p>
        @else
        <ul class="space-y-4">
          @foreach ($goals as $goal)
          <li class="border-b pb-4">
            <a href="{{ route('goals.show', $goal->id) }}" class="text-blue-600 hover:underline">
              {{ $goal->name }} ({{ $goal->period_start }} - {{ $goal->period_end }})
            </a>
          </li>
          @endforeach
        </ul>
        @endif

        <div class="mt-8">
          <a href="{{ route('goals.index') }}" class="text-blue-600 hover:text-blue-800">目標一覧に戻る</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
