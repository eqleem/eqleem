@props([
    'value' => null,
    'name' => null,
    'multiple' => true,
    'maxFiles' => 20,
    'block' => false,
    'label' => null,
    'uploadLabel' => 'رفع ملفات التحميل',
    'collection' => 'digital-product-downloads',
    'modelId' => null,
    'modelType' => 'content',
    'addMethod' => 'addDownloadFile',
    'removeMethod' => 'removeDownloadFile',
    'reorderMethod' => 'reorderDownloadFiles',
    'sortable' => true,
    'emptyText' => 'لم يتم رفع ملفات تحميل بعد. سيحصل العميل على هذه الملفات بعد إتمام الشراء.',
])

@php
    $isMultiple = filter_var($multiple, FILTER_VALIDATE_BOOLEAN);
    $isSortable = filter_var($sortable, FILTER_VALIDATE_BOOLEAN) && $isMultiple;
    $uploaderId = 'file-uploader-'.$collection.'-'.$name;
    $triggerClass = 'openFileUploader'.$collection.'-'.$name;
    $targetClass = 'fileUploaderTarget'.$collection.'-'.$name;
    $maxFileSize = (int) config('media-library.max_file_size', 10485760);

    $fileItems = collect((array) $value)
        ->filter(fn (mixed $item): bool => filled(is_array($item) ? ($item['name'] ?? $item['url'] ?? null) : $item))
        ->map(function (mixed $item): array {
            if (is_array($item)) {
                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'name' => (string) ($item['name'] ?? basename((string) ($item['url'] ?? ''))),
                    'url' => (string) ($item['url'] ?? ''),
                    'size' => (int) ($item['size'] ?? 0),
                ];
            }

            return [
                'id' => null,
                'name' => basename((string) $item),
                'url' => (string) $item,
                'size' => 0,
            ];
        })
        ->values()
        ->all();
@endphp

<ui:field :label="$label" :block="$block">
    <div
        wire:key="upload-files-{{ $collection }}-{{ $name }}"
        wire:ignore
        class="relative flex flex-col gap-3"
        x-data="{
            isMultiple: @js($isMultiple),
            isSortable: @js($isSortable),
            files: @js($fileItems),
            addMethod: @js($addMethod),
            removeMethod: @js($removeMethod),
            reorderMethod: @js($reorderMethod),
            init() {
                const maxFiles = @js((int) $maxFiles);
                const maxFileSize = @js($maxFileSize);
                const triggerClass = @js('.'.$triggerClass);
                const targetClass = @js('.'.$targetClass);

                const uppy = new Uppy({
                    id: @js($uploaderId),
                    autoProceed: true,
                    restrictions: {
                        maxNumberOfFiles: maxFiles,
                        maxFileSize,
                    },
                })
                .use(Dashboard, {
                    trigger: triggerClass,
                    id: @js($uploaderId),
                    inline: false,
                    locale: UppyAR,
                    closeAfterFinish: true,
                    target: targetClass,
                    proudlyDisplayPoweredByUppy: false,
                })
                .use(XHR, {
                    endpoint: @js(route('admin.upload-media')),
                    headers: { 'X-CSRF-Token': @js(csrf_token()) },
                    fieldName: 'file',
                    allowedMetaFields: ['mediaCollection', 'modelType', 'modelId'],
                });

                uppy.on('file-added', (file) => {
                    uppy.setFileMeta(file.id, {
                        mediaCollection: @js($collection),
                        modelType: @js($modelType),
                        modelId: @js((string) $modelId),
                    });
                });

                uppy.on('upload-success', (file, response) => {
                    const body = response?.body ?? {};
                    const filePath = body.filePath ?? body.url;
                    const url = body.url ?? body.filePath;
                    const fileName = body.fileName ?? file?.name ?? 'file';

                    if (! filePath) {
                        return;
                    }

                    this.addFile(filePath, url, body.mediaId ?? null, fileName, file?.size ?? 0);
                });

                if (this.isSortable && typeof Sortable !== 'undefined') {
                    this.$watch('files.length', (length) => {
                        if (length > 0) {
                            this.$nextTick(() => this.initSortable());
                        }
                    });

                    this.$nextTick(() => this.initSortable());
                }
            },
            initSortable() {
                if (! this.$refs.fileList || typeof Sortable === 'undefined' || this.files.length === 0) {
                    return;
                }

                if (this.$refs.fileListSortable) {
                    return;
                }

                this.$refs.fileListSortable = Sortable.create(this.$refs.fileList, {
                    animation: 150,
                    handle: '.sort-handle',
                    draggable: '.sort-item',
                    onEnd: (event) => {
                        if (event.oldIndex === event.newIndex) {
                            return;
                        }

                        const moved = this.files.splice(event.oldIndex, 1)[0];
                        this.files.splice(event.newIndex, 0, moved);

                        const orderedIds = this.files
                            .map((item) => item.id)
                            .filter((id) => id != null);

                        if (orderedIds.length) {
                            $wire.call(this.reorderMethod, orderedIds);
                        }
                    },
                });
            },
            addFile(path, url, mediaId = null, name = 'file', size = 0) {
                if (mediaId && this.files.some((item) => item.id === mediaId)) {
                    return;
                }

                if (! mediaId && (! path || this.files.some((item) => item.url === url))) {
                    return;
                }

                this.files.push({ id: mediaId, name, url, size });

                if (! mediaId && path) {
                    $wire.call(this.addMethod, path);
                } else if (mediaId) {
                    $wire.call(this.addMethod, path || url);
                }
            },
            removeFile(index) {
                const item = this.files[index];

                if (item?.id) {
                    $wire.call(this.removeMethod, item.id);
                }

                this.files.splice(index, 1);
            },
            formatSize(bytes) {
                if (! bytes) {
                    return '';
                }

                if (bytes < 1024) {
                    return bytes + ' B';
                }

                if (bytes < 1048576) {
                    return (bytes / 1024).toFixed(1) + ' KB';
                }

                return (bytes / 1048576).toFixed(1) + ' MB';
            }
        }"
    >
        <div x-ref="fileList" x-show="files.length > 0" class="space-y-2">
            <template x-for="(item, index) in files" :key="item.id ?? item.url">
                <div class="sort-item flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 group">
                    @if ($isSortable)
                        <button
                            type="button"
                            class="sort-handle cursor-grab text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100"
                            aria-label="سحب لإعادة الترتيب"
                        >
                            <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                        </button>
                    @endif

                    <ui:icon name="file-download" class="!w-5 !h-5 text-primary-500 shrink-0" />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-800" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="formatSize(item.size)" x-show="item.size"></p>
                    </div>

                    <a
                        :href="item.url"
                        target="_blank"
                        class="text-xs text-primary-600 hover:underline shrink-0"
                    >
                        معاينة
                    </a>

                    <button
                        type="button"
                        @click="removeFile(index)"
                        class="rounded-lg p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                        title="حذف الملف"
                    >
                        <ui:icon name="trash" class="!w-4 !h-4" />
                    </button>
                </div>
            </template>
        </div>

        <p x-show="files.length === 0" class="text-sm text-gray-500">
            {{ $emptyText }}
        </p>

        <div class="{{ $targetClass }}"></div>

        <button
            type="button"
            class="{{ $triggerClass }} text-gray-700 cursor-pointer hover:bg-primary-100 bg-white border shadow-sm p-2 px-3 rounded-lg flex items-center gap-x-2 text-sm w-fit"
        >
            <ui:icon name="upload" class="!w-5 !h-5" />
            <span>{{ $uploadLabel }}</span>
        </button>
    </div>
</ui:field>
