<?php /** @var array $stats */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê phần mềm</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; text-align: center; }
        .chart-container { width: 400px; margin: 0 auto; }
    </style>
</head>
<body>
    <h2>📊 Thống kê tỷ lệ Hệ điều hành (Chart.js)</h2>
    <div class="chart-container">
        <canvas id="osChart"></canvas>
    </div>

    <script>
        const statsData = <?= json_encode($stats ?? []) ?>;
        
        const labels = statsData.map(item => item.os_type);
        const data = statsData.map(item => item.total);

        const ctx = document.getElementById('osChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số lượng link cài đặt',
                    data: data,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            }
        });
    </script>
</body>
</html>