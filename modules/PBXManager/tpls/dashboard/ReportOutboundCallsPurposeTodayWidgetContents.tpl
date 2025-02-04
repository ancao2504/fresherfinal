{*
    ReportOutboundCallsPurposeTodayWidgetContents.tpl
    Author: Hieu Nguyen
    Date: 2019-10-25
*}

{strip}
    {if count($DATA) > 0}
        {assign var=CHART_PLACEHOLDER_ID value="report-outbound-calls-purpose-today-widget-chart"}

        <script type="text/javascript">
            new ReportCallsPurposeWidget('{$CHART_PLACEHOLDER_ID}', {Zend_Json::encode($DATA)});
        </script>

        <div id="{$CHART_PLACEHOLDER_ID}"></div>
    {else}
        <span class="noDataMsg">
            {vtranslate('LBL_NO')} {vtranslate('Call', 'Events')} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
        </span>
    {/if}
{/strip}