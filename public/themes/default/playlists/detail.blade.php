<x-tenant-theme::playlists.layout :desc="$playlist['description']">

<div class="flex items-center justify-between mb-5 px-2">
    <a href="{{route('tenant.playlists.index')}}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-left" aria-hidden="true" class="lucide lucide-arrow-left w-5 h-5 text-stone-700 "><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
    </a>
    <div class="flex items-center gap-2">
      <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="share" aria-hidden="true" class="lucide lucide-share w-5 h-5 text-stone-700 "><path d="M12 2v13"></path><path d="m16 6-4-4-4 4"></path><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path></svg>
      </button>  
      <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart h-4 w-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg> 
    </div>
    </button>
  </div>

    <section
        class="p-1 space-y-4"
        x-data='{
            playlist: null,
            activeItemIndex: 0,
            get items() {
                return Array.isArray(this.playlist?.items) ? this.playlist.items : [];
            },
            get activeItem() {
                return this.items[this.activeItemIndex] ?? { image: "", name: "", media: "", description: "" };
            },
            selectItem(index, autoPlay = true) {
                this.activeItemIndex = index;
                if (autoPlay) {
                    this.playCurrent();
                }
            },
            playAll() {
                this.activeItemIndex = 0;
                this.playCurrent();
            },
            playPrevious() {
                if (this.activeItemIndex === 0) {
                    return;
                }

                this.activeItemIndex -= 1;
                this.playCurrent();
            },
            playNext() {
                if (this.activeItemIndex >= this.items.length - 1) {
                    return;
                }

                this.activeItemIndex += 1;
                this.playCurrent();
            },
            playCurrent() {
                this.$nextTick(() => {
                    const player = this.playlist.type === "video" ? this.$refs.videoPlayer : this.$refs.audioPlayer;
                    if (!player || !this.activeItem.media) {
                        return;
                    }

                    player.currentTime = 0;
                    player.play().catch(() => {});
                });
            }
        }'
        x-init="playlist = @js($playlist)"
    >
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
            <div class="border-b border-stone-100 p-4 md:p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-3">
                        <img :src="playlist.image" :alt="playlist.name" class="h-16 w-16 rounded-xl object-cover">
                        <div>
                            <div class="mb-1 flex items-center gap-2">
                                <h2 class="text-lg font-bold text-stone-900" x-text="playlist.name"></h2>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="playlist.type === 'video' ? 'bg-blue-100 text-blue-700' : 'bg-violet-100 text-violet-700'"
                                    x-text="playlist.type === 'video' ? 'فيديو' : 'صوتي'"
                                ></span>
                            </div>
                            <p class="mt-1 text-sm text-stone-500" x-text="playlist.description"></p>
                            <p class="mt-2 text-xs font-semibold text-stone-600">
                                <span x-text="items.length"></span> مواد تعليمية
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-primary-600" x-on:click="playAll()">
                            <iconify-icon icon="solar:play-bold-duotone" class="text-base"></iconify-icon>
                            تشغيل الكل
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700 transition hover:bg-stone-50" x-on:click="playPrevious()">
                            <iconify-icon icon="solar:rewind-back-bold-duotone" class="text-base"></iconify-icon>
                            السابق
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700 transition hover:bg-stone-50" x-on:click="playNext()">
                            <iconify-icon icon="solar:rewind-forward-bold-duotone" class="text-base"></iconify-icon>
                            التالي
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-3 bg-stone-50 p-4 md:p-5">
                <template x-if="playlist.type === 'video'">
                    <video
                        x-ref="videoPlayer"
                        class="w-full rounded-2xl bg-black"
                        controls
                        preload="metadata"
                        x-bind:poster="activeItem.image"
                        x-bind:src="activeItem.media"
                    ></video>
                </template>

                <template x-if="playlist.type === 'audio'">
                    <div class="rounded-2xl bg-gradient-to-br from-violet-500 via-indigo-500 to-primary-500 p-4 text-white md:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <img :src="activeItem.image" :alt="activeItem.name" class="h-14 w-14 rounded-xl object-cover ring-2 ring-white/40">
                            <div>
                                <p class="text-xs text-white/80">قيد التشغيل الآن</p>
                                <h3 class="text-sm font-bold md:text-base" x-text="activeItem.name"></h3>
                            </div>
                        </div>
                        <audio
                            x-ref="audioPlayer"
                            class="w-full"
                            controls
                            preload="metadata"
                            x-bind:src="activeItem.media"
                        ></audio>
                    </div>
                </template>
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-bold text-stone-900 md:text-base">قائمة المواد</h3>
                <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600">
                    <span x-text="items.length ? activeItemIndex + 1 : 0"></span> / <span x-text="items.length"></span>
                </span>
            </div>

            <div class="space-y-2.5">
                <template x-for="(item, itemIndex) in items" :key="item.id">
                    <button
                        type="button"
                        class="w-full rounded-xl border p-2.5 text-start transition-all"
                        :class="activeItemIndex === itemIndex ? 'border-primary-400 bg-primary-50/50' : 'border-stone-200 bg-white hover:border-primary-200'"
                        x-on:click="selectItem(itemIndex, true)"
                    >
                        <div class="flex items-center gap-3">
                            <img :src="item.image" :alt="item.name" class="h-14 w-14 rounded-lg object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-stone-900" x-text="item.name"></p>
                                <p class="line-clamp-2 text-xs text-stone-500" x-text="item.description"></p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-stone-100 text-stone-700">
                                <iconify-icon icon="solar:play-bold-duotone" class="text-base"></iconify-icon>
                            </span>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </section>
</x-tenant-theme::playlists.layout>