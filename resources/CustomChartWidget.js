/*
    Custom Chart Widget
    Author: Hieu Nguyen
    Date: 2019-12-26
    Purpose: a parent class to render custom chart widget on the dashboard
*/

window.CustomChartWidget = class {

    constructor (placeholderId, chartData) {
        this.placeholderId = placeholderId;
        this.chartData = chartData;
        this.widgetContainer = null;
        this.chartOptions = this.getChartOptions(chartData);

        this.init();
    }

    init () {
        this.widgetContainer = $('#' + this.placeholderId).closest('.dashboardWidget');

        // Draw chart at widget load
        var chartSize = this.getChartSize(this.widgetContainer);
        this.drawChart(this.placeholderId, chartSize.width, chartSize.height);

        // Handle other events
        if (_META.module == 'Home') {
            this.handleWidgetResizeEvent();
            this.handleWidgetFullScreenEvent();
        }
    }

    getChartOptions (chartData) {
        return {};
    }

    getChartSize () { 
        var containerWidth = this.widgetContainer.width();
		var containerHeight = this.widgetContainer.height();

        var chartSize = { width: containerWidth - 20, height: containerHeight - 50 };
        return chartSize;
    }

    drawChart (placeholderId, width, height) {
        var thisInstance = this;
        google.charts.load('current', { 'packages': ['corechart'] });
        google.charts.setOnLoadCallback(drawColumnChart);

        function drawColumnChart() {
            var data = google.visualization.arrayToDataTable(thisInstance.chartData);
            var options = { 
                width: width, 
                height: height,
                animation: {
                    startup: true,
                    duration: 1000,
                    easing: 'in'
                },
                vAxis: {
                    viewWindowMode: 'explicit', 
                    viewWindow: { min: 0 }
                }
            };
            options = Object.assign(options, thisInstance.chartOptions);

            var chart = new google.visualization.ComboChart(document.getElementById(placeholderId));
            chart.draw(data, options);
        }
    }

    handleWidgetResizeEvent () {
        var thisInstance = this;

        this.widgetContainer.on(Vtiger_Widget_Js.widgetPostResizeEvent, function(e) {
			var chartSize = thisInstance.getChartSize(thisInstance.widgetContainer);
            thisInstance.drawChart(thisInstance.placeholderId, chartSize.width, chartSize.height);
		});
    }

    handleWidgetFullScreenEvent () {
        var thisInstance = this;
        var widgetContainer = $('#' + this.placeholderId).closest('.dashboardWidget');
        var placeholderId = this.placeholderId + '-fullscreen';

        widgetContainer.find('a[name="widgetFullScreen"]').on('click', function () {
            setTimeout(() => {
                // Setup modal
                var modalBody = $('.fullscreencontents:visible').find('.modal-body');
                modalBody.find('ul').remove();
                modalBody.append('<div id="'+ placeholderId +'"></div>');

                // Display no data message
                if (widgetContainer.find('.no-data')[0] != null) {
                    var noDataMsgHtml = widgetContainer.find('#' + thisInstance.placeholderId).html();
                    modalBody.find('#' + placeholderId).html(noDataMsgHtml);
                }
                // Draw chart on the modal
                else {
                    // Modified by Phu Vo on 2021.08.27 to render chart on modal body height basis
                    console.log(modalBody.height());
                    thisInstance.drawChart(placeholderId, this.chartData, modalBody.height() - 1, 350);
                    // End Phu Vo
                }
            }, 500);
        });
    }

    roundAxiForChart(value) {
        var round = 1;        
        var tempValue = parseInt(value);

        if (tempValue == value) {
            return tempValue;
        }

        while (value < 1 && value > 0) {
            value *= 10;
            round *= 10;
        }

        tempValue = parseInt(value);
        var valueLength = tempValue.toString().length;

        if (valueLength == 1) {
            if (tempValue >= 5 && tempValue < 10) {
                return 10 / round;
            }
            else if (tempValue >= 3 && tempValue < 5) {
                return 5 / round;
            }
            else {
                return (tempValue + 1) / round;
            }
        }

        tempValue = Math.round(value / Math.pow(10, valueLength - 1) + 0.5);

        return tempValue * Math.pow(10, valueLength - 1) / round;
    }

    getFormatedValueAndSuffix(val) {
        var suffix = '';
        var formatedVal = 0;

        if (val >= 1000000000) {
            formatedVal = app.formatNumberToUserFromNumber(val / 1000000000);
            suffix = app.vtranslate('Reports.JS_REPORT_BILION');
        }
        else if (val >= 1000000) {
            formatedVal = app.formatNumberToUserFromNumber(val / 1000000);
            suffix = app.vtranslate('Reports.JS_REPORT_MILION');
        }
        else if (val >= 1000) {
            formatedVal = app.formatNumberToUserFromNumber(val / 1000);
            suffix = app.vtranslate('Reports.JS_REPORT_THOUSAND');
        }
        else {
            formatedVal = val;
            suffix = '';
        }

        return [formatedVal, suffix];
    }
}