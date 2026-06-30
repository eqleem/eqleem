<div class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
    <div x-data="{
        notices: [],
        visible: [],
        add(notice) {
            notice.id = Math.random().toString(36).substr(2, 9)
            this.notices.push(notice)
            this.fire(notice.id)
        },
        fire(id) {
            this.visible.push(this.notices.find(notice => notice.id == id))
            const timeShown = 2000 * this.visible.length
            setTimeout(() => {
                this.remove(id)
            }, timeShown)
        },
        remove(id) {
            const notice = this.visible.find(notice => notice.id == id)
            const index = this.visible.indexOf(notice)
            this.visible.splice(index, 1)
        },
    }" class="flex w-full flex-col items-center space-y-4 sm:items-end"
        @notify.window="add($event.detail) " style="pointer-events:none">
        <template x-for="notice of notices" :key="notice.id">
            <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg  shadow-lg ring-0 ring-black ring-opacity-5"
                {{-- @if (app()->getLocale() == 'ar') --}} x-show="visible.includes(notice)"
                x-transition:enter="transition ease-in duration-200"
                x-transition:enter-start="transform opacity-0 translate-y-2"
                x-transition:enter-end="transform opacity-100" x-transition:leave="transition ease-out duration-1000"
                x-transition:leave-start="transform -translate-x-0 opacity-100"
                x-transition:leave-end="transform -translate-x-full opacity-0" 
                :class="{
                    'bg-white text-green-700': notice.type === undefined,
                    'bg-green-50 text-green-700': notice.type === 'success',
                    'bg-primary-50 text-primary-700': notice.type === 'info',
                    'bg-orange-50 text-orange-700': notice.type === 'warning',
                    'bg-red-50 text-red-700': notice.type === 'error',
                }"
                style="pointer-events:all">
                <div class="p-4  ">
                    <div class="flex items-center">
                        <div x-show="notice.type == 'sucess'" class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ms-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm" x-text="notice.text"></p>
                            <p class="mt-1 text-sm opacity-50" x-text="notice.subtext"></p>
                        </div>
                        <div class="ms-4 flex flex-shrink-0">
                            <button @click="remove(notice.id)" type="button"
                                class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
