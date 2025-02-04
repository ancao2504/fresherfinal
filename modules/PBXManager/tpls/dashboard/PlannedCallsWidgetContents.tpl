{*
    PlannedCallsWidgetContents.tpl
    Author: Hieu Nguyen
    Date: 2019-10-23
*}

{strip}
    {if count($DATA) > 0}
        <table class="widgetTable table-highlighted tbl-planned-calls"> {* Modified by Phu Vo on 2022.01.21 *}
            <thead>
                <th>{vtranslate('LBL_SUBJECT', 'Events')}</th>
                <th>{vtranslate('LBL_RELATED_CUSTOMER', 'Contacts')}</th>
                <th>{vtranslate('Start Date & Time', 'Events')}</th>
                <th width="100px" class="text-center">{vtranslate('LBL_ACTIONS', 'Events')}</th>
            </thead>
            <tbody>
                {foreach item=CALL from=$DATA}
                    <tr class="widgetRow">
                        <td>
                            <a target="_blank" href="{getRecordDetailUrl($CALL.activityid, 'Calendar')}">{decodeUTF8($CALL.subject)}</a>
                        </td>
                        <td>{$CALL.customer_name_with_link}</td>
                        <td class="{$CALL.highlight_color}">{$CALL.date_start} {substr($CALL.time_start, 0, 8)}</td>
                        <td class="text-center">
                            {if PBXManager_Logic_Helper::canMakeCall() && count($CALL.phone_numbers) > 0}
                                <a href="javascript:void(0);" onclick='Vtiger_PBXManager_Js.makeCallWithPhoneSelector(this, {$CALL.customer_id}, "{$CALL.customer_name}", {Zend_Json::encode($CALL.phone_numbers)}, "{$CALL.activityid}");' title="{vtranslate('LBL_MAKE_CALL', 'PBXManager')}">
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