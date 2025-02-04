/*
    AnalyzeSalesFluctuationReportChart.js
    Author: Phuc Lu
    Date: 2020.08.18
*/

window.AnalyzeSalesFluctuationReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var data = google.visualization.arrayToDataTable(this.chartData.data);
        var container = document.getElementById(placeholderId);
        var options = {
            width: width,
            height: height - 28,
            pointSize: 9,
            chartArea: { left: '9%', width: '84%', height: '70%' }, 
            legend: { position: 'top' },
            hAxis: {
                title: this.chartData.xlabel,
                gridlines: {
                    color: 'transparent'
                }
            },
            vAxis: {                
                title: this.chartData.ylabel,
                format: 'short',
                viewWindowMode: 'explicit',
                viewWindow: { min: 0 },
            },
            colors: ['#008ECF', '#FFA600'],
        };

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            options.fontSize = 40;
        }
        // END-- Support mobile compatibilities

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(container);
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
