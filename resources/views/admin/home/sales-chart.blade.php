<?php
use SaKanjo\EasyMetrics\Metrics;
use App\Models\Payment;
use App\Traits\HasChartWidget;

new class extends \Livewire\Component {

    use HasChartWidget;

    public $range;

    public $chartTitle = 'المبيعات ';
    public $options = [];
    public $label = 'المبيعات';

    function mount()
    {
        $query = Payment::query()->forTenant() ;
        $data = Metrics\Trend::make($query)
            ->ranges($this->range)
            ->sumByDays('amount') ;

        $this->runChart($data);
    }

    public function runChart($data)
    {
        $this->options($type = 'line', $data->getData(), $data->getLabels());
    }

}; ?>
