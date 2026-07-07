<div class="divide-y divide-gray-200 divide-dotted">
    <div class="bg-gray-100 p-3 flex items-center gap-x-4 w-full">
        <div class="ps-3" x-cloak>
            <div class="flex items-center">
                <ui:check-all />
            </div>
        </div>
        <div class="flex-grow">
            <div class="relative text-sm text-gray-800">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="text-gray-400" />
                </div>

                <input wire:model.live="search" type="text" placeholder="ابحث .."
                    class="block w-full rounded-lg py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6">
            </div>
        </div>
        <div x-show="$wire.selectedIds.length > 0" x-cloak class="flex items-center gap-x-2">
            <div class="flex items-center gap-1 text-sm text-gray-600">
                <span x-text="$wire.selectedIds.length"></span>
                <span>محددة</span>
            </div>

            <button wire:click="deleteSelected" wire:confirm="هل أنت متأكد من حذف العناصر المحددة؟"
                class="flex items-center gap-2 rounded-lg border px-3 py-1.5 bg-white text-sm text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-75">
                <ui:icon name="trash" wire:loading.remove class="text-gray-300 w-4 h-4" />
                <ui:icon name="loader-3" wire:loading wire:target="deleteSelected"
                    class="animate-spin text-gray-300 w-4 h-4" />
                {{ __('Delete selected') }}<span x-text="'('+$wire.selectedIds.length+')'"></span>
            </button>
        </div>
        <div>
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-newsletter' })" label="نشرة جديدة"
                icon="square-rounded-plus" />
        </div>

        <ui:modal title="إضافة نشرة بريدية جديدة" size="2xl" name="add-newsletter">
            <livewire:admin::page.content.newsletter.add-newsletter :contentType="$contentType" />
        </ui:modal>
    </div>

    <div class="relative overflow-x-auto">
        @if ($results->count() === 0)
            <ui:empty subtitle="سيتم عرض النشرات البريدية هنا بعد إضافتها.">
                لا توجد نشرات بريدية.
                <x-slot:icon>
                    <img src="{{ asset($contentType['icon']) }}" class="w-12 h-12 opacity-50" alt="">
                </x-slot:icon>
            </ui:empty>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-start font-medium w-10"></th>
                        <th class="px-4 py-3 text-start font-medium">النشرة</th>
                        <th class="px-4 py-3 text-start font-medium">حالة الإرسال</th>
                        <th class="px-4 py-3 text-start font-medium">التاريخ</th>
                        <th class="px-4 py-3 text-start font-medium">المستلمين</th>
                        <th class="px-4 py-3 text-start font-medium">الموقع</th>
                        <th class="px-4 py-3 text-end font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($results as $item)
                        @php
                            $mailStatus = $item->newsletterMailStatus();
                            $mailStatusClass = match ($mailStatus) {
                                'sent' => 'bg-emerald-100 text-emerald-700',
                                'scheduled' => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                            $displayDate = $item->newsletterSentAt() ?? $item->newsletterScheduledAt();
                            $dateLabel = match ($mailStatus) {
                                'sent' => 'أُرسلت',
                                'scheduled' => 'مجدولة',
                                default => null,
                            };
                        @endphp
                        <tr wire:key="newsletter-{{ $item->uuid }}" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input wire:model="selectedIds" value="{{ $item->id }}" type="checkbox"
                                    class="rounded-xl border-gray-300 shadow-sm w-4 h-4">
                            </td>
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.page.home', ['tab' => $contentType['tab_id'], 'item' => $item->uuid]) }}"
                                    wire:navigate
                                    class="block min-w-0 hover:text-primary-600 transition"
                                >
                                    <p class="font-medium text-gray-800 truncate">{{ $item->title }}</p>
                                    @if (filled(data_get($item->data, 'subject')))
                                        <p class="mt-0.5 text-xs text-gray-500 truncate">
                                            {{ data_get($item->data, 'subject') }}
                                        </p>
                                    @endif
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $mailStatusClass }}">
                                    {{ $item->newsletter_mail_status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-gray-600 whitespace-nowrap">
                                @if ($displayDate)
                                    <span class="block text-xs text-gray-400">{{ $dateLabel }}</span>
                                    <span dir="ltr">{{ $displayDate->translatedFormat('j M Y، H:i') }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-600">
                                @if ($item->newsletterRecipientsCount() > 0)
                                    {{ \Illuminate\Support\Number::format($item->newsletterRecipientsCount()) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($item->status === 'published')
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        منشورة
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">مسودة</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-end">
                                <ui:table-menu class="inline-block" width="w-40">
<a href="{{ route('admin.page.home', ['tab' => $contentType['tab_id'], 'item' => $item->uuid]) }}"
                                                wire:navigate
                                                @click="dropdownMenu = false"
                                                class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2">
                                                {{ __('Edit') }}
                                            </a>
</ui:table-menu>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div wire:loading wire:target="search, nextPage, previousPage, deleteSelected"
            class="absolute inset-0 bg-white opacity-50"></div>

        <div wire:loading.flex wire:target="search, nextPage, previousPage, deleteSelected"
            class="flex justify-center items-center absolute inset-0">
            <ui:icon name="loader-3" class="animate-spin text-gray-300 w-10 h-10" />
        </div>
    </div>

    @if ($results->total() > $results->perPage())
        <div class="p-4 px-6 bg-gray-50 rounded-b-2xl flex justify-between items-center">
            <div class="text-gray-500 text-sm">
                النتائج : <b>{{ \Illuminate\Support\Number::format($results->total()) }}</b>
            </div>
            {{ $results->links('ui::components.pagination') }}
        </div>
    @endif
</div>

<?php

use App\Models\Content;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new class extends Livewire\Component
{
    use WithPagination;

    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $search = '';

    /** @var array<int, string> */
    public array $selectedIds = [];

    /** @var array<int, string> */
    public array $allIdsOnPage = [];

    public function placeholder(): string
    {
        return loadingIcon();
    }

    #[On('updateNewsletterList')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    protected function applySearch($query)
    {
        return $this->search === ''
            ? $query
            : $query->where(function ($builder): void {
                $term = '%'.$this->search.'%';

                $builder
                    ->where('title', 'like', $term)
                    ->orWhere('data->subject', 'like', $term)
                    ->orWhere('data->subtitle', 'like', $term);
            });
    }

    public function delete(int $id): void
    {
        Content::query()->type(contentTypeModel($this->contentType['slug']))->whereKey($id)->first()?->delete();

        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function deleteSelected(): void
    {
        Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->whereIn('id', $this->selectedIds)
            ->get()
            ->each(fn (Content $item) => $item->delete());

        $this->selectedIds = [];
        $this->dispatch('notify', text: __('Selected items deleted successfully.'));
    }

    public function with(): array
    {
        $query = Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->orderByDesc('id');

        $query = $this->applySearch($query);

        $results = $query->paginate(20);

        $this->allIdsOnPage = $results->map(fn (Content $item): string => (string) $item->id)->toArray();

        return [
            'results' => $results,
        ];
    }
}; ?>
