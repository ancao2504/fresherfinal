/*
    SummarySalesByMarketReportChart.js
    Author: Phuc Lu
    Date: 2020.06.03
*/

window.SummarySalesByMarketReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var options = {
            width: width,
            height: height - 20,
            legend: { position: 'bottom' },
            colors: ['#008ecf', '#678be0', '#aa82df', '#e274cc', '#F654C9', '#ff6aa8', '#ff707a', '#ff8749', '#ffa600'],
        };

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            options.fontSize = 40;
        }
        // END-- Support mobile compatibilities

        // Generate container for each chart
        var container = document.getElementById(placeholderId);
        var data = google.visualization.arrayToDataTable(this.chartData.data);
        var chart = new google.visualization.PieChart(container);
        chart.draw(data, options);
    }
};
