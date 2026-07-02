<ui:container title="{{ __('Orders') }} / رد #{{ $submission->id }}"
    backRoute="{{ route('admin.orders.home', ['tab' => 'form-submissions']) }}">

    @php
        $submittedAt = $submission->submitted_at ?? $submission->created_at;
        $fields = $submission->fields();
        $formTabId = \App\Support\ContentType::fromConfig('forms', config('content-types.forms'))->tabId();
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <section class="relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-primary-50/80 via-white to-gray-50"></div>
            <div class="pointer-events-none absolute -top-20 start-0 h-48 w-48 rounded-full bg-primary-100/50 blur-3xl"></div>

            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">
                                <ui:icon name="clipboard-list" class="h-3.5 w-3.5" />
                                رد نموذج
                            </span>
                            <ui:badge color="{{ $submission->statusBadgeColor() }}" size="sm">
                                {{ $submission->statusLabel() }}
                            </ui:badge>
                            @if ($submission->form)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                    {{ $submission->form->title }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">رقم الرد</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                #{{ $submission->id }}
                            </h1>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-600">
                            <span class="inline-flex items-center gap-1.5">
                                <ui:icon name="calendar" class="h-4 w-4 text-gray-400" />
                                {{ $submittedAt->translatedFormat('d M Y') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5" dir="ltr">
                                <ui:icon name="clock" class="h-4 w-4 text-gray-400" />
                                {{ $submittedAt->translatedFormat('h:i A') }}
                            </span>
                            @if ($submission->read_at)
                                <span class="inline-flex items-center gap-1.5 text-emerald-700">
                                    <ui:icon name="checks" class="h-4 w-4" />
                                    قُرئ {{ $submission->read_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($submission->form)
                        <div class="shrink-0 rounded-xl border border-gray-200 bg-white/90 p-4 sm:min-w-56">
                            <a href="{{ route('admin.page.home', ['tab' => $formTabId, 'item' => $submission->form->uuid]) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700">
                                <ui:icon name="external-link" class="!w-4 !h-4" />
                                فتح النموذج
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Main content --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Submission fields --}}
                <ui:box title="بيانات الرد" icon="clipboard-list">
                    @if ($fields === [])
                        <p class="text-sm text-gray-500">لا توجد حقول في هذا الرد.</p>
                    @else
                        <dl class="grid gap-4 sm:grid-cols-2">
                            @foreach ($fields as $field)
                                @php
                                    $label = filled($field['label'] ?? null) ? $field['label'] : ($field['name'] ?? 'حقل');
                                    $value = $field['value'] ?? null;
                                    $type = $field['type'] ?? 'text';
                                @endphp
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                                    <dd class="mt-2 text-sm text-gray-900">
                                        @if ($type === 'checkbox')
                                            <ui:badge color="{{ $value ? 'green' : 'gray' }}" size="sm">
                                                {{ $value ? 'نعم' : 'لا' }}
                                            </ui:badge>
                                        @elseif ($type === 'file' && filled($value))
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($value) }}"
                                                target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 font-medium text-primary-600 hover:text-primary-700">
                                                <ui:icon name="Paperclip" class="!w-4 !h-4" />
                                                عرض الملف
                                            </a>
                                        @elseif (filled($value))
                                            <p class="whitespace-pre-wrap break-words @if (in_array($type, ['email', 'tel', 'number', 'date'], true)) dir="ltr" @endif">{{ $value }}</p>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </ui:box>

                {{-- Replies thread --}}
                <ui:box title="الردود" icon="message-2" class="">
                    @if ($replies->isEmpty())
                        <p class="mb-4 text-sm text-gray-500">لا توجد ردود بعد. أضف رداً للعميل أو للفريق.</p>
                    @else
                        <div class="mb-6 space-y-4">
                            @foreach ($replies as $reply)
                                <div wire:key="reply-{{ $reply->id }}"
                                    class="flex gap-3 rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700">
                                        {{ mb_substr($reply->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span class="text-sm font-semibold text-gray-800">{{ $reply->user?->name ?? __('Admin') }}</span>
                                            <span class="text-xs text-gray-400">{{ $reply->created_at->translatedFormat('d M Y · h:i A') }}</span>
                                        </div>
                                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $reply->body }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form wire:submit="submitReply" class="space-y-4 border-t border-gray-100 pt-4">
                        <ui:textarea
                            name="replyBody"
                             
                            placeholder="اكتب ردك هنا..."
                            rows="4"
                        />
                        <div class="flex justify-end">
                            <ui:button type="submit" label="إرسال الرد" icon="send" wire:loading.attr="disabled" />
                        </div>
                    </form>
                </ui:box>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <ui:box title="العميل" icon="user">
                    @if ($submission->client)
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-base font-bold text-gray-600">
                                    {{ mb_substr($submission->client->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-800">{{ $submission->client->name }}</p>
                                    <p class="text-xs text-gray-400">عميل</p>
                                </div>
                            </div>
                            @if ($submission->client->email)
                                <div class="rounded-lg bg-gray-50 px-3 py-2">
                                    <p class="text-xs text-gray-400">البريد</p>
                                    <p class="text-sm text-gray-800" dir="ltr">{{ $submission->client->email }}</p>
                                </div>
                            @endif
                            @if ($submission->client->phone)
                                <div class="rounded-lg bg-gray-50 px-3 py-2">
                                    <p class="text-xs text-gray-400">الجوال</p>
                                    <p class="text-sm text-gray-800" dir="ltr">{{ $submission->client->phone }}</p>
                                </div>
                            @endif
                            <a href="{{ route('admin.clients.detail', ['id' => $submission->client->uuid]) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700">
                                <ui:icon name="external-link" class="!w-4 !h-4" />
                                عرض العميل
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">{{ __('Guest') }}</p>
                    @endif
                </ui:box>

                <ui:box title="معلومات إضافية" icon="info-circle">
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500">الحالة</dt>
                            <dd>
                                <ui:badge color="{{ $submission->statusBadgeColor() }}" size="sm">
                                    {{ $submission->statusLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500">تاريخ الإرسال</dt>
                            <dd class="font-medium text-gray-800">{{ $submittedAt->translatedFormat('d M Y h:i A') }}</dd>
                        </div>
                        @if ($submission->read_at)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">تاريخ القراءة</dt>
                                <dd class="font-medium text-gray-800">{{ $submission->read_at->translatedFormat('d M Y h:i A') }}</dd>
                            </div>
                        @endif
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500">عدد الردود</dt>
                            <dd class="font-medium text-gray-800">{{ $replies->count() }}</dd>
                        </div>
                    </dl>
                </ui:box>
            </div>
        </div>
    </div>

</ui:container>

<?php

use App\Models\FormSubmission;
use App\Models\FormSubmissionReply;

new class extends \Livewire\Component {
    public FormSubmission $submission;

    public string $replyBody = '';

    public function mount(): void
    {
        $query = FormSubmission::query()
            ->with(['form', 'client']);

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        $this->submission = $query->whereKey(request()->id)->firstOrFail();
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
