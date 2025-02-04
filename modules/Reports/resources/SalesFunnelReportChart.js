/*
    SalesFunnelReportChart.js
    Author: Phuc Lu
    Date: 2020.06.04
*/

window.SalesFunnelReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var firstValue = this.chartData.data[0][1];
        var chartInfo = {
            type: 'funnel',
        };

        if (width > 0) {
            chartInfo['width'] = width;
        }

        if (height > 0) {
            chartInfo['height'] = height - 30;
        }

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            if (!chartInfo.style) chartInfo.style = {};
            chartInfo.style.fontSize = '40px';
        }
        // END-- Support mobile compatibilities

        Highcharts.chart(placeholderId, {
            chart: chartInfo,
            title: {
                text: ''
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return '<b>' + this.key + ':' + this.y + '(' + app.formatNumberToUserFromNumber(100 * this.y / firstValue) + '%)</b>';
                        },
                        softConnector: true
                    },
                    center: ['50%', '50%'],
                    neckWidth: '30%',
                    neckHeight: '40%',
                    width: '70%'
                }
            },
            legend: {
                enabled: false
            },
            series: [{
                name: app.vtranslate('Reports.JS_REPORT_NUMBER'),
                data: this.chartData.data
            }],
            tooltip: {
                pointFormatter: function() {
                    var point = this;
                    return '<span>' + point.series.name + ': <b>' + (app.formatNumberToUserFromNumber(100 * point.y / firstValue, 2)) + '% (' + point.y + '/' + firstValue + ')</b><br/>';
                }
            },
            responsive: {
                rules: [
                    {
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            plotOptions: {
                                series: {
                                    dataLabels: {
                                        inside: true
                                    },
                                    center: ['50%', '50%'],
                                    neckWidth: '50%',
                                    width: '100%'
                                }
                            }
                        }
                    },
                ]
            }
        });
    }
};

function redimChartBeforePrint(chart, width, height) {
    if (typeof(width) == 'undefined') width = 950;

    if (typeof(height) == 'undefined') height = 400;

    chart.hasUserSizeBk = chart.hasUserSize;
    chart.resetParams = [chart.chartWidth, chart.chartHeight, false];
    chart.setSize(width, height, false);
}

function redimChartAfterPrint(chart) {
    chart.setSize.apply(chart, chart.resetParams);
    chart.hasUserSize = chart.hasUserSizeBk;
}

window.onbeforeprint = function() {
    redimChartBeforePrint($('#custom-chart-widget-').highcharts(), 1000, 300);
};

window.onafterprint = function() {
    redimChartAfterPrint($('#custom-chart-widget-').highcharts());
};
