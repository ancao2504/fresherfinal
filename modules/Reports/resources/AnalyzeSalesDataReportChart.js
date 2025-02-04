/*
    AnalyzeSalesDataReportChart.js
    Author: Phuc Lu
    Date: 2020.06.03
*/

window.AnalyzeSalesDataReportChart = class extends CustomChartWidget {
    drawChart (placeholderId, width, height) {
        var options = {
            width: width / 2,
            height: height / 2 - 20,
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
        container.innerHTML = '<div style="display: block; height: 50%;"><div id="' + placeholderId + '-1" class="chart-area-2"></div><div id="' + placeholderId + '-2" class="chart-area-2"></div></div>';
        container.innerHTML += '<div style="display: block; height: 50%;"><div id="' + placeholderId + '-3" class="chart-area-2"></div><div id="' + placeholderId + '-4" class="chart-area-2"></div></div>';

        // First chart
        var data = google.visualization.arrayToDataTable(this.chartData.data.lead_number);
        container = document.getElementById(placeholderId + '-1');
        var chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];
        chart.draw(data, options);

        // Second chart
        data = google.visualization.arrayToDataTable(this.chartData.data.potential_number);
        container = document.getElementById(placeholderId + '-2');
        chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];        
        chart.draw(data, options);

        // Third chart
        data = google.visualization.arrayToDataTable(this.chartData.data.quote_number);
        container = document.getElementById(placeholderId + '-3');
        chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];        
        chart.draw(data, options);
        
        // Fourth chart
        data = google.visualization.arrayToDataTable(this.chartData.data.sales_order_number);
        container = document.getElementById(placeholderId + '-4');
        chart = new google.visualization.PieChart(container);
        options.title = data.vg[1]["label"];       
        chart.draw(data, options);
    }
};
