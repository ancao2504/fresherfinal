/*
    CompareCustomerConversionRateByCampaignReportChart.js
    Author: Phuc Lu
    Date: 2020.05.12
*/

window.CompareCustomerConversionRateByCampaignReportChart = class extends CustomChartWidget {

    drawChart(placeholderId, width, height) {
        var data = new google.visualization.DataTable(this.chartData.data);
        var container = document.getElementById(placeholderId);

        const _drawChart = (colIndex) => {
            // build hAxis labels
            var formatNumber = new google.visualization.NumberFormat({ suffix:'%' , pattern: '#'});
            // var colRange = data.getColumnRange(colIndex);
            var ticks = [];
            for (var i = -100; i <= 100; i = i + 10) {
                ticks.push({
                    v: i,
                    f: formatNumber.formatValue(Math.abs(i))
                });
            }
    
            // build hAxis view window (ensure 150.00 tick has room)
            // var edgeValue = (colRange.max < 0) ? -100 : 100;
            // var viewWindow = {};
    
            // hide inner vAxis labels, build hAxis view window
            // var vAxis = {
            //     title: this.chartData.ylabel,
            //     format: 'short',
            //     viewWindowMode: 'explicit',
            //     viewWindow: {
            //         min: 0
            //     },
            // };
    
            // chart options
            var options = {
                width: width,
                height: height - 28,
                pointSize: 9,
                legend: { position: 'bottom' },
                chartArea: { left: '9%', top: '7%', width: '86%', height: '70%' }, 
                isStacked: true,
                hAxis: {
                ticks: ticks,
                },
                vAxis: {                
                    title: this.chartData.ylabel,
                    format: 'short',
                    viewWindowMode: 'explicit',
                    viewWindow: { min: 0 },
                },
                series: {
                    0: { type: "bars", targetAxisIndex: 0, color: '#7cb5ec' },
                    1: { type: "bars", targetAxisIndex: 1, color: '#f7a35c' },
                },
            };
    
            // choose series to display
            var view = new google.visualization.DataView(data);
            view.setColumns([0, colIndex]);
    
            // create chart
            // container.className = 'chart';
            var chart = new google.visualization.BarChart(container);
    
            // add series label
            // google.visualization.events.addListener(chart, 'ready', function () {
            //     var chartTitle = null;
            //     Array.prototype.forEach.call(container.getElementsByTagName('text'), function (axisLabel) {
            //         if ((axisLabel.getAttribute('aria-hidden') !== 'true') &&
            //             (axisLabel.innerHTML === '100.00') &&
            //             (axisLabel.getAttribute('text-anchor') === 'end') &&
            //             (axisLabel.getAttribute('fill') === data.getColumnProperty(colIndex, 'seriesColor')) &&
            //             (chartTitle === null)) {
            //             chartTitle = axisLabel.cloneNode(true);
            //             chartTitle.setAttribute('y', parseFloat(chartTitle.getAttribute('y')) - 38);
            //             chartTitle.innerHTML = data.getColumnLabel(colIndex);
            //             axisLabel.parentNode.appendChild(chartTitle);
            //         }
            //     });
            // });

            // START-- Support mobile compatibilities
            if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
                options.fontSize = 40;
            }
            // END-- Support mobile compatibilities
            
            var runOnce = google.visualization.events.addListener(chart, 'ready', function () {
                google.visualization.events.removeListener(runOnce);
                chart.draw(data, options);
            });
    
            // draw chart
            chart.draw(view, options);
        }

        _drawChart(1);
        _drawChart(2);
    }
};