@props([
    'profile' => 'nav',
    'contentKey' => 'link',
    'linkType' => '',
    'contentId' => null,
    'contentSearch' => '',
    'selectedContentTitle' => '',
    'showContentResults' => false,
    'linkTypeOptions' => [],
    'contentResults' => null,
])

@php
    use App\Support\CtaLink;
    use Illuminate\Support\Collection;

    $options = filled($linkTypeOptions) ? $linkTypeOptions : CtaLink::linkTypeOptions($profile);
    $results = $contentResults instanceof Collection ? $contentResults : collect();
    $linkTypeLabel = $profile === 'block' ? 'نوع الرابط *' : 'نوع الرابط';
    $nameField = $profile === 'block' ? 'title' : 'label';
    $nameLabel = $profile === 'block' ? 'عنوان الرابط' : 'اسم الرابط';
@endphp

<div {{ $attributes->class('space-y-2') }}>
    <ui:select name="linkType" :label="$linkTypeLabel" :options="$options" live />

    <ui:input
        :name="$nameField"
        :label="$nameLabel"
        placeholder="{{ CtaLink::linkNamePlaceholder($linkType, $profile) }}"
        info="{{ CtaLink::linkNameHint($linkType, $profile) }}"
    />

    @if ($profile === 'block')
        <ui:textarea name="description" label="الوصف" placeholder="وصف قصير يظهر تحت العنوان" rows="3" />
    @endif

    @if (CtaLink::isExternalLink($linkType))
        <ui:input name="url" label="الرابط" placeholder="https://..." dir="ltr" />
        <ui:input
            name="icon"
            label="الأيقونة"
            placeholder="hugeicons:calendar-add-01"
            dir="ltr"
            info="اسم أيقونة من مكتبة iconify"
        />
    @endif

    @if (CtaLink::needsContentPicker($linkType))
        <div class="space-y-2">
            @if ($contentId && $selectedContentTitle)
                <ui:field name="contentId" label="{{ CtaLink::contentPickerLabel($linkType) }} *">
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                        <p class="text-sm font-medium text-gray-800">{{ $selectedContentTitle }}</p>
                        <button
                            type="button"
                            wire:click="clearContentSelection"
                            class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50"
                        >
                            تغيير
                        </button>
                    </div>
                </ui:field>
            @else
                <div class="relative">
                    <ui:field name="contentSearch" label="{{ CtaLink::contentPickerLabel($linkType) }} *">
                        <div class="relative">
                            <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                                <ui:icon name="search" class="text-gray-400" />
                            </div>
                            <input
                                wire:model.live.debounce.300ms="contentSearch"
                                wire:focus="showRecentContent"
                                type="text"
                                placeholder="ابحث بالاسم..."
                                class="block w-full rounded-lg py-2 ps-10 text-gray-800 border border-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm @error('contentId') border-red-300 @enderror"
                            >
                        </div>
                    </ui:field>

                    @if ($showContentResults && $results->isNotEmpty())
                        <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach ($results as $result)
                                <button
                                    type="button"
                                    wire:click="selectContent({{ $result->id }})"
                                    wire:key="{{ $contentKey }}-content-{{ $result->id }}"
                                    class="w-full text-start px-3 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0"
                                >
                                    <p class="text-sm font-semibold text-gray-800">{{ $result->title }}</p>
                                </button>
                            @endforeach
                        </div>
                    @elseif ($showContentResults && mb_strlen(trim($contentSearch)) >= 2)
                        <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500">
                            لا توجد نتائج.
                        </div>
                    @endif

                    @error('contentId')
                        <small class="text-red-600 text-xs px-1">{{ $message }}</small>
                    @enderror
                </div>
            @endif
        </div>
    @endif
</div>
