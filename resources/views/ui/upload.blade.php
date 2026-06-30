@props([
    'value' => null,
    'name' => null,
  
    'file' => null,
    'accept' => 'image/jpg,image/jpeg,image/png,application/pdf',
    'multiple' => false,
    'block' => false,
    'label' => null,

    'mode' => 'image',
    'options' => [],
    'collection' => 'theme-media',
    'profileClass' => 'w-20 h-20 rounded-full'
])

<ui:field :label="$label" :block="$block">

 
<div 
    x-data="{
        image: '{{ empty($value) || is_null($value)  ? null : (str_contains($value, 'http') ? $value : \Storage::url($value)) }}',
        removeImage() {
            this.image = null;
            $wire.set('{{ $name }}', null);
        }
    }"
    wire:key="upload-{{ $collection }}-{{ $name }}" 
    wire:ignore 
    class="relative flex flex-col gap-0.5">
 

    <img x-show="!image" src="{{asset('assets/images/image.png')}}" alt="upload" class="{{ $mode === 'profile' ? 'rounded-full size-20' : 'w-full h-40 rounded-lg object-cover' }}">

    <div class="relative" x-show="image">
        <img x-ref="image" 
            :src="image" 
            alt="{{ __($label) }}" 
            class="{{ $mode === 'profile' ? 'rounded-full size-20' : 'w-full h-40 rounded-lg object-cover' }}">
        
        <button 
            type="button"
            @click="removeImage()"
            class="absolute -top-2 right-1 bg-gray-600 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
            title="حذف الصورة">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <div class="uploader{{ $collection }}"></div>
    <button id="uploadmediabtn{{ $collection }}-{{ $name }}" type="button"
        class="openUploader{{ $collection }} mt-2  text-gray-700 cursor-pointer  hover:bg-primary-100 bg-white borderx shadow-smx p-2 px-3 rounded-lg flex items-center gap-x-2 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-plus w-5 h-5"
            width="36" height="36" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
            <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
            <path d="M12 11l0 6"></path>
            <path d="M9 14l6 0"></path>
        </svg>
        <span class="text-sm"> رفع الصورة </span>
    </button>


  <div
            x-data='{
                init(){
                    let that = this;
                    let uppy = new Uppy({
                        id: "uploader-multi-{{ $collection }}-{{ $name }}",
                        autoProceed: false,
                        maxNumberOfFiles: 1, // Maximum 1 file
                        minNumberOfFiles: 1, // Minimum 1 file
                        allowedFileTypes: ["image/*", ".jpg", ".png", ".gif", ".webp", ".svg"], // Allowed types
                        maxFileSize: 6291456, // Max 6MB per file (in bytes)
                        maxTotalFileSize: 6291456, // Max 6MB total size (in bytes),
                        allowMultipleUploadBatches:false,
                        allowMultipleFileSelection:false,
                        allowMultipleFileSelection:false,
                    })
                    .use(Dashboard, {
                        trigger: ".openUploader{{ $collection }}",
                        id: "uploader-multi-{{ $collection }}-{{ $name }}",
                        inline: false,
                        locale: UppyAR,
                        closeAfterFinish: true,
                        singleFileFullScreen: true,
                        {{-- height: "50px", --}}
                        showLinkToFileUploadResult: false,
                        target: ".uploader{{ $collection }}",
                        proudlyDisplayPoweredByUppy: false,
                    })
                    .use(XHR, { endpoint: "{{ route('dashboard.upload-image') }}", headers: { "X-CSRF-Token": "{{ csrf_token() }}" } })
                    .use(ImageEditor, { target: Dashboard });
                    uppy.on("file-added", (file) => {
                        uppy.setFileMeta(file.id, {
                            size: file.size,
                        });
                    });            
                    uppy.on("dashboard:modal-closed", (file) => {
                        that.$dispatch("load-media");
                        console.log(file);
                    });
                    
                    uppy.on("upload-success", (file, response) => {
                        $wire.set("{{ $name }}", response.body.filePath);
                        this.image = response.body.url;
                    }); 
                }
            }'>
        </div>
 


</div>
</ui:field>