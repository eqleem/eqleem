@props([
    'name' => 'body',
    'editorModeName' => 'editorMode',
    'modelId' => null,
    'modelType' => 'content',
    'mediaCollection' => 'editor-images',
])

<div x-data="{ mode: @entangle($editorModeName) }" class="w-full space-y-2">
    <div class="flex items-center gap-2 p-1 bg-gray-100/75 rounded-md">
        <button
            type="button"
            x-on:click="mode = 'markdown'"
            x-bind:class="mode === 'markdown'
                ? 'bg-white text-gray-800 shadow-sm font-semibold'
                : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 px-3 py-2 rounded-md text-sm transition"
        >
            <ui:icon name="Markdown" class="!w-4 !h-4" />
            محرر Markdown
        </button>
        <button
            type="button"
            x-on:click="mode = 'html'"
            x-bind:class="mode === 'html'
                ? 'bg-white text-gray-800 shadow-sm font-semibold'
                : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 px-3 py-2 rounded-md text-sm transition"
        >
            <ui:icon name="list" class="!w-4 !h-4" />
            للمحرر الإفتراضي
        </button>
    </div>

    <div x-show="mode === 'markdown'" x-cloak>
        <ui:textarea
            :name="$name"
            placeholder="اكتب شيئاً .."
            dir="auto"
            class="min-h-[280px]"
        />
    </div>

    <div x-show="mode === 'html'" x-cloak>
        <ui:ck
            :name="$name"
            :model-id="$modelId"
            :model-type="$modelType"
            :media-collection="$mediaCollection"
            :min-height="35"
        />
    </div>
</div>
