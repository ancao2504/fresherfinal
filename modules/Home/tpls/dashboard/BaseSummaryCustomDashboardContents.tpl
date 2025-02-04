{*
    Name: BaseSummaryCustomDashboardContents.tpl
    Author: Phu Vo
    Date: 2020.08.26
*}

{strip}
    <div class="summary-widget-container">
        <div class="summary-container">
            {foreach from=$WIDGET_META['widget_headers'] item=widgetHeader key=index}
                {if !empty($DATA[$widgetHeader['name']])}
                    {assign var=item value=$DATA[$widgetHeader['name']]}
                    <div class="summary-item">
                        <div class="summary-item-title">
                            <label>{$widgetHeader['label']}</label>
                            {if !empty($widgetHeader['tooltip'])}<i class="far fa-info-circle widget-tooltip" data-toggle="tooltip" title="{$widgetHeader['tooltip']}" aria-hidden="true"></i>{/if}
                        </div>
                        <div class="summary-item-value">
                            <span>{$item['value']}</span>
                        </div>
                        {if $WIDGET_META['last_period']}
                            <div class="summary-item-last-period">
                                <div class="summary-item-last-period-label">
                                    {vtranslate('LBL_DASHBOARD_PREVIOUS_TERM')}: <span class="summary-item-last-period-value" data-toggle="tooltip" title="{$item['last_period']}">{formatOverflowNumber($item['last_period'], false)}</span>
                                </div>
                                <div class="summary-item-last-period-change">
                                    {if $item['direction'] == '+'}
                                        <span class="summary-item-last-period-change positive" data-toggle="tooltip" title="{vtranslate('LBL_DASHBOARD_LAST_PERIOD_CHANGE_DESCRIPTION_TITLE', 'Home')}"><i class="far fa-angle-up" aria-hidden="true"></i> {$item['change']} {if $item['change'] != 'N/A'} %{/if}</span>
                                    {else if $item['direction'] == '-'}
                                        <span class="summary-item-last-period-change nagative" data-toggle="tooltip" title="{vtranslate('LBL_DASHBOARD_LAST_PERIOD_CHANGE_DESCRIPTION_TITLE', 'Home')}"><i class="far fa-angle-down" aria-hidden="true"></i> {$item['change']} {if $item['change'] != 'N/A'} %{/if}</span>
                                    {else if $item['direction'] == '0'}
                                        <span class="summary-item-last-period-change na unchange" data-toggle="tooltip" title="{vtranslate('LBL_DASHBOARD_LAST_PERIOD_CHANGE_DESCRIPTION_TITLE', 'Home')}"><i class="far fa-minus" aria-hidden="true"></i> {$item['change']} {if $item['change'] != 'N/A'} %{/if}</span>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    </div>
                    {if !empty($WIDGET_META['column'])}
                        {if ($index + 1) % $WIDGET_META['column'] == 0}
                            </div>
                            <div class="summary-container">
                        {/if}
                    {else}
                        {if count($WIDGET_META['widget_headers']) > 6 && ($index + 1) % 4 == 0}
                            </div>
                            <div class="summary-container">
                        {/if}
                    {/if}
                {/if}
            {/foreach}
        </div>
    </div>

    {if $CONTENT == false}
        <script type="text/javascript">
            jQuery(function($) {
                window.BaseSummaryCustomDashboard.init($('.customWidget.{$WIDGET_NAME}'));
            });
        </script>
    {/if}
{/strip}