 
<?php
use SaKanjo\EasyMetrics\Metrics;
use App\Models\RequestAnalytics;
use App\Traits\HasChartWidget;

new class extends \Livewire\Component {

    use HasChartWidget;

    public $range;

    public $chartTitle = 'الزيارات ';
    public $options = [];
    public $label = 'العدد';

    function mount()
    {
        $data = Metrics\Trend::make(RequestAnalytics::class)
            ->dateColumn('visited_at')
            ->ranges($this->range)
            ->countByDays();

        $this->runChart($data);
    }

    public function runChart($data)
    {
        $this->options($type = 'line', $data->getData(), $data->getLabels());
    }

}; ?>
