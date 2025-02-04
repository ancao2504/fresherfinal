/*
    SocialReport.js
    Author: Phuc Lu
    Date: 2019-08-30
    Purpose: to handle custom logic on Campaign's Report Modal
*/

function viewSocialReport() {
    var modalTemplate = jQuery('#socialReportModal').clone(true, true);
    modalTemplate.removeClass('hide');
    var form = modalTemplate.find('form');
    
    // Setup inital values
    callBackFunction = function () {
        app.helper.showProgress();
        var params = {
            'module': 'Campaigns',
            'action': 'SocialReportAjax',
            'record': jQuery('input[name="record_id"]').val()
        };

        app.request.post({ data: params }).then(function(error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR', 'Campaigns')});
                return;
            }

            jQuery(form).find('.div_sent_source_chart').attr('id', 'div_sent_source_chart');
            jQuery(form).find('.div_sent_status_chart').attr('id', 'div_sent_status_chart');
            jQuery(form).find('.div_sent_detail_chart').attr('id', 'div_sent_detail_chart');
            jQuery(form).find('.div_collected_data_chart').attr('id', 'div_collected_data_chart');
            jQuery(form).find('.div_related_data_chart').attr('id', 'div_related_data_chart');

            drawColumnChart('div_sent_source_chart', data.social_message_report.source_chart_array);
            drawColumnChart('div_collected_data_chart', data.campaign_result.collected_data_chart_array);
            drawColumnChart('div_related_data_chart', data.campaign_result.outcome_chart_array);
            drawBarChart('div_sent_detail_chart', data.social_message_report.result_detail_chart_array, ['#FFFF80', '#039D12', '#EC808D']);

            jQuery(form).find('.zalo_number').html(data.social_message_report.sources.formatted_zalo);
            jQuery(form).find('.total_number').html(data.social_message_report.sources.formatted_total);
            jQuery(form).find('.sent_number').html(data.social_message_report.results.formatted_sent);

            jQuery(form).find('.filter_city').html(data.social_article_report.filters.cities);
            jQuery(form).find('.filter_age').html(data.social_article_report.filters.ages);
            jQuery(form).find('.filter_gender').html(data.social_article_report.filters.genders);
            jQuery(form).find('.filter_platform').html(data.social_article_report.filters.platforms);

            jQuery.each(data.social_article_report.articles, function(key, article) {
                let newRow = '<tr><td><a target = "_blank" title="' + article.name + '" href = "index.php?module=CPSocialArticle&view=Detail&record=' + key + '&app=MARKETING">' + article.name + '</td><td><span title="' + article.social_holder_name + '">' + article.social_holder_name + '</span></td></tr>';
                
                jQuery(form).find('#tbl_article_list tbody').append(newRow);
            })

            jQuery(form).find('#tbl_article_list').DataTable({
                ordering: false,
                searching: false,
                pageLength: 5,
                lengthChange: false,
                oLanguage: {
                    sEmptyTable: app.vtranslate('JS_DATATABLES_NO_DATA_AVAILABLE'),
                    sInfo: app.vtranslate('JS_DATATABLES_FOOTER_INFO'),
                    sInfoEmpty: app.vtranslate('JS_DATATABLES_FOOTER_INFO_NO_ENTRY'),
                    sZeroRecords: app.vtranslate('JS_DATATABLES_NO_RECORD'),
                    oPaginate: {
                        sNext: app.vtranslate('JS_DATATABLES_PAGINATE_NEXT_PAGE'),
                        sPrevious: app.vtranslate('JS_DATATABLES_PAGINATE_PREVIOUS_PAGE')
                    },
                }
            });    
        })
    };

    var modalParams = {
        cb: callBackFunction
    };

    app.helper.showModal(modalTemplate, modalParams);

    return false;
}

function drawColumnChart(elementId, dataArray) {
    google.charts.load('current', { 'packages': ['corechart'] });
    google.charts.setOnLoadCallback(drawChart);
    var dataArray = dataArray;

    function drawChart() {
        // Create the data table.
        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            height: 300,
            legend: { position: 'none' },
            chartArea: { width: '80%', height: '70%' }, 
            vAxis: {
                format: 'short',
                viewWindowMode: 'explicit',
                viewWindow: { min: 0 }
            },
        };

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById(elementId));
        chart.draw(data, options);
    }
}

function drawBarChart(elementId, dataArray, color) {
    google.charts.load('current', { 'packages': ['corechart'] });
    google.charts.setOnLoadCallback(drawChart);
    var dataArray = dataArray;
    var color = color;

    function drawChart() {
        // Create the data table.
        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            isStacked: 'percent',
            height: 200,
            legend: { position: 'bottom' },
            vAxis: {                
                gridlines: { count: 0, color: 'transparent' },
                baselineColor: 'transparent'
            },
            hAxis: {       
                minValue: 0,
                ticks: [0, .3, .6, .9, 1], 
                gridlines: { count: 0, color: 'transparent' },
                baselineColor: 'transparent'
            },
            chartArea: { left: 0 },
            colors: color,            
            chartArea: { width: '80%', height: '25%' }, 
        };

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.BarChart(document.getElementById(elementId));
        chart.draw(data, options);
    }
}
