@props([
    'value' => null,
    'name' => 'text',
    'label' => null,
    'subtitle' => '',
    'placeholder' => '',
])

<div class="bg-black/5 p-1 rounded-lg">

    <div class=" sm:flex items-center">
        @if ($label)
            <label for="{{ $name }}"
                class=" inline-block text-sm text-gray-500 font-semibold p-2 flex-shrink-0 w-36"> <span
                    class=" ">{{ $label }}</span> </label>
        @endif
        <div x-data="{
            colors: ['gray', 'stone', 'slate', 'red', 'pink', 'fuchsia', 'violet', 'blue', 'indigo', 'purple', 'yellow',
                'amber', 'cyan', 'green', 'teal', 'sky', 'zinc', 'emerald', 'lime', 'rose', 'sky', 'emerald', 
            ],
            variants: [50, 100, 200, 300, 400, 500, 600, 700, 800, 900],
            currentColor: @entangle($name),
            currentBgColor: '',
            iconColor: '',
            isOpen: false,
        
            setIconWhite() {
                this.iconColor = 'bg-white'
            },
            setIconBlack() {
                this.iconColor = 'bg-black'
            },
            selectColor(color, variant) {
        
                if (variant) {
                    this.currentColor = color + '-' + variant
                    this.currentBgColor = color + '-' + variant
                } else {
                    this.currentColor = color
                    this.currentBgColor = color
                }
        
                if (variant < 500 || this.currentColor == 'bg-white') {
                    this.setIconBlack()
                } else {
                    this.setIconWhite()
                }
            }
        }" x-init=" this.currentColor = '{{ $value }}';
         this.currentBgColor = '{{ $value }}'.replace('text-', '');
         setIconWhite()" :id="$id('{{ $name }}')">
            <div>
                <div class="flex flex-row relative">
                    {{-- <input id="color-picker" @click="isOpen = true"  class="py-2 bg-white border-2 border-gray-300/50 p-2 px-3 shadow-sm focus:outline-none rounded-s-md" x-model="currentColor"  wire:model="{{$name}}"  id="{{$name}}" :value="currentColor"> --}}
                    {{-- <div @click="isOpen = !isOpen"   class="cursor-pointer rounded-lg my-auto xborder-2 -mx-0.5 border-gray-300/50 p-2.5 hover:opacity-75 flex " :class="`bg-${currentBgColor}`" > --}}
                    <div @click="isOpen = !isOpen"
                        class="cursor-pointer rounded-lg my-auto xborder-2 -mx-0.5 border-gray-300/50 p-2.5 hover:opacity-75 flex "
                        :class="'bg-' + currentColor">
                        {{-- <div @click="isOpen = !isOpen"   class="cursor-pointer rounded-lg my-auto xborder-2 -mx-0.5 border-gray-300/50 p-2.5 hover:opacity-75 flex " :class="'bg-red-400'" > --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto my-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d=" M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0
            002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7
            17h.01" />
                        </svg>
                    </div>
                    <div x-show="isOpen" @click.away="isOpen = false"
                        x-transition:enter="transition ease-out duration-100 transform" x-cloak
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75 transform"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="border border-gray-300 origin-top-right absolute right-0 top-full mt-2 rounded-md shadow-lg z-10">
                        <div class="rounded-md bg-white shadow-xs p-2">
                            <div class="flex">

                                <template x-for="color in colors">
                                    <div class="" wire:ignore>
                                        <template x-for="variant in variants">
                                            <div @click="selectColor(color,variant)"
                                                class="cursor-pointer w-6 h-6 rounded mx-1 my-1 hover:opacity-70 border"
                                                :title="`${color}-${variant}`" {{-- :class="`bg-${color}-${variant}`, 'border border-black': currentBgColor === --}}
                                                {{-- :class="['bg-' + color + '-' + variant, 'border border-black':
                                                    currentBgColor ===
                                                    color + '-' + variant
                                                ]" --}}
                                                :class="[
                                                    currentBgColor === color + '-' + variant ? 'border-2 border-black' :
                                                    '', 'bg-' + color + '-' + variant,
                                                ]">
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <div @click="selectColor('white')"
                                    class="cursor-pointer w-6 h-6 rounded mx-1 my-1 hover:opacity-70 border bg-white"
                                    :class="currentBgColor === 'white' ? 'border-2 border-black' : ''"
                                    title="white">
                                </div>
                                <div @click="selectColor('black')"
                                    class="cursor-pointer w-6 h-6 rounded mx-1 my-1 hover:opacity-70 border bg-black"
                                    :class="currentBgColor === 'black' ? 'border-2 border-red-400' : ''"
                                    title="white">
                                </div>
                                <div @click="selectColor('bg-tranparent')"
                                    class="cursor-pointer w-6 h-6 rounded mx-1 my-1 hover:opacity-70 border bg-gray-100"
                                    :class="currentBgColor === 'bg-tranparent' ? 'border-2 border-black' : ''"
                                    title="tranparent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
