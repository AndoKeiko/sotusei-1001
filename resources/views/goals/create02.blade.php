@extends('layouts.app')

@section('content')
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('目標設定') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
        <form id="goalForm" method="POST" action="{{ isset($goal) ? route('goals.update', $goal->id) : route('goals.store') }}">
            @csrf
            @if (isset($goal))
              @method('PUT')
            @endif
            <input type="hidden" name="user_id" value="{{ Auth::id() }}">

            <!-- 目標名 -->
            <div class="mb-4">
              <x-input-label for="name" :value="__('目標名')" />
              <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $goal->name ?? '') }}" required autofocus />
              <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- 現在の状況 -->
            <div class="mb-4">
              <x-input-label for="current_status" :value="__('現在の状況')" />
              <x-textarea id="current_status" class="block mt-1 w-full" name="current_status">{{ old('current_status', $goal->current_status ?? '') }}</x-textarea>
              <x-input-error :messages="$errors->get('current_status')" class="mt-2" />
            </div>

            <!-- 開始日 -->
            <div class="mb-4">
              <x-input-label for="period_start" :value="__('開始日')" />
              <x-text-input id="period_start" class="block mt-1 w-full" type="date" name="period_start" value="{{ old('period_start', $goal->period_start ?? '') }}" required />
              <x-input-error :messages="$errors->get('period_start')" class="mt-2" />
            </div>

            <!-- 終了日 -->
            <div class="mb-4">
              <x-input-label for="period_end" :value="__('終了日')" />
              <x-text-input id="period_end" class="block mt-1 w-full" type="date" name="period_end" value="{{ old('period_end', $goal->period_end ?? '') }}" required />
              <x-input-error :messages="$errors->get('period_end')" class="mt-2" />
            </div>

            <!-- 詳細説明 -->
            <div class="mb-4">
              <x-input-label for="description" :value="__('詳細説明')" />
              <x-textarea id="description" class="block mt-1 w-full" name="description">{{ old('description', $goal->description ?? '') }}</x-textarea>
              <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <!-- 追加情報 -->
            <div class="mb-4">
              <x-input-label for="additional_info" :value="__('追加情報')" />
              <x-textarea id="additional_info" class="block mt-1 w-full" name="additional_info">{{ old('additional_info', $goal->additional_info ?? '') }}</x-textarea>
              <x-input-error :messages="$errors->get('additional_info')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
              <button id="submitButton" type="submit" class="ml-4">
                {{ isset($goal) ? '目標を更新' : '目標を作成' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="animate-spin rounded-full h-32 w-32 border-t-2 border-b-2 border-gray-900"></div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitButton = document.getElementById('submitButton');
    const form = document.getElementById('goalForm');
    if (submitButton && form) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loadingOverlay').classList.remove('hidden');
            form.submit();
        });
    } else {
        console.error('フォームまたはボタンが見つかりません');
    }
});
</script>
@endpush
