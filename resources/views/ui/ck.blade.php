@props([
    'modelId' => null,
    'modelType' => 'entry',
    'class' => null,
    'value' => null,
    'mediaCollection' => 'editor-images',
    'name' => 'content',
    'type' => 'text',
    'label' => null,
    'dir' => '',
    'info' => '',
    'placeholder' => '',
    'minHeight' => '35',
    'width' => 'w-full',
    'toolbar' => [
        'heading', '|', 'bold', 'italic', 'underline', '|',
        'fontColor', 'fontBackgroundColor', 'alignment',
        'link',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'blockQuote',
        '|',
        'mediaEmbed',
        'insertImage',
        '|',
        'codeBlock',
        '|',
        'horizontalLine',
    ],
])

<style>
    .ck.ck-reset_all,
    .ck-reset_all *:not(.ck-reset_all-excluded *) {
        --tw-text-opacity: 1;
        color: #4b5563;

    }

    .ck-rounded-corners .ck.ck-editor__main>.ck-editor__editable,
    .ck.ck-editor__main>.ck-editor__editable.ck-rounded-corners {
        color: #313842;
    }
</style>


<ui:field info="{{ $info }}" :width="$width" >

<div wire:ignore id="ck{{ $modelId }}" x-data="{
    editor: null,
    value: @entangle($name),
    initialContent: @js($value ?? ''),
    init() {
        if (typeof ClassicEditor === 'undefined') {
            console.error('CKEditor is not loaded. Run npm run build or npm run dev.');

            return;
        }

        this.$nextTick(() => {
            const content = this.value || this.initialContent || this.$refs.ckeditor?.value || '';

        ClassicEditor
            .create(this.$refs.ckeditor, {
                initialData: content,
                plugins: [Essentials, Bold, Italic, Font, Paragraph, BlockQuote, Heading, List, Link, MediaEmbed, Underline, Image, ImageUpload, ImageResize, ImageStyle, ImageToolbar, ImageCaption, SimpleUploadAdapter, HorizontalLine, AutoImage, ImageInsert, CodeBlock, Autoformat, Alignment],
                language: {
                    // The UI will be English.
                    ui: 'ar',

                    // But the content will be edited in Arabic.
                    content: 'ar'
                },
                translations: [
                    coreTranslations,
                ],
                toolbar: {
                    items: @js($toolbar)
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: '' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'h1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'h2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'h3' },

                    ]
                },
                image: {
                    resizeOptions: [{
                            name: 'resizeImage:original',
                            label: 'Default image width',
                            value: null,
                        },
                        {
                            name: 'resizeImage:50',
                            label: '50% page width',
                            value: '50',
                        },
                        {
                            name: 'resizeImage:75',
                            label: '75% page width',
                            value: '75',
                        },
                    ],
                    toolbar: [

                        'imageTextAlternative',
                        'toggleImageCaption',
                        '|',
                        'imageStyle:inline',
                        'imageStyle:wrapText',
                        'imageStyle:breakText',
                        '|',
                        'resizeImage',
                    ],
                },
                mediaEmbed: {
                    previewsInData: true,
                },
                simpleUpload: {
                    // The URL that the images are uploaded to.
                    uploadUrl: '{{ route('admin.upload-media') }}',

                    // Enable the XMLHttpRequest.withCredentials property.
                    withCredentials: true,

                    // Headers sent along with the XMLHttpRequest to the upload server.
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',

                        modelType: '{{ $modelType }}',
                        modelId: '{{ $modelId }}',
                        mediaCollection: '{{ $mediaCollection }}',
                        tenantId: '{{ currentTenantId() }}',
 
                    },

                },
            }).then(editor => {
                this.editor = editor;

                if (! this.value && content) {
                    this.value = content;
                }

                editor.model.document.on('change:data', () => {
                    this.value = editor.getData();
                });

                this.$watch('value', (newValue) => {
                    if (! this.editor) {
                        return;
                    }

                    const current = this.editor.getData();

                    if ((newValue ?? '') !== current) {
                        this.editor.setData(newValue ?? '');
                    }
                });
            });
        });
    },
}" class="w-full">

    {{-- @if ($label)
        <label for="{{ $name }}" class=" inline-block text-sm text-gray-500  p-2 flex-shrink-0 w-36x"> <span
                class=" ">{{ $label }}</span> </label>
    @endif --}}

    <textarea x-ref="ckeditor" dir="auto" placeholder="اكتب شيئاً .. " class="w-full">{!! $value ?? '' !!}</textarea>
</div> 

</ui:field>