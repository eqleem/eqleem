@props([
    'active' => '',
    'nav' => '',
    'content' => '',
    'urlKey' => null,
    'validTabs' => [],
])

<div
    x-data="{
        activeTab: @js($active),
        defaultTab: @js($active),
        urlKey: @js($urlKey),
        validTabs: @js($validTabs),
        init() {
            if (this.urlKey) {
                this.syncFromUrl();
                window.addEventListener('popstate', () => this.syncFromUrl());
            }
        },
        syncFromUrl() {
            const param = new URLSearchParams(window.location.search).get(this.urlKey);

            if (param && (this.validTabs.length === 0 || this.validTabs.includes(param))) {
                this.activeTab = param;

                return;
            }

            this.activeTab = this.defaultTab;
        },
        setTab(id) {
            this.activeTab = id;

            if (! this.urlKey) {
                return;
            }

            const url = new URL(window.location.href);

            if (id === this.defaultTab) {
                url.searchParams.delete(this.urlKey);
            } else {
                url.searchParams.set(this.urlKey, id);
            }

            history.pushState({ [this.urlKey]: id }, '', url);
        },
    }"
    {{ $attributes->class(['mb-5x rounded-b-2xl']) }}
>
    <div {{ $nav->attributes->class(['whitespace-nowrap text-sm  rounded-t-lg text-gray-600 overflow-x-auto']) }}>
        {{ $nav }}
    </div>
    @if ($content)
        <div {{ $content->attributes->class(['[&>*:first-child]:rounded-ts-none']) }}>
            {{ $content }}
        </div>
    @else
        <div class="[&>*:first-child]:rounded-ts-none rounded-b-2xl">
            {{ $slot }}
        </div>
    @endif
</div>
