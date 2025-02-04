{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="record-header clearfix">
			{if !$MODULE}
				{assign var=MODULE value=$MODULE_NAME}
			{/if}
			<div class="hidden-sm hidden-xs recordImage bg_{$MODULE} app-{$SELECTED_MENU_CATEGORY}">
				<div class="name"><span><strong>{$MODULE_MODEL->getModuleIcon()}</strong></span></div>
			</div>

			<div class="recordBasicInfo">
				<div class="info-row">
					<h4>
						{* BEGIN-- [FullNameConfig] Modified by Phu Vo on 2020.08.11 to support person module *}
							{if isPersonModule($MODULE_NAME)}
								<span class="recordLabel pushDown" title="{$RECORD->getDisplayValue('salutationtype')}&nbsp;{$RECORD->getName()}"> 
									{if $RECORD->getDisplayValue('salutationtype')}
										<span class="salutation">  {$RECORD->getDisplayValue('salutationtype')}</span>&nbsp;
									{/if}

									{assign var=COUNTER value=0}
									
									{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
										{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}

										{if $FIELD_MODEL->getPermissions()}
											<span class="{$NAME_FIELD}">{trim($RECORD->get($NAME_FIELD))}</span>
											{if $COUNTER eq 0 && ($RECORD->get($NAME_FIELD))}&nbsp;{assign var=COUNTER value=$COUNTER+1}{/if}
										{/if}
									{/foreach}
								</span>
							{else}
								<span class="recordLabel pushDown" title="{$RECORD->getName()}">
									{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
										{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
										{if $FIELD_MODEL->getPermissions()}
											<span class="{$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
										{/if}
									{/foreach}
								</span>
							{/if}
						{* END-- [FullNameConfig] *}
					</h4>
				</div>
				{include file="DetailViewHeaderFieldsView.tpl"|vtemplate_path:$MODULE}
			</div>
		</div>
	</div>
{/strip}