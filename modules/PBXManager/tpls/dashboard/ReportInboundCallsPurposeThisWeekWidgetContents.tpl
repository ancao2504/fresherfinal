{*
    ReportInboundCallsPurposeThisWeekWidgetContents.tpl
    Author: Hieu Nguyen
    Date: 2020-02-18
*}

{strip}
    {if count($DATA) > 0}
        {assign var=CHART_PLACEHOLDER_ID value="report-inbound-calls-purpose-this-week-widget-chart"}

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