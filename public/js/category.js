// Bar Chart
var barChartData = JSON.parse($("#barChart").attr('data'));

barChartData.datasets[0].fill   = true
barChartData.datasets[0].borderColor   = 'rgba(210, 214, 222, 1)'
barChartData.datasets[0].backgroundColor   = 'rgba(210, 214, 222, 0.8)'

barChartData.datasets[1].fill   = true
barChartData.datasets[1].borderColor   = 'rgba(60, 141 ,188, 0.8)'
barChartData.datasets[1].backgroundColor   = 'rgba(60, 141 ,188, 0.8)'

new Chart($('#barChart').get(0).getContext('2d'), {
    type: 'line',
    data: barChartData,
    options: {
        responsive: true,
        tooltips: {
            callbacks: {
                label: function(tooltipItem) {
                    return [
                        "€ " + Number(barChartData['datasets'][1]['data'][tooltipItem.index]),
                        "",
                        "Één jaar eerder",
                        "€ " + Number(barChartData['datasets'][0]['data'][tooltipItem.index]),
                    ];
                }
            }
        },

    }
});