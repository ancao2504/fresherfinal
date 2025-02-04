/*
    TopCustomerBySalesReportChart.js
    Author: Phuc Lu
    Date: 2020.04.14
*/

window.SalesOrderByCustomerTypeReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var options = {
            width: width / 3,
            height: height - 20,
            legend: { position: 'none' },
            colors: ['#434348', '#7cb5ec']
        };

        // START-- Support mobile compatibilities
        if (location.href.indexOf('EmbeddedReportChart') > 0 && screen.availWidth <= 600) {
            options.fontSize = 40;
        }
        // END-- Support mobile compatibilities

        // Generate container for each chart
        var container = document.getElementById(placeholderId);
        container.innerHTML = '<div id="' + placeholderId + '-1" class="chart-area-3"></div><div id="' + placeholderId + '-2" class="chart-area-3"></div><div id="' + placeholderId + '-3" class="chart-area-3"></div>';

        // First chart
        var data = google.visualization.arrayToDataTable(this.chartData.data.saleorder_number);
        container = document.getElementById(placeholderId + '-1');
        var chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];
        chart.draw(data, options);

        // Second chart
        data = google.visualization.arrayToDataTable(this.chartData.data.sales);
        container = document.getElementById(placeholderId + '-2');
        chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];        
        options.legend = { position: 'bottom' };
        chart.draw(data, options);

        // Third chart
        data = google.visualization.arrayToDataTable(this.chartData.data.revenue);
        container = document.getElementById(placeholderId + '-3');
        chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];        
        options.legend = { position: 'none' };
        chart.draw(data, options);
    }
};
