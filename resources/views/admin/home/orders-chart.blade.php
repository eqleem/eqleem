 
<?php
use SaKanjo\EasyMetrics\Metrics;
use App\Models\Order;
use App\Traits\HasChartWidget;

new class extends \Livewire\Component {

    use HasChartWidget;

    public $range;

    public $chartTitle = 'الطلبات ';
    public $options = [];
    public $label = 'العدد';

    function mount()
    {
        $data = Metrics\Trend::make(Order::class)
            ->ranges($this->range)
            ->countByDays();

        $this->runChart($data);
    }

    public function runChart($data)
    {
        $this->options($type = 'line', $data->getData(), $data->getLabels());
    }

}; ?>
