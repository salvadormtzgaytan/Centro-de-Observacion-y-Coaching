@props(['group', 'checked' => false])

<label class="flex items-center gap-4 p-4 border rounded-lg cursor-pointer transition-colors
              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600
              hover:border-primary-500 @if($checked) ring-2 ring-primary-500 @endif">
    <input
        type="radio"
        name="guide_group_id"
        value="{{ $group->id }}"
        class="form-radio h-5 w-5 text-primary-600 transition focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700"
        @if($checked) checked @endif
    >
    <div class="flex-1">
        <span class="block font-semibold text-gray-900 dark:text-white">
            {{ $group->name }}
        </span>
        <span class="block text-xs text-gray-500 dark:text-gray-400">
            {{ $group->description }}
        </span>
        <span class="block text-xs text-gray-400 mt-1">
            {{ __('general.templates_count', ['count' => $group->templates->count()]) }}
        </span>
    </div>
</label>
