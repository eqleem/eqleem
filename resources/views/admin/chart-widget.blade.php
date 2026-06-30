<div>
    <div x-data="{ 
        init() 
            { 
                new Chart(this.$refs.canvas, @js($options))                 
            }
        }" 
        class=" bg-white rounded-2xl">
        <div class="text-sm font-semibold text-gray-500 p-3 border-b-2 border-gray-200/50">
            {{$chartTitle}}
        </div>
        <div class="p-3">
            <canvas x-ref="canvas" id="canvas"></canvas>
        </div>
    </div>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    @endassets
</div>