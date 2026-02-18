import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip
);

const colorMap = {
    signups: { border: 'oklch(0.57 0.19 25)', bg: 'oklch(0.57 0.19 25 / 0.1)' },
    gifts:   { border: 'oklch(0.60 0.11 175)', bg: 'oklch(0.60 0.11 175 / 0.1)' },
    claims:  { border: 'oklch(0.68 0.16 80)', bg: 'oklch(0.68 0.16 80 / 0.1)' },
};

document.addEventListener('alpine:init', () => {
    const dataEl = document.getElementById('admin-chart-data');
    if (!dataEl) return;

    const chartData = JSON.parse(dataEl.textContent);

    Alpine.data('adminChart', (key) => ({
        initChart() {
            new Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData[key],
                        borderColor: colorMap[key].border,
                        backgroundColor: colorMap[key].bg,
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHitRadius: 10,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { tooltip: { mode: 'index', intersect: false } },
                    scales: {
                        x: { display: false },
                        y: { display: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    },
                },
            });
        },
    }));
});
