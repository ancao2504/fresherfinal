{*
    MissedCallsWidgetContents.tpl
    Author: Hieu Nguyen
    Date: 2019-10-23
*}

{strip}
    {if count($DATA) > 0}
        <table class="widgetTable table-highlighted tbl-missed-calls"> {* Modified by Phu Vo on 2022.01.21 *}
            <thead>
                <th>{vtranslate('LBL_WIDGET_CUSTOMER_NAME', 'PBXManager')}</th>
                <th>{vtranslate('LBL_WIDGET_PHONE_NUMBER', 'PBXManager')}</th>
                <th>{vtranslate('LBL_WIDGET_TIME', 'PBXManager')}</th>
                <th class="text-center">{vtranslate('LBL_MISSED_CALL_WIDGET_MISSED_CALLS_COUNT', 'PBXManager')}</th>
                <th width="100px" class="text-center">{vtranslate('LBL_ACTIONS', 'Events')}</th>
            </thead>
            <tbody>
                {foreach item=CALL from=$DATA}
                    <tr>
                        <td>
                            {if $CALL.customer_id}
                                <a target="_blank" href="{getRecordDetailUrl($CALL.customer_id, $CALL.customer_type)}">{$CALL.customer_name}</a>
                            {else}
                                <span>N/A</span>
                            {/if}
                        </td>
                        <td>{$CALL.phone_number}</td>
                        <td>{$CALL.date_start} {substr($CALL.time_start, 0, 8)}</td>
                        <td class="text-center">{$CALL.missed_calls_count}</td>
                        <td class="text-center">
                            {if PBXManager_Logic_Helper::canMakeCall()}
                                <a href="javascript:void(0);" onclick='MissedCallsWidget.makeCall(this, "{$CALL.customer_id}", "{$CALL.phone_number}");' title="{vtranslate('LBL_MAKE_CALL', 'PBXManager')}">
                                    <i class="far fa-phone"></i>
                                </a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        <span class="noDataMsg">
            {vtranslate('LBL_NO')} {vtranslate('Call', 'Events')} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
        </span>
    {/if}
{/strip}