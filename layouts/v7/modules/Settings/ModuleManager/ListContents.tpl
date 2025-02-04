{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/ModuleManager/views/List.php *}

{strip}
	{* Added by Hieu Nguyen on 2018-07-31 *}
	{include file='modules/Settings/ModuleManager/tpls/ModuleBuilderForm.tpl'}
	{* End Hieu Nguyen *}

	<div class="listViewPageDiv detailViewContainer" id="moduleManagerContents">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
			<div id="listview-actions" class="listview-actions-container">
				<div class="clearfix">
					<h4 class="pull-left">{vtranslate('LBL_MODULE_MANAGER', $QUALIFIED_MODULE)}</h4>

					{* Modified by Hieu Nguyen on 2018-12-24 *}
					{if isDeveloperMode()}
						<div class="pull-right">
							{* Added by Hieu Nguyen on 2018-08-08 *}
							<div class="btn-group">
								<button type="button" id="btnCreateModule" class="btn btn-primary">
									{vtranslate('LBL_MODULE_BUILDER_BTN_CREATE_NEW_MODULE', $QUALIFIED_MODULE)}
								</button>
							</div>&nbsp;
							{* End Hieu Nguyen *}

							<div class="btn-group">
								<button class="btn btn-default" type="button" onclick='window.location.href="{$IMPORT_USER_MODULE_FROM_FILE_URL}"'>
									{vtranslate('LBL_IMPORT_MODULE_FROM_ZIP', $QUALIFIED_MODULE)}
								</button>
							</div>&nbsp;
							<div class="btn-group">
								<button class="btn btn-default" type="button" onclick='window.location.href = "{$IMPORT_MODULE_URL}"'>
									{vtranslate('LBL_EXTENSION_STORE', 'Settings:ExtensionStore')}
								</button>
							</div>
						</div>
					{/if}
					{* End Hieu Nguyen *}
				</div>
			</div>
			<div class="contents">
				{assign var=COUNTER value=0}
				<table class="table table-bordered modulesTable">
					<tr>
						{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
							{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
							{assign var=MODULE_ACTIVE value=$MODULE_MODEL->isActive()}
							{assign var=MODULE_LABEL value=vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->get('name'))}
							{if $COUNTER eq 2}
							</tr><tr>
								{assign var=COUNTER value=0}
							{/if}
							<td class="ModulemanagerSettings">
								<div class="moduleManagerBlock">
									{* Added by Hieu Nguyen on 2021-11-04 to show toggle on/off module for developer only *}
									{if isDeveloperMode()}
										<span class="col-lg-1" style="line-height: 2.5;">
											<input type="checkbox" value="" name="moduleStatus" data-module="{$MODULE_NAME}" data-module-translation="{$MODULE_LABEL}" {if $MODULE_MODEL->isActive()}checked{/if} />
										</span>
									{/if}
									{* End Hieu Nguyen *}
									<span class="col-lg-1 moduleImage {if !$MODULE_ACTIVE}dull {/if}">
										{* Modified by Phu Vo on 2021.05.21 to load module icon from config *}
										<div class="alignMiddle flex-center-center">
											{$MODULE_MODEL->getModuleIcon()}
										</div>
										{* End Phu Vo *}
									</span>
									<span class="col-lg-7 moduleName {if !$MODULE_ACTIVE} dull {/if}"><h5 style="line-height: 0.5;">{$MODULE_LABEL}</h5></span>
										{assign var=SETTINGS_LINKS value=$MODULE_MODEL->getSettingLinks()}
										{if !in_array($MODULE_NAME, $RESTRICTED_MODULES_LIST) && (count($SETTINGS_LINKS) > 0)}
										<span class="col-lg-3 moduleblock">
											<span class="btn-group pull-right actions {if !$MODULE_ACTIVE}hide{/if}">
												<button class="btn btn-default dropdown-toggle unpin hiden " data-toggle="dropdown">
													{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}&nbsp;<i class="caret"></i>
												</button>
												<ul class="dropdown-menu pull-right dropdownfields">
													{foreach item=SETTINGS_LINK from=$SETTINGS_LINKS}
														{* Added by Hieu Nguyen on 2022-05-10 to support access Layout Editor for developer only by config *}
														{assign var='LAYOUT_EDITOR_CONFIG' value=getGlobalVariable('layoutEditorConfig')}

														{if $SETTINGS_LINK['linklabel'] == 'LBL_EDIT_FIELDS' && !isDeveloperMode()}
															{if !empty($LAYOUT_EDITOR_CONFIG['modules_allow_developer_only']) && in_array($MODULE_NAME, $LAYOUT_EDITOR_CONFIG['modules_allow_developer_only']) }
																{continue}
															{/if}
														{/if}
														{* End Hieu Nguyen *}

														{if $MODULE_NAME eq 'Calendar'}
															{if $SETTINGS_LINK['linklabel'] eq 'LBL_EDIT_FIELDS'}
																<li><a href="{$SETTINGS_LINK['linkurl']}&sourceModule=Events">{vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME, vtranslate('LBL_EVENTS',$MODULE_NAME))}</a></li>
																<li><a href="{$SETTINGS_LINK['linkurl']}&sourceModule=Calendar">{vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME, vtranslate('LBL_TASKS','Calendar'))}</a></li>
															{else if $SETTINGS_LINK['linklabel'] eq 'LBL_EDIT_WORKFLOWS'} 
																<li><a href="{$SETTINGS_LINK['linkurl']}&sourceModule=Events">{vtranslate('LBL_EVENTS', $MODULE_NAME)} {vtranslate('LBL_WORKFLOWS',$MODULE_NAME)}</a></li>	
																<li><a href="{$SETTINGS_LINK['linkurl']}&sourceModule=Calendar">{vtranslate('LBL_TASKS', 'Calendar')} {vtranslate('LBL_WORKFLOWS',$MODULE_NAME)}</a></li>
															{else}
																<li><a href={$SETTINGS_LINK['linkurl']}>{vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME, vtranslate($MODULE_NAME, $MODULE_NAME))}</a></li>
															{/if}
														{else}
															<li>
																<a	{if stripos($SETTINGS_LINK['linkurl'], 'javascript:')===0}
																		onclick='{$SETTINGS_LINK['linkurl']|substr:strlen("javascript:")};'
																	{else}
																		onclick='window.location.href = "{$SETTINGS_LINK['linkurl']}"'
																	{/if}>
																	{vtranslate($SETTINGS_LINK['linklabel'], $MODULE_NAME, vtranslate("SINGLE_$MODULE_NAME", $MODULE_NAME))}
																</a>
															</li>
														{/if}
													{/foreach}
												</ul>
											</span>
										</span>
									{/if}
								</div>
								{assign var=COUNTER value=$COUNTER+1}
							</td>
						{/foreach}
					</tr>
				</table>
			</div>
		</div>
	</div>
{/strip}
