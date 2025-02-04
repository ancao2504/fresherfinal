{*
    SalesByIndustryReportChart.tpl
    Author: Phuc Lu
    Date: 2020.06.08
*}

{strip}
    <h6 class="widget-title">{$CHART_TITLE}</h6>
    {assign var="PLACE_HOLDER_ID" value="custom-chart-widget-{$WIDGET_ID}"}

    <div id="{$PLACE_HOLDER_ID}" class="chart-area {if $DATA == false}no-chart{/if}">{if $DATA == false}<div class="no-data-info">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</div>{/if}</div>

    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    <script src="{vresource_url('resources/libraries/HighCharts_8.1.0/code/modules/stock.js')}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesByIndustryReportChart.js")}"></script>
    <script type="text/javascript">
        {if $DATA != false}
            if (typeof google.visualization == 'undefined') {
                google.charts.load('current', { 'packages' : ['corechart'], 'language' : 'vi' });
                google.charts.setOnLoadCallback(function () {
                    new SalesByIndustryReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)})
                });
            }
            else {
                new SalesByIndustryReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)});
            }
            
            jQuery(window).resize(function () {
                new SalesByIndustryReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)})
            });
        {/if}
    </script>
{/strip}