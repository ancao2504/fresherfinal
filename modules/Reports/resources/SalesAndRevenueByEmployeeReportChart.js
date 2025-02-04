/*
    SalesAndRevenueByEmployeeReportChart.js
    Author: Phuc Lu
    Date: 2020.04.28
*/

window.SalesAndRevenueByEmployeeReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var data = google.visualization.arrayToDataTable(this.chartData.data);
        var container = document.getElementById(placeholderId);
        var options = {
            width: width,
            height: height - 28,
            legend: { position: 'bottom' },
            chartArea: { left: '7%', width: '90%', height: '70%' }, 
            vAxis: {
                format: 'short',
                viewWindowMode: 'explicit',
                viewWindow: { min: 0 }
            },
            colors: ['#7cb5ec', '#434348']
        };

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            options.fontSize = 40;
        }
        // END-- Support mobile compatibilities

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(container);
        var _this = this;
        
        var runOnce = google.visualization.events.addListener(chart, 'ready', function () {
            google.visualization.events.removeListener(runOnce);
            var leftVal, newLeftTicks = [];
            var leftTicks = chart.ia.hd[0].La;
            var maxLeftTick = 0;
            var leftInterval = 0;

            for (var i = 0; i < leftTicks.length; i++) {
                if (leftTicks[i].Da > maxLeftTick) {
                    maxLeftTick = leftTicks[i].Da;
                }
            }

            if (maxLeftTick == 1) {
                leftInterval = 1;
            }
            else {
                leftInterval = _this.roundAxiForChart(maxLeftTick / 5);
            }

            for (var i = 0; i < 6; i++) {
                leftVal = _this.getFormatedValueAndSuffix(leftInterval * i);
                newLeftTicks.push({ v: leftInterval * i, f: leftVal[0] + ' ' + leftVal[1] });
            }

            options.vAxes = options.vAxes || {};            
            options.vAxis.ticks = newLeftTicks;
            options.vAxis.viewWindow.max = leftInterval * 5;
            chart.draw(data, options);
        });

        chart.draw(data, options);
    }
};
