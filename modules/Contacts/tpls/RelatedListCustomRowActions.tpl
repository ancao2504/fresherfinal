{*
    File RelatedListCustomRowActions.tpl
    Author: Hieu Nguyen
    Date: 2019-12-26
    Purpose: to add custom buttons in related list rows
*}

{strip}
    {if $RELATED_MODULE_NAME eq 'Calendar'}
        {* Click to Call *}
        {assign var='SYSTEM_CAN_MAKE_CALL' value=PBXManager_Logic_Helper::canMakeCall()}
        {assign var='CALL_LOG_CAN_MAKE_CALL' value=PBXManager_CallLog_Model::canMakeCall($PARENT_RECORD->getId(), $RELATED_RECORD->getId())}

        {if $SYSTEM_CAN_MAKE_CALL && $CALL_LOG_CAN_MAKE_CALL}
            {assign var="CALL_INFO" value=PBXManager_CallLog_Model::getCallInfoToMakeCall($PARENT_RECORD->getId(), $RELATED_RECORD->getId())}

            <a class="make-call" onclick='Vtiger_PBXManager_Js.makeCallWithPhoneSelector(this, {$CALL_INFO.customer_id}, "{$CALL_INFO.customer_name}", {Zend_Json::encode($CALL_INFO.phone_numbers)}, "{$CALL_INFO.activity_id}");'>
                <i title="{vtranslate('LBL_MAKE_CALL', 'PBXManager')}" class="far fa-phone"></i>
            </a>
        {/if}

        {* Play Recording *}
        {assign var="CAN_PLAY_RECORDING" value=PBXManager_CallLog_Model::canPlayRecording($RELATED_RECORD->getId())}

        {if $CAN_PLAY_RECORDING eq true}
            <a class="play-recording" data-call-id="{$RELATED_RECORD->getId()}" data-popup-title="{vtranslate('LBL_RECORDING_POPUP_TITLE', 'PBXManager', ['%call_subject' => $RELATED_RECORD->get('subject')])}">
                <i title="{vtranslate('LBL_RECORDING_POPUP_PLAY_RECORDING', 'PBXManager')}" class="far fa-play"></i>
            </a>
        {/if}
    {/if}
{/strip}