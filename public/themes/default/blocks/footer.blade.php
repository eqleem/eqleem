@php
    $data = $block->data ?? [];
    $showCopyright = (bool) ($data['show_copyright'] ?? true);
    $copyrightText = filled($data['copyright_text'] ?? null)
        ? $data['copyright_text']
        : '© '.date('Y').' '.tenant()->name.' الحقوق محفوظة.';
    $showContactEmail = (bool) ($data['show_contact_email'] ?? true);
    $contactEmail = $data['contact_email'] ?? '';
    $showPoweredBy = (bool) ($data['show_powered_by'] ?? true);
    $poweredByText = $data['powered_by_text'] ?? 'صُنع بواسطة إقليم';
    $poweredByUrl = $data['powered_by_url'] ?? '';
@endphp

<div class="p-4 text-center text-stone-500 text-xs mt-3">
    @if($showCopyright)
    <p class="mb-3 text-stone-500/75">{{ $copyrightText }}</p>
    @endif

    <div class="flex flex-wrap justify-center items-center gap-x-5 gap-y-2 text-sm">
        @if($showContactEmail && filled($contactEmail))
        <a href="mailto:{{ $contactEmail }}" dir="ltr" class="hover:text-stone-900 transition">{{ $contactEmail }}</a>
        @endif

        @if($showPoweredBy && filled($poweredByText))
        <a href="{{ $poweredByUrl ?: '#' }}" @if(filled($poweredByUrl)) target="_blank" rel="noopener noreferrer" @endif class="hover:text-stone-900 transition">{{ $poweredByText }}</a>
        @endif
    </div>
</div>
