<div class="row g-4 mb-4">
    <!-- User Distribution Chart -->
    <div class="col-lg-5">
        <div class="section-card h-100 mb-0 shadow-sm border-0">
            <div class="section-title d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-chart-pie text-primary"></i>
                <h6 class="mb-0 fw-bold">User Role Distribution</h6>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="userDistChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Audit Activity Trend Chart -->
    <div class="col-lg-7">
        <div class="section-card h-100 mb-0 shadow-sm border-0">
            <div class="section-title d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-chart-line text-success"></i>
                <h6 class="mb-0 fw-bold">7-Day System Activity Trend</h6>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="activityTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. User Distribution Donut Chart
    const roleLabels = <?php echo json_encode($role_labels); ?>;
    const roleCounts = <?php echo json_encode($role_counts); ?>;
    
    const ctxUser = document.getElementById('userDistChart').getContext('2d');
    new Chart(ctxUser, {
        type: 'doughnut',
        data: {
            labels: roleLabels,
            datasets: [{
                data: roleCounts,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                ],
                hoverBackgroundColor: [
                    '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                }
            },
            cutout: '70%',
        }
    });

    // 2. Audit Activity Line Chart
    const trendLabels = <?php echo json_encode($trend_labels); ?>;
    const trendCounts = <?php echo json_encode($trend_counts); ?>;
    
    const ctxTrend = document.getElementById('activityTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: "Activities",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: trendCounts,
            }],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { maxTicksLimit: 7 }
                },
                y: {
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        beginAtZero: true
                    },
                    grid: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                }
            }
        }
    });
});
</script>
