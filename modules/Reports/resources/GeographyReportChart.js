
/*
    GeographyReportChart.js
    Author: Phuc Lu
    Date: 2020.06.30
*/


window.GeographyReportChart = class extends CustomChartWidget {

    drawChart (placeholderId, width, height) {
        var chartData = this.chartData.data;
        var data = [];
        var object = '';

        // Get long and lat        
        $.each(chartData, function (k, v) {
            if (v['bill_city'] != '' && v['longitude'] != '') {
                data.push({name: v['bill_city'], record_name: v.record_name, lon: v['longitude'], lat: v['latitude'], data: v});
            }
            
            object = v.data_format.cluster_object;
        })
        
        var chartInfo = {            
            map: 'countries/vn/vn-all'
        };

        if (width > 0) {
            chartInfo['width'] = width;
        }

        if (height > 0) {
            chartInfo['height'] = height;
        }

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            if (!chartInfo.style) chartInfo.style = {};
            chartInfo.style.fontSize = '40px';
        }
        // END-- Support mobile compatibilities

        Highcharts.mapChart(placeholderId, {
            chart: chartInfo,
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            mapNavigation: {
                enabled: true
            },
            tooltip: {
                headerFormat: '',
                formatter: function () {

                    // Show cluster tooltip
                    if (this.point.clusteredData) {
                        return '<b>' + object + ': ' + this.point.clusterPointsAmount + '</b>';
                    }

                    if (object != 'Accounts') {
                        return '<b><span class="spn-title">' + this.point.name + ' - ' +  this.point.data.record_name + '<span></b>';
                    }

                    // Show normal tooltip
                    return '<b><span class="spn-title">' + this.point.name + ' - ' +  this.point.data.record_name + '<span></b><br>'
                    + app.vtranslate('Reports.JS_REPORT_CURRENT_YEAR_SALES', '')  + ': ' + this.point.data.data_format.cur_sales + ' <span class="spn-compare ' + this.point.data.data_format.compare_class + '">' + this.point.data.data_format.compare_percent + '</span><br>'
                    + app.vtranslate('Reports.JS_REPORT_ACCUMULATED_SALES', '')  + ': ' + this.point.data.data_format.sales + '<br>'
                    + app.vtranslate('Reports.JS_REPORT_LATEST_DATE_OF_SALES_ORDER', '')  + ': ' + this.point.data.latest_date_of_so;
                }
            },
            colorAxis: {
                min: 0,
                max: 20
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                mappoint: {
                    cluster: {
                        enabled: true,
                        allowOverlap: false,
                        layoutAlgorithm: {
                            type: 'grid',
                            gridSize: 70
                        },
                        zones: [{
                            from: 1,
                            to: 4,
                            marker: {
                                radius: 13,
                                fillColor: '#7CE3AC',
                            }
                        }, {
                            from: 5,
                            to: 9,
                            marker: {
                                radius: 15,
                                fillColor: '#7CE3AC',
                            }
                        }, {
                            from: 10,
                            to: 15,
                            marker: {
                                radius: 17,
                                fillColor: '#7CE3AC',
                            }
                        }, {
                            from: 16,
                            to: 20,
                            marker: {
                                radius: 19,
                                fillColor: '#7CE3AC',
                            }
                        }, {
                            from: 21,
                            to: 100,
                            marker: {
                                radius: 21,
                                fillColor: '#7CE3AC',
                            }
                        }, {
                            from: 101,
                            to: 999999,
                            marker: {
                                radius: 23,
                                fillColor: '#7CE3AC',
                            }
                        }]
                    }
                }
            },
            series: [{
                name: 'Basemap',
                borderColor: '#AFB8CA',
                nullColor: '#738FC7',
                showInLegend: false
            }, {
                type: 'mappoint',
                enableMouseTracking: true,
                colorKey: 'clusterPointsAmount',
                color: '#7CE3AC',
                data: data
            }]
        });
    }
};