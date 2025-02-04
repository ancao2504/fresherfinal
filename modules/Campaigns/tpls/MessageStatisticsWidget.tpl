{*
    Name: MessageStatisticsWidget.tpl
    Author: Phu Vo
    Date: 2020.11.17
*}

{assign var=STATISTIC_HEADERS value=$MESSAGE_STATISTICS_WIDGET_MODEL->getWidgetStatisticHeaders()}
{assign var=DATA_TABLE_HEADERS value=$MESSAGE_STATISTICS_WIDGET_MODEL->getWidgetDataTableHeaders()}
{assign var=STATISTIC_DATA value=$MESSAGE_STATISTICS_WIDGET_MODEL->getWidgetStatisticData(['record' => $RECORD->getId()])}

{strip}
    <div id="messageStatistics" class="summaryWidgetContainer">
        <div class="widget-container">
            <div class="widget_header clearfix">
                <h4 class="display-inline-block pull-left">{vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_HEADER_MESSAGE_STATISTICS', 'Campaigns')}</h4>
            </div>
            <div class="widget_contents">
                <div class="message-statistics-container">
                    <table class="table table-bordered" style="width: 100%"">
                        <thead>
                            <tr>
                                {foreach from=$STATISTIC_HEADERS item=header}
                                    <th class="th {$header['name']}" style="{if !empty($header['width'])} width: {$header['width']};{/if}">{$header['label']}</th>
                                {/foreach}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                {foreach from=$STATISTIC_HEADERS item=header}
                                    <td class="td {$header['name']}" style="{if !empty($header['width'])} width: {$header['width']};{/if}">{$STATISTIC_DATA[$header['name']]}</td>
                                {/foreach}
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="widget_header clearfix">
                <h4 class="display-inline-block pull-left">{vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_HEADER_DETAILS', 'Campaigns')}</h4>
            </div>
            <div class="widget_contents">
                <div class="message-statistics-detail-container">
                    <table id="message-statistics-detail" class="table table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    {foreach from=$DATA_TABLE_HEADERS item=header}
                                        <th class="th {$header['name']}">{$header['label']}</th>
                                    {/foreach}
                                </tr>
                            </thead>
                            <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        var _MESSAGE_STATISTICS_DATA_TABLE_HEADERS = {ZEND_JSON::encode($DATA_TABLE_HEADERS)};
    </script>

    <link rel="stylesheet" type="text/css" href="{vresource_url('modules/Campaigns/resources/MessageStatisticsWidget.css')}">
    <script type="text/javascript" src="{vresource_url('modules/Campaigns/resources/MessageStatisticsWidget.js')}"></script>
{/strip}
