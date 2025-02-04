/*
    SalesByIndustryReportChart.js
    Author: Phuc Lu
    Date: 2020.06.08
*/

window.SalesByIndustryReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        jQuery('#' + placeholderId).css('height', '90%');

        var chartInfo = {
            type: 'funnel',
        };

        if (width > 0) {
            chartInfo['width'] = width;
        }

        if (height > 0) {
            chartInfo['height'] = height;

            if (placeholderId != 'custom-chart-widget-') {
                chartInfo['height'] = height + height * 5 / 100;
            }
        }

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            if (!chartInfo.style) chartInfo.style = {};
            chartInfo.style.fontSize = '40px';
        }
        // END-- Support mobile compatibilities
        
        Highcharts.chart(placeholderId, {
            chart: {
                type: 'column'
            },
            title: '',
            xAxis: {
                categories: this.chartData.data.categories,
                min: 0,
                max: this.chartData.data.categories.length > 10 ? 9 : this.chartData.data.categories.length - 1,
                scrollbar: {
                    enabled: this.chartData.data.categories.length > 10 ? true : false,
                    barBackgroundColor: 'rgba(170, 170, 170, 0.596078431372549)',
                    barBorderRadius: 7,
                    barBorderWidth: 0,
                    rifleColor: 'none',
                    buttonArrowColor: 'rgba(170, 170, 170, 0.596078431372549)',
                    buttonBorderColor: 'none',
                    buttonBackgroundColor: 'none',
                    buttonBorderWidth: 0,
                    buttonBorderRadius: 7,
                    trackBackgroundColor: 'none',
                    trackBorderWidth: 1,
                    trackBorderRadius: 8,
                    trackBorderColor: 'rgba(170, 170, 170, 0.596078431372549)',                    
                    height: 10,
                },
            },
            yAxis: [{
                min: 0,
                title: '',
            }],
            legend: {
                shadow: false
            },
            tooltip: {
                shared: true
            },
            plotOptions: {
                column: {
                    grouping: false,
                    shadow: false,
                    borderWidth: 0
                }
            },
            series: this.chartData.data.series
        });
    }
};
