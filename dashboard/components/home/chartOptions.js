// Mirrors App\Traits\HasChartWidget::options() — a single-series line chart.
export function lineChartOptions(label, data, labels) {
    return {
        type: 'line',
        rtl: true,
        locale: 'ar',
        data: {
            labels,
            datasets: [
                {
                    label,
                    data,
                    borderWidth: 2,
                    fill: 'start',
                    backgroundColor: '#9FD1F5',
                    borderColor: '#36A2EB',
                    tension: 0.2,
                },
            ],
        },
    };
}

// Dummy daily trend: `days` points ending today, each a random value in [min, max].
export function dummyTrend(days, min, max) {
    const labels = [];
    const data = [];
    const today = new Date();
    const fmt = new Intl.DateTimeFormat('ar', { day: 'numeric', month: 'short' });

    for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        labels.push(fmt.format(date));
        data.push(Math.floor(min + Math.random() * (max - min)));
    }

    return { labels, data };
}
