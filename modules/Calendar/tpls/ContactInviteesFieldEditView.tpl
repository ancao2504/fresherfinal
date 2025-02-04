{* Added by Hieu Nguyen on 2020-03-24 to customize contact invitees field *}

{strip}
	{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=RECORD_ID value=$smarty.request.record}

    {if $RECORD && $RECORD->get('id') != ''}
        {assign var=RECORD_ID value=$RECORD->get('id')}
    {/if}

    {assign var="SELECTED_CONTACT_INVITEES" value=Events_Invitation_Helper::getInviteesForEdit($RECORD_ID, 'Contacts')}

	<div class="referencefield-wrapper">
		<input name="popupReferenceModule" type="hidden" value="Contacts" />
		<div class="input-group">
			<input id="{$FIELD_NAME}_display" name="contact_invitees" data-fieldname="{$FIELD_NAME}" 
                data-fieldtype="reference" type="text" class="marginLeftZero autoComplete inputElement sourceField"
				data-fieldinfo='{Zend_Json::encode($FIELD_INFO)}' data-fieldtype="multireference" 
                placeholder="{vtranslate('LBL_TYPE_SEARCH', $MODULE)}" data-multiple="true"
				{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                {if $SELECTED_CONTACT_INVITEES} data-selected-tags='{Zend_Json::encode($SELECTED_CONTACT_INVITEES)}' {/if}
			/>
			<span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', $MODULE)}" style="width:30px; height:auto;">
				<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="far fa-search"></i>
			</span>
		</div>

		<!-- Show the add button only if it is edit view  -->
		{if $smarty.request.view eq 'Edit'}
			<span class="createReferenceRecord cursorPointer clearfix" title="{vtranslate('LBL_CREATE', $MODULE)}">
				<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="far fa-plus"></i>
			</span>
		{/if}
	</div>
{/strip}