/*
    FailedPotentialsByReasonReportChart.js
    Author: Phuc Lu
    Date: 2020.05.18
*/

window.FailedPotentialsByReasonReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var options = {
            width: width / 3,
            height: height - 20,
            legend: { position: 'bottom' },
            colors: ['#7cb5ec', '#434348', '#90ed7d','#f7a35c', '#8085e9', '#f15c80'],
        };

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            options.fontSize = 40;
        }
        // END-- Support mobile compatibilities

        // Generate container for each chart
        var container = document.getElementById(placeholderId);
        container.innerHTML = '<div id="' + placeholderId + '-1" class="chart-area-2"></div><div id="' + placeholderId + '-2" class="chart-area-2"></div>';

        // First chart
        var data = google.visualization.arrayToDataTable(this.chartData.data.number_rates);
        container = document.getElementById(placeholderId + '-1');
        var chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];
        chart.draw(data, options);

        // Second chart
        data = google.visualization.arrayToDataTable(this.chartData.data.value_rates);
        container = document.getElementById(placeholderId + '-2');
        chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];        
        options.legend = { position: 'bottom' };
        chart.draw(data, options);
    }
};
