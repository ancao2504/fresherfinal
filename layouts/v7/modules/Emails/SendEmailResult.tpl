{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Emails/views/MassSaveAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}

<div class="modal-dialog">
	<div class="modal-content">
	{* Modified by Phu Vo on 2021.11.15 *}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_RESULT')} 
		<div class="modal-body">
			{if $SUCCESS}
				{if $FLAG == 'SAVED'}
					<div class="mailSentSuccessfully" data-relatedload="{$RELATED_LOAD}">
						{vtranslate('LBL_MAIL_SAVED_SUCCESSFULLY')}
					</div>
				{else}
					<div class="mailSentSuccessfully" data-relatedload="{$RELATED_LOAD}">
						{vtranslate('LBL_MAIL_SENT_SUCCESSFULLY')}
					</div>
				{/if}
				{if $FLAG}
					<input type="hidden" id="flag" value="{$FLAG}">
				{/if}
			{else}
				<div class="failedToSend" data-relatedload="false">
					{vtranslate('LBL_FAILED_TO_SEND')}
					<br>
					{$MESSAGE}
				</div>
			{/if}
		</div>
	</div>
	{* End Phu Vo *}
</div>
