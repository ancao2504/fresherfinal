{*
    CallsSummaryTodayWidgetContents.tpl
    Author: Hieu Nguyen
    Date: 2019-09-30
*}

{strip}
    {if count($DATA) > 0}
        <div class="row item">
            <div class="row main-data">
                <span class="value">{number_format($DATA['today']['calls_count'])}</span>
                {vtranslate('LBL_WIDGET_CALLS', $MODULE_NAME)}
            </div>
            <div class="row sub-data {if !$SHOW_COMPARE}invisible{/if}">
                <span class="value {if $DATA['compare_count_ratio'] >= 0} good {else} bad {/if}">
                    {if $DATA['compare_count_ratio'] >= 0} <i class="far fa-sort-up"></i> {else} <i class="far fa-sort-down"></i> {/if}
                    {number_format(abs($DATA['compare_count_ratio']))} %
                </span>
                {vtranslate('LBL_WIDGET_FROM_YESTERDAY', $MODULE_NAME)} ({number_format($DATA['yesterday']['calls_count'])})
            </div>
        </div>
        <div class="row item">
            <div class="row main-data">
                <span class="value">{number_format($DATA['today']['total_duration'], 1)}</span>
                {vtranslate('LBL_WIDGET_MINUTES', $MODULE_NAME)}
            </div>
            <div class="row sub-data {if !$SHOW_COMPARE}invisible{/if}">
                <span class="value {if $DATA['compare_duration_ratio'] >= 0} good {else} bad {/if}">
                    {if $DATA['compare_duration_ratio'] >= 0} <i class="far fa-sort-up"></i> {else} <i class="far fa-sort-down"></i> {/if}
                    {number_format(abs($DATA['compare_duration_ratio']), 1)} %
                </span>
                {vtranslate('LBL_WIDGET_FROM_YESTERDAY', $MODULE_NAME)} ({number_format($DATA['yesterday']['total_duration'], 1)})
            </div>
        </div>
        <div class="row item">
            <div class="row main-data">
                <span class="value">{number_format($DATA['today']['duration_per_call'], 1)}</span>
                {vtranslate('LBL_WIDGET_MINUTES', $MODULE_NAME)} / {vtranslate('LBL_WIDGET_CALLS', $MODULE_NAME)}
            </div>
            <div class="row sub-data {if !$SHOW_COMPARE}invisible{/if}">
                <span class="value {if $DATA['compare_duration_per_call_ratio'] >= 0} good {else} bad {/if}">
                    {if $DATA['compare_duration_per_call_ratio'] >= 0} <i class="far fa-sort-up"></i> {else} <i class="far fa-sort-down"></i> {/if}
                    {number_format(abs($DATA['compare_duration_per_call_ratio']), 1)} %
                </span>
                {vtranslate('LBL_WIDGET_FROM_YESTERDAY', $MODULE_NAME)} ({number_format($DATA['yesterday']['duration_per_call'], 1)})
            </div>
        </div>
    {else}
        <span class="noDataMsg">
            {vtranslate('LBL_NO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
        </span>
    {/if}
{/strip}