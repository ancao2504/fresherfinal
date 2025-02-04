{*
    SalesByProductGroupReportChart.tpl
    Author: Phuc Lu
    Date: 2020.06.25
*}

{strip}
    <h6 class="widget-title">{$CHART_TITLE}</h6>
    {assign var="PLACE_HOLDER_ID" value="custom-chart-widget-{$WIDGET_ID}"}

    <div id="{$PLACE_HOLDER_ID}" class="chart-area {if $DATA == false}no-chart{/if}">{if $DATA == false}<div class="no-data-info">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</div>{/if}</div>

    <script type="text/javascript" src="{vresource_url("resources/CustomChartWidget.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesByProductGroupReportChart.js")}"></script>
    <script type="text/javascript">
        {if $DATA != false}
            if (typeof google.visualization == 'undefined') {
                google.charts.load('current', { 'packages' : ['corechart'], 'language' : 'vi' });
                google.charts.setOnLoadCallback(function () {
                    new SalesByProductGroupReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)})
                });
            }
            else {
                new SalesByProductGroupReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)});
            }

            jQuery(window).resize(function () {
                new SalesByProductGroupReportChart('{$PLACE_HOLDER_ID}', {Zend_Json::encode($DATA)})
            });
        {/if}
    </script>
{/strip}