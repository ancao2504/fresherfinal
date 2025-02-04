/*
    CustomChartReportChartWidget.js
    Author: Hieu Nguyen
    Date: 2021-06-03
    Purpose: render chart for chart report using HighCharts library
*/

window.CustomChartReportChartWidget = class extends CustomChartWidget {

    drawChart (placeholderId, width, height) {
        if (jQuery('#' + placeholderId).find('.no-data')[0] != null) {
            return;
        }
        
        console.log('Chart Data:', this.chartData);
        
        var self = this;
        var chartType = this.chartData.chart_type.toUpperCase();

        var chartOptions = {
            chart: {
                type: '',
            },
            title: {
                text: this.chartData.chart_title,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                }
            },
            tooltip: {
                pointFormat: '<b>{point.y:,.0f}</b>'
            },
            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                                if (location.href.indexOf('entrypoint.php') < 0 && self.chartData.drilldown == true) {
                                    window.open(self.chartData.categories_link[this.index]);    // Drilldown for non-pie chart
                                }
                            }
                        }
                    }
                }
            },
            series: [],
            exporting: {
                filename: this.chartData.chart_title.unUnicode()
            }
        };

        if (height > 0) {
            chartOptions.chart.height = height;
        }
        
        if (chartType == 'PIE') {
            chartOptions = {
                ...chartOptions,
                chart: { ...chartOptions.chart, type: 'pie' },
                plotOptions: {
                    'pie': {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y:,.0f}'
                        },
                    },
                },
                series: [{
                    colorByPoint: true,
                    data: this.chartData.series_data,
                    point: {
                        events: {
                            click: function () {
                                if (location.href.indexOf('entrypoint.php') < 0 && self.chartData.drilldown == true) {
                                    window.open(this.link); // Drilldown for pie chart
                                }
                            }
                        }
                    },
                }]
            };

            Highcharts.chart(placeholderId, chartOptions);
        }

        if (chartType == 'SINGLE_COLUMN' || chartType == 'SINGLE_BAR' || chartType == 'SINGLE_LINE') {
            var type = chartType.split('_')[1].toLowerCase();
            console.log('Chart Type: ', type);

            chartOptions = {
                ...chartOptions,
                chart: { ...chartOptions.chart, type: type },
                xAxis: {
                    categories: this.chartData.categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: { text: '' },
                    labels: {
                        format: '{value:,.0f}'
                    }
                },
                series: [{
                    name: this.chartData.series_name,
                    data: this.chartData.series_data,
                    dataLabels: {
                        enabled: true,
                        format: '{y:,.0f}'
                    }
                }]
            };

            Highcharts.chart(placeholderId, chartOptions);
        }

        if (chartType == 'MULTI_COLUMNS' || chartType == 'MULTI_BARS'|| chartType == 'MULTI_LINES') {
            var type = chartType.split('_')[1].replace('S', '').toLowerCase();
            console.log('Chart Type: ', type);

            var self = this;
            var series = [];

            this.chartData.series_names.forEach(function (name, index) {
                series.push({
                    name: name,
                    data: self.chartData.series_datas[index],
                    dataLabels: {
                        enabled: true,
                        format: '{y:,.0f}'
                    }
                });
            });

            chartOptions = {
                ...chartOptions,
                chart: { ...chartOptions.chart, type: type },
                xAxis: {
                    categories: this.chartData.categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: { text: '' },
                    labels: {
                        format: '{value:,.0f}',
                    }
                },
                series: series
            };

            Highcharts.chart(placeholderId, chartOptions);
        }
    }
};