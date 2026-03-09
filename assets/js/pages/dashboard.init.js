async function postJSON(action, extra = {}) {
    const params = new URLSearchParams({ action, ...extra });
    const res = await fetch('ajax/php/report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    });
    return res.json();
}

async function loadCards() {
    try {
        const json = await postJSON('get_dashboard_cards');
        if (json.status === 'success') {
            const { monthly_sales, total_stock, monthly_profit, monthly_expenses } = json.data;
            setCountUp('metric-monthly-sales', monthly_sales, 'Rs. ');
            setCountUp('metric-total-stock', total_stock);
            setCountUp('metric-monthly-profit', monthly_profit, 'Rs. ');
            setCountUp('metric-monthly-expenses', monthly_expenses, 'Rs. ');
        }
    } catch (e) {
        console.error('Failed to load cards', e);
    }
}

async function renderSalesChart() {
    try {
        const json = await postJSON('get_monthly_sales');
        if (json.status !== 'success') return;
        const months = json.data.map(d => d.month);
        const values = json.data.map(d => d.value);

        const options = {
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            stroke: { width: 3, curve: 'smooth' },
            colors: ['#5b73e8'],
            dataLabels: { enabled: false },
            series: [{ name: 'Sales', data: values }],
            xaxis: { categories: months },
            yaxis: { labels: { formatter: val => `Rs. ${Math.round(val).toLocaleString()}` } },
            tooltip: { y: { formatter: val => `Rs. ${Math.round(val).toLocaleString()}` } },
            grid: { borderColor: '#f1f3f5' }
        };

        const el = document.querySelector('#sales-summary-chart');
        if (el) {
            const chart = new ApexCharts(el, options);
            chart.render();
        }
    } catch (e) {
        console.error('Failed to render sales chart', e);
    }
}

async function renderProfitChart() {
    try {
        const cards = await postJSON('get_dashboard_cards');
        if (cards.status !== 'success') return;
        const {
            monthly_profit = 0,
            monthly_expenses = 0,
            monthly_sales = 0,
            monthly_returns = 0,
            monthly_daily_income = 0,
            monthly_sales_gross = 0
        } = cards.data;

        // Visualize net view similar to profit report: profit, returns, expenses, income (gross)
        const values = [
            Math.max(monthly_profit, 0),
            Math.max(monthly_returns, 0),
            Math.max(monthly_expenses, 0)
        ];
        const labels = ['Profit (final)', 'Returns', 'Expenses'];
        const colors = ['#00c292', '#f46a6a', '#ffb822'];

        const options = {
            chart: { type: 'pie', height: 320 },
            labels,
            series: values,
            colors,
            legend: { show: false },
            dataLabels: { formatter: (val, opts) => `${opts.w.config.series[opts.seriesIndex].toLocaleString()}` }
        };

        const el = document.querySelector('#profit-summary-chart');
        if (el) {
            const chart = new ApexCharts(el, options);
            chart.render();

            const legend = document.getElementById('profit-legend');
            if (legend) {
                legend.innerHTML = labels.map((label, i) => `
                    <span class="d-flex align-items-center text-muted small">
                        <span class="legend-dot" style="background:${colors[i]}"></span>${label}: ${values[i].toLocaleString()}
                    </span>
                `).join('');
                legend.innerHTML += `
                    <span class="d-flex align-items-center text-muted small">
                        <span class="legend-dot" style="background:#5b73e8"></span>Income (gross): ${Math.max(monthly_sales_gross, 0).toLocaleString()}
                    </span>
                    <span class="d-flex align-items-center text-muted small">
                        <span class="legend-dot" style="background:#9c27b0"></span>Daily Income: ${Math.max(monthly_daily_income, 0).toLocaleString()}
                    </span>`;
            }
        }
    } catch (e) {
        console.error('Failed to render profit chart', e);
    }
}

function setCountUp(id, value, prefix = '') {
    const el = document.getElementById(id);
    if (!el) return;
    const duration = 1000;
    const start = 0;
    const startTime = performance.now();

    function tick(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const current = start + (value - start) * progress;
        el.textContent = `${prefix}${Math.round(current).toLocaleString()}`;
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

document.addEventListener('DOMContentLoaded', () => {
    loadCards();
    renderSalesChart();
    renderProfitChart();
});