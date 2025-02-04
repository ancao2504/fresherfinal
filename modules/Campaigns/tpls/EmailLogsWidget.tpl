{*
    Name: EmailLogsWidget.tpl
    Author: Phu Vo
    Date: 2020.11.17
*}

{assign var=DATA_TABLE_HEADERS value=$EMAIL_LOGS_WIDGET_MODEL->getWidgetDataTableHeaders()}

{strip}
    <div id="emailLogs" class="summaryWidgetContainer">
        <div class="widget-container">
            <div class="widget_header clearfix">
                <h4 class="display-inline-block pull-left">{vtranslate('LBL_EMAIL_LOGS_WIDGET_HEADER_EMAIL_LOGS', 'Campaigns')}</h4>
            </div>
            <div class="widget_contents">
                <div class="email-logs-detail-container">
                    <table id="email-logs-detail" class="table table-bordered" style="width: 100%">
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
        var _EMAIL_LOGS_DATA_TABLE_HEADERS = {ZEND_JSON::encode($DATA_TABLE_HEADERS)};
    </script>

    <link rel="stylesheet" type="text/css" href="{vresource_url('modules/Campaigns/resources/EmailLogsWidget.css')}">
    <script type="text/javascript" src="{vresource_url('modules/Campaigns/resources/EmailLogsWidget.js')}"></script>
{/strip}
