
/*
    CampaignEffectReportDetail.js
    Author: Phuc Lu
    Date: 2020.04.28
*/

jQuery(function ($) {
    var container = $('#custom-report-detail');    
    let initFields = 3;
    initReportFilters(container);
    initReportButtons(container);

    container.find('#form-filter').vtValidate();    
    $('#period').trigger('change');

    container.find('#period').closest('.time-group').find('input, select').on('change', function (e) {
        app.helper.showProgress();
        
        var params = {
            module: 'Reports',
            action: 'DetailAjax',
            mode: 'getCampaignsByTime',
            from_date: $('input#from-date').val(),
            to_date: $('input#to-date').val(),
            top: $('select#top').val(),
            period: $('select#period').val(),
            month: $('select#month').val(),
            quarter: $('select#quarter').val(),
            year: $('select#year').val(),         
        };

        app.request.post({ data: params }).then(function (error, data) {
            app.helper.hideProgress();

            // Work around for now
            if (initFields > 1) {
                if (initFields > 0) initFields--;
                return;
            }

            if (error || !data) {
                var message = app.vtranslate('Vtiger.JS_THERE_WAS_SOMETHING_ERROR');
                app.helper.showErrorNotification({ message: message });
                return;
            }

            jQuery('#campaigns').select2('destroy');
            jQuery('#campaigns').find('option').remove();

            jQuery.each(data, function (k, v) {
                jQuery('#campaigns').append('<option value="' + k  + '">' + v + '</option>');
            })

            jQuery('#campaigns').val('0');
            jQuery('#campaigns').select2();
        });
    });
});

function initReportFilters (container) {
    container.find('select.filter').select2();

    CustomReportHelper.initPeriodFilter(container);

    container.find('.draw-roi').on('click', function() {
        var _this = $(this);

        if (typeof google.visualization == 'undefined') {
            google.charts.load('current', { 'packages': ['corechart'], 'language' : 'vi' });
            google.charts.setOnLoadCallback(function () {
                _this.trigger('click');
            });

            return;
        }

        var declareModal =  $('#report_modal').clone(true, true);
        var title = _this.closest('tr').find('.name a').html();
        declareModal.attr('id', 'report_modal_clone');
        declareModal.attr('title', declareModal.attr('title') + ' - ' + title);
        declareModal.find('div#report_chart').attr('id', 'report_chart_clone');
        

        var callBackFunction = function (data) {
            data.find('#report_modal_clone').removeClass('hide');
            data.find('.modal-header h4').html(data.find('#report_modal_clone').attr('title'));

            // Generate date
            var data = [['Element', app.vtranslate('Reports.JS_REPORT_VALUE'), {'role':"style"}]];
            var budget = app.unformatCurrencyToUser(_this.closest('tr').find('.budget').html());
            var cost = app.unformatCurrencyToUser(_this.closest('tr').find('.cost').html());
            var eRevenue = app.unformatCurrencyToUser(_this.closest('tr').find('.expected_revenue').html());
            var aRevenue = app.unformatCurrencyToUser(_this.closest('tr').find('.actual_revenue').html());

            data[1] = [app.vtranslate('Reports.JS_REPORT_BUDGET'), budget, '#5EC7F8'];
            data[2] = [app.vtranslate('Reports.JS_REPORT_ACTUAL_COST'), cost, '#FECD2D'];
            data[3] = [app.vtranslate('Reports.JS_REPORT_EXPECTED_REVENUE'), eRevenue, '#50D968'];
            data[4] = [app.vtranslate('Reports.JS_REPORT_ACTUAL_REVENUE'), aRevenue, '#1AB7AC'];

            var data = google.visualization.arrayToDataTable(data);
            var container = document.getElementById('report_chart_clone');
            var options = {
                height: 400,
                legend: { position: 'none' },
                chartArea: { left: '15%', width: '80%', height: '70%' }, 
                vAxis: {
                    format: 'short',
                    viewWindowMode: 'explicit',
                    viewWindow: { min: 0 }
                },
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.ColumnChart(container);   
            var runOnce = google.visualization.events.addListener(chart, 'ready', function () {
                google.visualization.events.removeListener(runOnce);
                var leftVal, newLeftTicks = [];
                var leftTicks = chart.ia.hd[0].La;
                var maxLeftTick = 0;
                var leftInterval = 0;
    
                for (var i = 0;i < leftTicks.length; i++) {
                    if (leftTicks[i].Da > maxLeftTick) {
                        maxLeftTick = leftTicks[i].Da;
                    }
                }
    
                if (maxLeftTick == 1) {
                    leftInterval = 1;
                }
                else {
                    let tempValue = parseInt(maxLeftTick / 5);
                    let valueLength = tempValue.toString().length;
            
                    if (valueLength == 1) {
                        if (tempValue >= 5 && tempValue < 10) {
                            leftInterval = 10;
                        }
                        else if (tempValue >= 3 && tempValue < 5) {
                            leftInterval = 5;
                        }
                        else {
                            leftInterval = tempValue + 1;
                        }
                    }
                    else {            
                        tempValue = Math.round(maxLeftTick / 5 / Math.pow(10, valueLength - 1) + 0.5);                
                        leftInterval =  tempValue * Math.pow(10, valueLength - 1);
                    }
                }
    
                for (var i = 0; i < 6; i++) {
                    leftVal = leftInterval * i;

                    if (leftVal >= 1000000000) {
                        formatedVal = app.formatNumberToUserFromNumber(leftVal / 1000000000);
                        suffix = app.vtranslate('Reports.JS_REPORT_BILION');
                    }
                    else if (leftVal >= 1000000) {
                        formatedVal = app.formatNumberToUserFromNumber(leftVal / 1000000);
                        suffix = app.vtranslate('Reports.JS_REPORT_MILION');
                    }
                    else if (leftVal >= 1000) {
                        formatedVal = app.formatNumberToUserFromNumber(leftVal / 1000);
                        suffix = app.vtranslate('Reports.JS_REPORT_THOUSAND');
                    }
                    else {
                        formatedVal = leftVal;
                        suffix = '';
                    }
    
                    newLeftTicks.push({ v: leftVal, f: formatedVal + ' ' + suffix });
                }
    
                options.vAxes = options.vAxes || {};            
                options.vAxis.ticks = newLeftTicks;
                options.vAxis.viewWindow.max = leftInterval * 5;
                chart.draw(data, options);
            });

            chart.draw(data, options);
        }

        var modalParams = {
            cb: callBackFunction
        }

        app.helper.showModal(declareModal, modalParams);
    })
}

function initReportButtons (container) {
    CustomReportHelper.initCustomButtons(container);
}
