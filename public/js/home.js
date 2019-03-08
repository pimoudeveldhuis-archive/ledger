var locale = 'nl';
var options = {style: 'currency', currency: 'eur', minimumFractionDigits: 2, maximumFractionDigits: 2};
var formatter = new Intl.NumberFormat(locale, options);

// Budgets bar chart
var chartBudgetsData = JSON.parse($("#chartBudgets").attr('cache'));

chartBudgetsData.datasets[0].fill = true;
chartBudgetsData.datasets[0].backgroundColor = '#00a65a';

chartBudgetsData.datasets[1].fill = true;
chartBudgetsData.datasets[1].backgroundColor = '#f56954';

chartBudgetsData.datasets[2].fill = true;
chartBudgetsData.datasets[2].backgroundColor = '#3c8dbc';

new Chart($('#chartBudgets').get(0).getContext('2d'), {
    type: 'bar',
    data: chartBudgetsData,
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
                        formatter.format(chartBudgetsData['datasets'][0]['data'][tooltipItem.index] + chartBudgetsData['datasets'][1]['data'][tooltipItem.index]),
                    ];
                }
            }
        },

    }
});

// Categories bar chart
var chartCategoriesData = JSON.parse($("#chartCategories").attr('cache'));

chartCategoriesData.datasets[0].fill = true;
chartCategoriesData.datasets[0].backgroundColor = '#3c8dbc';

new Chart($('#chartCategories').get(0).getContext('2d'), {
    type: 'bar',
    data: chartCategoriesData,
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
                        formatter.format(chartCategoriesData['datasets'][0]['data'][tooltipItem.index]),
                    ];
                }
            }
        },

    }
});