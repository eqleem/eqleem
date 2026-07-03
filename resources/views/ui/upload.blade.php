@props([
    'value' => null,
    'name' => null,
    'file' => null,
    'accept' => 'image/jpg,image/jpeg,image/png,application/pdf',
    'multiple' => false,
    'maxFiles' => 20,
    'block' => false,
    'label' => null,
    'uploadLabel' => null,
    'mode' => 'image',
    'options' => [],
    'collection' => 'theme-media',
    'mediaPath' => null,
    'modelId' => null,
    'modelType' => null,
    'profileClass' => 'w-20 h-20 rounded-full',
    'addMethod' => 'addImage',
    'removeMethod' => 'removeImage',
    'sortable' => false,
    'reorderMethod' => 'reorderImages',
])

@php
    $isMultiple = filter_var($multiple, FILTER_VALIDATE_BOOLEAN);
    $isSortable = filter_var($sortable, FILTER_VALIDATE_BOOLEAN) && $isMultiple;
    $uploadButtonLabel = $uploadLabel ?? ($isMultiple ? 'رفع الصور' : 'رفع الصورة');
    $usesMediaLibrary = filled($modelId) && filled($modelType);
    $storagePath = $mediaPath ?? 'tenant-media/'.(tenant('uuid') ?? 'shared').'/'.$collection;
    $uploaderId = 'uploader-'.$collection.'-'.$name;
    $triggerClass = 'openUploader'.$collection.'-'.$name;
    $targetClass = 'uploaderTarget'.$collection.'-'.$name;
    $uploadEndpoint = $usesMediaLibrary ? route('admin.upload-media') : route('admin.upload-image');

    $imageItems = $isMultiple
        ? collect((array) $value)
            ->filter(fn (mixed $item): bool => filled(is_array($item) ? ($item['url'] ?? $item['path'] ?? null) : $item))
            ->map(function (mixed $item): array {
                if (is_array($item)) {
                    $path = (string) ($item['url'] ?? $item['path'] ?? '');

                    return [
                        'id' => isset($item['id']) ? (int) $item['id'] : null,
                        'path' => $path,
                        'url' => contentImageUrl($path) ?? $path,
                    ];
                }

                $path = (string) $item;

                return [
                    'id' => null,
                    'path' => $path,
                    'url' => contentImageUrl($path) ?? $path,
                ];
            })
            ->values()
            ->all()
        : [];

    $singleImageUrl = ! $isMultiple && filled($value)
        ? (str_contains((string) $value, 'http') ? $value : contentImageUrl((string) $value) ?? \Illuminate\Support\Facades\Storage::url((string) $value))
        : null;
@endphp

<ui:field :label="$label" :block="$block">
    <div
        wire:key="upload-{{ $collection }}-{{ $name }}"
        wire:ignore
        class="relative flex flex-col gap-2"
        x-data="{
            isMultiple: @js($isMultiple),
            isSortable: @js($isSortable),
            image: @js($singleImageUrl),
            images: @js($imageItems),
            addMethod: @js($addMethod),
            removeMethod: @js($removeMethod),
            reorderMethod: @js($reorderMethod),
            wireName: @js($name),
            init() {
                const storagePath = @js($storagePath);
                const usesMediaLibrary = @js($usesMediaLibrary);
                const maxFiles = @js((int) $maxFiles);
                const triggerClass = @js('.'.$triggerClass);
                const targetClass = @js('.'.$targetClass);

                const uppy = new Uppy({
                    id: @js($uploaderId),
                    autoProceed: true,
                    restrictions: {
                        maxNumberOfFiles: this.isMultiple ? maxFiles : 1,
                        minNumberOfFiles: 1,
                        allowedFileTypes: ['image/*', '.jpg', '.png', '.gif', '.webp', '.svg'],
                        maxFileSize: 6291456,
                    },
                })
                .use(Dashboard, {
                    trigger: triggerClass,
                    id: @js($uploaderId),
                    inline: false,
                    locale: UppyAR,
                    closeAfterFinish: true,
                    singleFileFullScreen: ! this.isMultiple,
                    showLinkToFileUploadResult: false,
                    target: targetClass,
                    proudlyDisplayPoweredByUppy: false,
                })
                .use(XHR, {
                    endpoint: @js($uploadEndpoint),
                    headers: { 'X-CSRF-Token': @js(csrf_token()) },
                    fieldName: 'file',
                    allowedMetaFields: usesMediaLibrary
                        ? ['mediaCollection', 'modelType', 'modelId']
                        : ['mediaCollection'],
                })
                .use(ImageEditor, { target: Dashboard });

                uppy.on('file-added', (file) => {
                    uppy.setFileMeta(file.id, {
                        mediaCollection: usesMediaLibrary ? @js($collection) : storagePath,
                        ...(usesMediaLibrary ? {
                            modelType: @js($modelType),
                            modelId: @js((string) $modelId),
                        } : {}),
                    });
                });

                uppy.on('upload-success', (file, response) => {
                    const body = response?.body ?? {};
                    const filePath = body.filePath ?? body.url;
                    const url = body.url ?? body.filePath;

                    if (! filePath) {
                        return;
                    }

                    if (this.isMultiple) {
                        this.addGalleryImage(filePath, url, body.mediaId ?? null);
                    } else {
                        this.setSingleImage(filePath, url);
                    }
                });

                if (this.isMultiple && this.isSortable && typeof Sortable !== 'undefined') {
                    this.$watch('images.length', (length) => {
                        if (length > 0) {
                            this.$nextTick(() => this.initSortable());
                        }
                    });

                    this.$nextTick(() => this.initSortable());
                }
            },
            initSortable() {
                if (! this.$refs.gallery || typeof Sortable === 'undefined' || this.images.length === 0) {
                    return;
                }

                if (this.$refs.gallerySortable) {
                    return;
                }

                this.$refs.gallerySortable = Sortable.create(this.$refs.gallery, {
                    animation: 150,
                    handle: '.sort-handle',
                    draggable: '.sort-item',
                    onEnd: (event) => {
                        if (event.oldIndex === event.newIndex) {
                            return;
                        }

                        const moved = this.images.splice(event.oldIndex, 1)[0];
                        this.images.splice(event.newIndex, 0, moved);

                        const orderedIds = this.images
                            .map((item) => item.id)
                            .filter((id) => id != null);

                        if (orderedIds.length) {
                            $wire.call(this.reorderMethod, orderedIds);
                        }
                    },
                });
            },
            removeImage() {
                this.image = null;
                $wire.set(this.wireName, null);
            },
            removeGalleryImage(index) {
                const item = this.images[index];

                if (item?.id) {
                    $wire.call(this.removeMethod, item.id);
                } else {
                    $wire.call(this.removeMethod, index);
                }

                this.images.splice(index, 1);
            },
            addGalleryImage(path, url, mediaId = null) {
                if (! path || this.images.some((item) => item.path === path)) {
                    return;
                }

                this.images.push({ id: mediaId, path, url });
                $wire.call(this.addMethod, path);
            },
            setSingleImage(path, url) {
                this.image = url;
                $wire.set(this.wireName, path);
            }
        }"
    >
        @if ($isMultiple)
            <template x-if="images.length === 0">
                <img src="{{ asset('assets/images/image.png') }}" alt="upload" class="w-full h-40 rounded-lg object-cover">
            </template>

            <div x-ref="gallery" x-show="images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <template x-for="(item, index) in images" :key="item.id ?? item.path">
                    <div
                        class="sort-item relative group"
                        :data-media-id="item.id"
                    >
                        <img
                            :src="item.url"
                            alt="{{ __($label ?? 'Image') }}"
                            class="w-full h-28 rounded-lg object-cover bg-gray-100"
                        >
                        @if ($isSortable)
                            <button
                                type="button"
                                class="sort-handle absolute top-1 left-1 bg-gray-700/80 hover:bg-gray-900 text-white rounded-full p-1 transition-colors opacity-0 group-hover:opacity-100 cursor-grab active:cursor-grabbing"
                                title="سحب لإعادة الترتيب"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </button>
                        @endif
                        <button
                            type="button"
                            @click="removeGalleryImage(index)"
                            class="absolute top-1 right-1 bg-gray-700/80 hover:bg-red-600 text-white rounded-full p-1 transition-colors opacity-0 group-hover:opacity-100"
                            title="حذف الصورة"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        @else
            <img x-show="!image" src="{{ asset('assets/images/image.png') }}" alt="upload" class="{{ $mode === 'profile' ? 'rounded-full size-20' : 'w-full h-40 rounded-lg object-cover' }}">

            <div class="relative" x-show="image">
                <img
                    x-ref="image"
                    :src="image"
                    alt="{{ __($label ?? 'Image') }}"
                    class="{{ $mode === 'profile' ? 'rounded-full size-20' : 'w-full h-40 rounded-lg object-cover' }}"
                >

                <button
                    type="button"
                    @click="removeImage()"
                    class="absolute -top-2 right-1 bg-gray-600 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
                    title="حذف الصورة"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="{{ $targetClass }}"></div>

        <button
            id="uploadmediabtn{{ $collection }}-{{ $name }}"
            type="button"
            class="{{ $triggerClass }} mt-1 text-gray-700 cursor-pointer hover:bg-primary-100 bg-white borderx shadow-smx p-2 px-3 rounded-lg flex items-center gap-x-2 text-sm w-fit"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-plus w-5 h-5"
                width="36" height="36" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                <path d="M12 11l0 6"></path>
                <path d="M9 14l6 0"></path>
            </svg>
            <span class="text-sm">{{ $uploadButtonLabel }}</span>
        </button>
    </div>
</ui:field>
