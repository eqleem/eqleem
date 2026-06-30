<div>
    <div x-data="{
        chart: null,
        init() {
            this.$nextTick(() => this.renderChart());
        },
        renderChart() {
            if (typeof Chart === 'undefined') {
                setTimeout(() => this.renderChart(), 50);

                return;
            }

            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new Chart(this.$refs.canvas, @js($options));
        },
    }"
        class=" bg-white rounded-2xl">
        <div class="text-sm font-semibold text-gray-500 p-3 border-b-2 border-gray-200/50">
            {{ $chartTitle }}
        </div>
        <div class="p-3" wire:ignore>
            <canvas x-ref="canvas" id="canvas"></canvas>
        </div>
    </div>
</div>
