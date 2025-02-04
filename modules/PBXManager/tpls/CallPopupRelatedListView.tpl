{strip}
    <input type="hidden" class="total-count" value="{$TOTAL_COUNT}" />
    {if sizeof($DATAROWS) > 0}
        <table class="related-list">
            <thead>
                <tr>
                    {foreach from=$HEADERS item=field}
                        <th class="related-title {$field}">
                            {if $MODULE_NAME === 'Calendar'}
                                {if $field === 'events_call_direction'}
                                    {vtranslate('LBL_CALL_POPUP_CALL_DIRECTION', 'PBXManager')}
                                {else if $field === 'subject'}
                                    {vtranslate('LBL_CALL_POPUP_CALL_SUBJECT', 'PBXManager')}
                                {else if $field === 'date_start'}
                                    {vtranslate('LBL_CALL_POPUP_CALL_DATE_START', 'PBXManager')}
                                {else if $field === 'eventstatus'}
                                    {vtranslate('LBL_CALL_POPUP_CALL_EVENT_STATUS', 'PBXManager')}
                                {* Modified by Vu Mai on 2023-03-03 to add telesales campaign *}
                                {else if $field === 'related_campaign'}
                                    {vtranslate('LBL_TELESALES_CAMPAIGN')}       
                                {* End Vu Mai *}
                                {else}
                                    {vtranslate($FIELD_MODELS[$field]->get('label'), $MODULE_NAME)}
                                {/if}
                            {else}
                                {vtranslate($FIELD_MODELS[$field]->get('label'), $MODULE_NAME)}
                            {/if}
                        </th>
                    {/foreach}
                </tr>
            </thead>
            <tbody>
                {foreach from=$DATAROWS item=row}
                    <tr>
                        {foreach from=$HEADERS item=field}
                            {assign var=FIELD_VALUE value=$row[$field]}
                            {assign var=FIELD_MODEL value=$FIELD_MODELS[$field]->set('fieldvalue', $FIELD_VALUE)}
                            <td title="{strip_tags($FIELD_MODEL->getDisplayValue($FIELD_VALUE))}" class="related-item {$field}">
                                {if $field == 'subject' || $FIELD_MODEL->get('uitype') == '4'}
                                    <a target="_blank" href="{getRecordDetailUrl($row['id'], $MODULE_NAME)}">{$FIELD_MODEL->getDisplayValue($FIELD_VALUE)}</a>
                                {else if $MODULE_NAME === 'Calendar' && $field === 'events_call_direction'}
                                    <div class="field-value-wraper events_call_direction {$FIELD_VALUE}">
                                        {if $FIELD_VALUE === 'Inbound'}
                                            <i class="far fa-arrow-down events_call_direction" aria-hidden="true"></i>
                                        {else if $FIELD_VALUE === 'Outbound'}
                                            <i class="far fa-arrow-up events_call_direction" aria-hidden="true"></i>
                                        {/if}
                                    </div>
                                {else}
                                    {include file="modules/PBXManager/tpls/CallPopupRelatedListViewDefaultCell.tpl" MODULE=$MODULE_NAME}
                                {/if}
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}
            </tbody>
        <table>
        {if $TOTAL_COUNT > 5}
            <br />
            <a href="javascript:void(0)" class="relatedListFull">{vtranslate('LBL_CALL_POPUP_SEE_ALL', 'PBXManager')}</a>
        {/if}
    {else}
        <div class="related-list-no-data">
            {vtranslate('LBL_CALL_POPUP_NO_DATA', 'PBXManager')}
        </div>
    {/if}
{/strip}