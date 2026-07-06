<ui:container title="{{ __('Orders') }} / رد #{{ $submission->id }}"
    backRoute="{{ route('admin.orders.home', ['tab' => 'form-submissions']) }}">

    @php
        $submittedAt = $submission->submitted_at ?? $submission->created_at;
        $fields = $submission->fields();
        $formTabId = \App\Support\ContentType::fromConfig('forms', config('content-types.forms'))->tabId();
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- الشريط الجانبي --}}
        <div class="space-y-6 lg:order-1">

            {{-- ملخص الرد --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="clipboard-list" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">ملخص الرد</h2>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">الحالة</span>
                        <ui:badge color="{{ $submission->statusBadgeColor() }}" size="sm">
                            {{ $submission->statusLabel() }}
                        </ui:badge>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">عدد الحقول</span>
                        <span class="font-medium text-gray-800">{{ count($fields) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">عدد الردود</span>
                        <span class="font-medium text-gray-800">{{ $replies->count() }}</span>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800">رقم الرد</span>
                            <span class="text-xl font-bold text-primary-700">#{{ $submission->id }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">تاريخ الإرسال</span>
                            <span class="font-medium text-gray-800">
                                {{ $submittedAt->translatedFormat('d M Y') }}
                            </span>
                        </div>
                        @if ($submission->read_at)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">تاريخ القراءة</span>
                                <span class="font-medium text-emerald-700">
                                    {{ $submission->read_at->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- العميل --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="user" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">العميل</h2>
                </div>
                <div class="p-5">
                    @if ($submission->client)
                        <div class="flex items-center gap-3">
                            <img src="{{ $submission->client->avatar }}" alt="{{ $submission->client->name }}"
                                class="h-12 w-12 shrink-0 rounded-full bg-gray-100 object-cover">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $submission->client->name }}</p>
                                @if ($submission->client->email)
                                    <p class="truncate text-sm text-gray-500">{{ $submission->client->email }}</p>
                                @endif
                                @if ($submission->client->phone)
                                    <p class="text-sm text-gray-500" dir="ltr">{{ $submission->client->phone }}</p>
                                @endif
                            </div>
                        </div>

                        <ui:button
                            href="{{ route('admin.clients.detail', ['id' => $submission->client->uuid]) }}"
                            label="عرض ملف العميل"
                            variant="outline"
                            class="mt-4 w-full"
                            wire:navigate
                        />
                    @else
                        <div class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                <ui:icon name="user" class="h-6 w-6" />
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">{{ __('Guest') }}</p>
                            <p class="mt-1 text-xs text-gray-400">رد بدون حساب عميل</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- النموذج --}}
            @if ($submission->form)
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="forms" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">النموذج</h2>
                    </div>
                    <div class="p-5">
                        <p class="text-sm font-semibold text-gray-900">{{ $submission->form->title }}</p>
                        <ui:button
                            href="{{ route('admin.page.home', ['tab' => $formTabId, 'item' => $submission->form->uuid]) }}"
                            label="فتح النموذج"
                            icon="external-link"
                            variant="outline"
                            class="mt-4 w-full"
                            wire:navigate
                        />
                    </div>
                </section>
            @endif
        </div>

        {{-- المحتوى الرئيسي --}}
        <div class="space-y-6 lg:order-2 lg:col-span-2">

            {{-- تفاصيل الرد --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="clipboard-list" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">تفاصيل الرد</h2>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">رقم الرد</dt>
                            <dd class="text-sm font-semibold text-gray-900">#{{ $submission->id }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">الحالة</dt>
                            <dd>
                                <ui:badge color="{{ $submission->statusBadgeColor() }}" size="sm">
                                    {{ $submission->statusLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">تاريخ الإرسال</dt>
                            <dd class="text-sm text-gray-800">
                                {{ $submittedAt->translatedFormat('d M Y') }}
                                <span class="text-gray-400" dir="ltr">{{ $submittedAt->translatedFormat('h:i A') }}</span>
                            </dd>
                        </div>
                        @if ($submission->read_at)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">تاريخ القراءة</dt>
                                <dd class="text-sm text-emerald-700">
                                    {{ $submission->read_at->translatedFormat('d M Y') }}
                                    <span class="text-emerald-500" dir="ltr">{{ $submission->read_at->translatedFormat('h:i A') }}</span>
                                </dd>
                            </div>
                        @endif
                        @if ($submission->form)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">النموذج</dt>
                                <dd>
                                    <a href="{{ route('admin.page.home', ['tab' => $formTabId, 'item' => $submission->form->uuid]) }}"
                                        wire:navigate
                                        class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                        {{ $submission->form->title }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">العميل</dt>
                            <dd class="text-sm text-gray-800">
                                @if ($submission->client)
                                    <a href="{{ route('admin.clients.detail', ['id' => $submission->client->uuid]) }}"
                                        wire:navigate
                                        class="font-medium text-primary-600 hover:text-primary-700">
                                        {{ $submission->client->name }}
                                    </a>
                                @else
                                    {{ __('Guest') }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- بيانات الرد --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ui:icon name="list-details" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">
                            بيانات الرد ({{ count($fields) }})
                        </h2>
                    </div>
                </div>

                @if ($fields === [])
                    <div class="p-5">
                        <ui:empty subtitle="لم يُرسل أي حقل مع هذا الرد.">
                            لا توجد حقول في هذا الرد.
                            <x-slot:icon>
                                <ui:icon name="clipboard-list" class="!h-12 !w-12 p-0.5 text-gray-400" />
                            </x-slot:icon>
                        </ui:empty>
                    </div>
                @else
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            @foreach ($fields as $field)
                                @php
                                    $label = filled($field['label'] ?? null) ? $field['label'] : ($field['name'] ?? 'حقل');
                                    $value = $field['value'] ?? null;
                                    $type = $field['type'] ?? 'text';
                                @endphp
                                <div class="{{ in_array($type, ['textarea', 'file'], true) && filled($value) ? 'sm:col-span-2' : '' }}">
                                    <dt class="mb-1 text-xs text-gray-400">{{ $label }}</dt>
                                    <dd class="text-sm text-gray-800">
                                        @if ($type === 'checkbox')
                                            <ui:badge color="{{ $value ? 'green' : 'gray' }}" size="sm">
                                                {{ $value ? 'نعم' : 'لا' }}
                                            </ui:badge>
                                        @elseif ($type === 'file' && filled($value))
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($value) }}"
                                                target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 font-medium text-primary-600 hover:text-primary-700">
                                                <ui:icon name="paperclip" class="h-4 w-4" />
                                                عرض الملف
                                            </a>
                                        @elseif (filled($value))
                                            <p class="whitespace-pre-wrap break-words" @if (in_array($type, ['email', 'tel', 'number', 'date'], true)) dir="ltr" @endif>{{ $value }}</p>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </section>

            {{-- الردود --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ui:icon name="message-2" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">الردود</h2>
                    </div>
                    @if ($replies->isNotEmpty())
                        <span class="text-xs text-gray-400">
                            {{ $replies->count() }} {{ $replies->count() === 1 ? 'رد' : 'ردود' }}
                        </span>
                    @endif
                </div>

                <div class="p-5">
                    @if ($replies->isEmpty())
                        <ui:empty subtitle="أضف رداً للعميل أو للفريق.">
                            لا توجد ردود بعد.
                            <x-slot:icon>
                                <ui:icon name="message-2" class="!h-12 !w-12 p-0.5 text-gray-400" />
                            </x-slot:icon>
                        </ui:empty>
                    @else
                        <div class="divide-y divide-gray-50">
                            @foreach ($replies as $reply)
                                <div wire:key="reply-{{ $reply->id }}"
                                    class="flex gap-4 py-4 first:pt-0 last:pb-0">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-sm font-bold text-primary-700">
                                        {{ mb_substr($reply->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-800">{{ $reply->user?->name ?? __('Admin') }}</p>
                                                <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $reply->body }}</p>
                                            </div>
                                            <p class="shrink-0 text-xs text-gray-400">
                                                {{ $reply->created_at->translatedFormat('d M Y') }}
                                                <span dir="ltr">{{ $reply->created_at->translatedFormat('h:i A') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form wire:submit="submitReply" class="mt-6 space-y-4 border-t border-gray-100 pt-5">
                        <ui:textarea
                            name="replyBody"
                            label="إضافة رد"
                            placeholder="اكتب ردك هنا..."
                            rows="4"
                        />
                        <div class="flex justify-end">
                            <ui:button type="submit" label="إرسال الرد" icon="send" wire:loading.attr="disabled" />
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

</ui:container>

<?php

use App\Models\FormSubmission;
use App\Models\FormSubmissionReply;

new class extends \Livewire\Component {
    public FormSubmission $submission;

    public string $replyBody = '';

    public function mount(int|string $id): void
    {
        $query = FormSubmission::query()
            ->with(['form', 'client']);

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        $this->submission = $query->whereKey($id)->firstOrFail();
        $this->submission->markAsRead();
    }

    public function submitReply(): void
    {
        $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'replyBody.required' => 'يرجى كتابة الرد.',
            'replyBody.min' => 'الرد قصير جداً.',
            'replyBody.max' => 'الرد طويل جداً.',
        ]);

        FormSubmissionReply::query()->create([
            'form_submission_id' => $this->submission->id,
            'user_id' => auth()->id(),
            'body' => trim($this->replyBody),
        ]);

        $this->replyBody = '';
        $this->submission->load('replies.user');
    }

    public function with(): array
    {
        return [
            'replies' => $this->submission->replies()->with('user')->get(),
        ];
    }

    public function rendering($view): void
    {
        $view->title(__('Orders').' / رد #'.$this->submission->id)->layout('admin::layout');
    }
}; ?>
