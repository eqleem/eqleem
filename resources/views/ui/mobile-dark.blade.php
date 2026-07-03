<!-- Mobile Device Mockup -->
<div class="flex items-center justify-center p-8">
    <!-- Phone Frame Container -->
    <div class="relative">
        <!-- Phone Frame with Dark Blue Bezel -->
        <div class="relative w-[324px] h-[700px] bg-gradient-to-b from-slate-700 via-slate-800 to-slate-900 rounded-[3.5rem] p-2.5 shadow-2xl border border-slate-600/30">
            <!-- Inner Screen Container -->
            <div class="relative w-full h-full bg-slate-900 rounded-[3rem] overflow-hidden">
                <!-- Status Bar -->
                {{-- <div class="absolute top-0 left-0 right-0 h-12 z-20 flex items-center justify-between px-8 pt-2">
                    <!-- Time -->
                    <span class="text-white text-sm font-medium">9:41</span>
                    <!-- Right Icons -->
                    <div class="flex items-center gap-1.5">
                        <!-- Signal Bars (4 bars) -->
                        <div class="flex items-end gap-0.5">
                            <div class="w-1 h-1.5 bg-white rounded-sm"></div>
                            <div class="w-1 h-2 bg-white rounded-sm"></div>
                            <div class="w-1 h-2.5 bg-white rounded-sm"></div>
                            <div class="w-1 h-3 bg-white rounded-sm"></div>
                        </div>
                        <!-- Wi-Fi -->
                        <svg class="w-4 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.07 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/>
                        </svg>
                        <!-- Battery -->
                        <div class="flex items-center">
                            <div class="w-6 h-3 border border-white rounded-sm">
                                <div class="w-4 h-1.5 bg-white ml-0.5 mt-0.5 rounded-sm"></div>
                            </div>
                            <div class="w-1 h-2 bg-white rounded-r-sm ml-0.5"></div>
                        </div>
                    </div>
                </div> --}}

                <!-- Notch -->
                {{-- <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-900 rounded-b-3xl z-30"></div> --}}
                
                <!-- Screen Content -->
                <div class="absolute text-white bg-white top-0 left-0 right-0 bottom-0   overflow-hidden">
                    {{ $slot }}
                </div>

                <!-- Home Indicator -->
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-32 h-1 bg-white/30 rounded-full"></div>
            </div>
        </div>

        <!-- Optional: Side Buttons -->
        <div class="absolute left-0 top-24 w-1 h-16 bg-slate-500 rounded-r-full"></div>
        <div class="absolute left-0 top-48 w-1 h-20 bg-slate-500 rounded-r-full"></div>
        <div class="absolute left-0 top-80 w-1 h-20 bg-slate-500 rounded-r-full"></div>
    </div>
</div>