$('#input_year').change(function() {
    var year = $(this).find(":selected").val();
    $.ajax({
        method: 'GET',
        url: '/ajax/budget_by_year',
        data: { budget: budget_id, year: year },
        dataType: 'json',
        success: function(callback) {
            for(var month in callback) {
                $('input[name="month[' + month + ']"]').val(callback[month]);
            }
        }
    })
});

// Initial load of the budgets per month
$('#input_year').change();

var locale = 'nl';
var options = {style: 'currency', currency: 'eur', minimumFractionDigits: 2, maximumFractionDigits: 2};
var formatter = new Intl.NumberFormat(locale, options);

// Overview bar chart
var chartOverviewData = JSON.parse($("#chartOverview").attr('cache'));

chartOverviewData.datasets[0].fill = true;
chartOverviewData.datasets[0].backgroundColor = '#00a65a';

chartOverviewData.datasets[1].fill = true;
chartOverviewData.datasets[1].backgroundColor = '#f56954';

chartOverviewData.datasets[2].fill = true;
chartOverviewData.datasets[2].backgroundColor = '#3c8dbc';

new Chart($('#chartOverview').get(0).getContext('2d'), {
    type: 'bar',
    data: chartOverviewData,
    options: {
        responsive: true,

        scales: {
            xAxes: [{
                stacked: true,
            }],
            yAxes: [{
                stacked: true
            }]
        },

        legend: {
            display: false
        },

        tooltips: {
            callbacks: {
                label: function(tooltipItem) {
                    return [
                        formatter.format(chartOverviewData['datasets'][0]['data'][tooltipItem.index] + chartOverviewData['datasets'][1]['data'][tooltipItem.index]),
                    ];
                }
            }
        },

    }
});

// Longterm line chart
var longtermLinechartData = JSON.parse($("#chartLongterm").attr('cache'));
longtermLinechartData.datasets[0].backgroundColor = 'rgba(60, 141 ,188, 0.8)',
longtermLinechartData.datasets[0].borderColor = 'rgba(60, 141, 188, 0.9)',

new Chart($('#chartLongterm').get(0).getContext('2d'), {
    type: 'line',
    data: longtermLinechartData,
    options: {
        responsive: true,

        legend: {
            onClick: (e) => e.stopPropagation()
        },
        
        tooltips: {
            callbacks: {
                label: function(tooltipItem) {
                    return [
                        formatter.format(longtermLinechartData['datasets'][0]['data'][tooltipItem.index]),
                    ];
                }
            }
        },
    },
});

// Comparing previous year line chart
var chartComparingPreviousYearData = JSON.parse($("#chartComparingPreviousYear").attr('cache'));

chartComparingPreviousYearData.datasets[0].backgroundColor = 'rgba(60, 141 ,188, 0.8)',
chartComparingPreviousYearData.datasets[0].borderColor = 'rgba(60, 141, 188, 0.9)',

chartComparingPreviousYearData.datasets[1].backgroundColor = 'rgba(210, 214, 222, 0.8)',
chartComparingPreviousYearData.datasets[1].borderColor = 'rgba(210, 214, 222, 0.9)',

new Chart($('#chartComparingPreviousYear').get(0).getContext('2d'), {
    type: 'line',
    data: chartComparingPreviousYearData,
    options: {
        responsive: true,

        legend: {
            onClick: (e) => e.stopPropagation()
        },
        
        tooltips: {
            callbacks: {
                label: function(tooltipItem) {
                    return [
                        formatter.format(chartComparingPreviousYearData['datasets'][0]['data'][tooltipItem.index]),
                        "",
                        "Één jaar eerder",
                        formatter.format(chartComparingPreviousYearData['datasets'][1]['data'][tooltipItem.index]),
                    ];
                }
            },
        },
    },
});