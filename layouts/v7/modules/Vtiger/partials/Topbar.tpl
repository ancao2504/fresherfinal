{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{include file="modules/Vtiger/Header.tpl"}

	{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
	<nav class="navbar navbar-default navbar-fixed-top app-fixed-navbar">
		<div class="container-fluid global-nav">
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-3 app-navigator-container">
					<div class="row">
						<div id="appnavigator" class="col-sm-2 col-xs-2 cursorPointer app-switcher-container" data-app-class="{if $MODULE eq 'Home' || !$MODULE}fa-dashboard{else}{$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}{/if}">
							<div class="row app-navigator">
								<span class="app-icon far fa-bars"></span>
							</div>
						</div>
						<div class="logo-container col-lg-9 col-md-9 col-sm-9 col-xs-9">
							<div class="row">
								<a href="index.php" class="company-logo">
									<img src="{$COMPANY_LOGO->get('imagepath')}" alt="{$COMPANY_LOGO->get('alt')}"/>
								</a>
							</div>
						</div>  
					</div>
				</div>
				<div class="search-links-container col-md-3 col-lg-3 hidden-sm">
					<div class="search-link hidden-xs">
						<span class="far fa-search" aria-hidden="true"></span>
						<input class="keyword-input" type="text" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" value="{$GLOBAL_SEARCH_VALUE}">
						<span id="adv-search" class="adv-search far fa-chevron-down pull-right cursorPointer" aria-hidden="true"></span>
					</div>
				</div>
				<div id="navbar" class="col-sm-6 col-md-3 col-lg-3 collapse navbar-collapse navbar-right global-actions">
					<ul class="nav navbar-nav">
						{* [CloudGOBlogPosts] Added by Vu Mai on 2022-08-05 *}
						<li>
							<div id="blog-posts" class="dropdown">
								<div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
									<a href="javascript: void(0)" class="far fa-bullhorn relative topbar-icon" data-toggle="tooltip" title="{vtranslate('LBL_NEWS', $MODULE)}" aria-hidden="true">
										<span id="blog-posts-counter" class="inline counter bg-danger badge hide"></span>
									</a>
								</div>	
								<div id="blog-posts-dropdown" class="dropdown-menu" role="menu">
									<div id="blog-posts-title">
										<strong>{vtranslate('LBL_WHAT_NEWS', $MODULE)}</strong>
									</div>
									<div id="blog-posts-iframe">
										<iframe src="https://cloudgo.vn/api/blogs?action=getNewPosts"></iframe>
									</div>
									<div id="blog-posts-footer">
										<a href="https://cloudgo.vn/blog" target="_blank">{vtranslate('LBL_MORE', $MODULE)}</a>
									</div>
								</div>
							</div>	
						</li>
						{* End Vu Mai *}

						<li>
							<div class="dropdown">
								<div class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
									<a href="#" id="menubar_quickCreate" class="qc-button far fa-plus-circle" data-toggle="tooltip" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" aria-hidden="true"></a>
								</div>
								<ul class="dropdown-menu quick-create-module-dropdown" role="menu" aria-labelledby="dropdownMenu1">
									<li class="title" style="padding: 5px 0 0 15px;">
										<strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong>
									</li>
									<hr/>
									<li id="quickCreateModules" style="padding: 0 5px;">
										<div class="col-lg-12">
											{* Added by Hieu Nguyen on 2022-03-15 to display dropdown options for Quick Create menus *}
											{assign var='ADVANCED_QUICK_CREATE_MENUS' value=getGlobalVariable('advancedQuickCreateMenus')}
											{* End Hieu Nguyen *}

											{foreach key=moduleName item=moduleModel from=$QUICK_CREATE_MODULES}
												{if $moduleModel->isPermitted('CreateView') || $moduleModel->isPermitted('EditView')}
													{assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
													{assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
													{assign var=hideDiv value={!$moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('EditView')}}
													{if $quickCreateModule == '1'}
														{if $count % 4 == 0}
                                                        <div class="row">
                                                        {/if}
                                                        {* Adding two links,Event and Task if module is Calendar *}
                                                        {if $singularLabel == 'SINGLE_Calendar'}
                                                            {assign var='singularLabel' value='LBL_TASK'}
                                                            <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-3{/if} dropdown">
                                                                <a id="menubar_quickCreate_Events" class="quickCreateModule" data-name="Events"
																	title="{vtranslate('LBL_EVENT', $moduleName)}"
																	data-toggle="tooltip"
                                                                    data-url="index.php?module=Events&view=QuickCreateAjax" href="javascript:void(0)"
																>
																	{$moduleModel->getModuleIcon('Event')}
																	<span class="quick-create-module">
																		{vtranslate('LBL_EVENT', $moduleName)}
																		<i class="far fa-angle-down quickcreateMoreDropdownAction" data-toggle="dropdown"></i>
																	</span>
																</a>
																<ul class="dropdown-menu quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleName}">
																	<li><a href="javascript:vtUtils.openQuickCreateModal('Events', {literal}{data: {'activitytype': 'Call'}}{/literal});"><i class="far fa-phone-plus"></i> {vtranslate('LBL_ADD_CALL', $moduleName)}</a></li>
																	<li><a href="javascript:vtUtils.openQuickCreateModal('Events', {literal}{data: {'activitytype': 'Meeting'}}{/literal});"><i class="far fa-screen-users"></i> {vtranslate('LBL_ADD_MEETING', $moduleName)}</a></li>
																	<li><a href="javascript:vtUtils.openQuickCreateModal('Events');"><i class="far fa-calendar"></i> {vtranslate('LBL_ADD_EVENT', $moduleName)}</a></li>
																</ul>
                                                            </div>
                                                            {if $count % 4 == 3}
                                                                </div>
                                                                <br>
                                                                <div class="row">
                                                            {/if}
															{* Modified by Hieu Nguyen on 2022-09-19 to hide this button when it is not supported *}
															{if Calendar_Module_Model::canCreateActivity('Task')}
																<div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-3{/if}">
																	<a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
																		title="{vtranslate($singularLabel,$moduleName)}"
																		data-toggle="tooltip"
																		data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">{$moduleModel->getModuleIcon('Task')}<span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span></a>
																</div>
																{if !$hideDiv}
																	{assign var='count' value=$count+1}
																{/if}
															{/if}
															{* End Hieu Nguyen *}
                                                        {else if $singularLabel == 'SINGLE_Documents'}
                                                            <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-3{/if} dropdown">
                                                                <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModuleSubmenu dropdown-toggle" data-name="{$moduleModel->getName()}" 
                                                                    title="{vtranslate($singularLabel,$moduleName)}"
																	data-toggle="tooltip"
																	data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                                                    {$moduleModel->getModuleIcon()}
                                                                    <span class="quick-create-module" data-toggle="dropdown">
                                                                        {vtranslate($singularLabel,$moduleName)}
                                                                        <i class="far fa-angle-down quickcreateMoreDropdownAction"></i>
                                                                    </span>
                                                                </a>
                                                                <ul class="dropdown-menu quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleModel->getName()}">
                                                                    <li class="dropdown-header"><i class="far fa-upload"></i> {vtranslate('LBL_FILE_UPLOAD', $moduleName)}</li>
                                                                    <li id="VtigerAction">
                                                                        <a href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                                                            <i class="far fa-cloud-upload"></i> 
                                                                            {vtranslate('LBL_TO_SERVICE', $moduleName, {vtranslate('LBL_VTIGER', $moduleName)})}
                                                                        </a>
                                                                    </li>
                                                                    <li class="dropdown-header"><i class="far fa-link"></i> {vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $moduleName)}</li>
                                                                    <li id="shareDocument"><a href="javascript:Documents_Index_Js.createDocument('E')"><i class="far fa-external-link"></i>{vtranslate('LBL_FROM_SERVICE', $moduleName, {vtranslate('LBL_FILE_URL', $moduleName)})}</a></li>
                                                                    <li role="separator" class="divider"></li>
                                                                    <li id="createDocument"><a href="javascript:Documents_Index_Js.createDocument('W')"><i class="far fa-file-text"></i>{vtranslate('LBL_CREATE_NEW', $moduleName, {vtranslate('SINGLE_Documents', $moduleName)})}</a></li>
                                                                </ul>
                                                            </div>
														{* Added by Hieu Nguyen on 2022-03-15 to display dropdown options for Quick Create menus *}
														{else if $ADVANCED_QUICK_CREATE_MENUS[$moduleName] != null}
															<div class="{if $hideDiv}create_restricted_{$moduleName} hide{else}col-lg-3{/if} dropdown">
																<a id="menubar_quickCreate_{$moduleName}" class="quickCreateModule" data-name="{$moduleName}"
																	title="{vtranslate($singularLabel, $moduleName)}"
																	data-toggle="tooltip"
																	data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)"
																>
																	{$moduleModel->getModuleIcon()}
																	<span class="quick-create-module">
																		{vtranslate($singularLabel, $moduleName)}
																		<i class="far fa-angle-down quickcreateMoreDropdownAction" data-toggle="dropdown"></i>
																	</span>
																</a>
																<ul class="dropdown-menu quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleName}">
																	{assign var='DROPDOWN_OPTIONS' value=$ADVANCED_QUICK_CREATE_MENUS[$moduleName]}

																	{foreach from=$DROPDOWN_OPTIONS key=LABEL item=OPTION}
																		<li><a href="{$OPTION.link}"><i class="far {$OPTION.icon}"></i> {vtranslate($LABEL, $moduleName)}</a></li>
																	{/foreach}
																</ul>
															</div>
														{* End Hieu Nguyen *}
                                                        {else}
                                                            <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-3{/if}">
                                                                <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
																	title="{vtranslate($singularLabel,$moduleName)}"
																	data-toggle="tooltip"
                                                                    data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                                                    {$moduleModel->getModuleIcon()}
                                                                    <span class="quick-create-module">{vtranslate($singularLabel,$moduleName)}</span>
                                                                </a>
                                                            </div>
                                                        {/if}
                                                        {if !$hideDiv}
                                                            {assign var='count' value=$count+1}
                                                        {/if}
                                                        {if $count % 4 == 0}
                                                            </div>
                                                        {/if}
													{/if}
												{/if}
											{/foreach}
										</div>
									</li>
								</ul>
							</div>
						</li>

                        {* Added by Hieu Nguyen on 2019-03-18 *}
                        <li>{include file="modules/CPNotifications/tpls/Notifications.tpl"}</li>
                        {* End Hieu Nguyen *}

                        {* [SocialChatbox] Added by Hieu Nguyen on 2021-01-13 *}
                        <li>{include file="modules/CPSocialIntegration/tpls/SocialChatboxTopbarIcon.tpl"}</li>
                        {* End Hieu Nguyen *}

						{assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
						{assign var=CALENDAR_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Calendar')}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
							<li><div><a href="index.php?module=Calendar&view={$CALENDAR_MODULE_MODEL->getDefaultViewName()}" class="far fa-calendar" data-toggle="tooltip" title="{vtranslate('Calendar','Calendar')}" aria-hidden="true"></a></div></li>
						{/if}
						{assign var=REPORTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Reports')}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($REPORTS_MODULE_MODEL->getId())}
							<li><div><a href="index.php?module=Reports&view=List" class="far fa-chart-mixed" data-toggle="tooltip" title="{vtranslate('Reports','Reports')}" aria-hidden="true"></a></div></li>
						{/if}
                        {* Modified by Hieu Nguyen on 2021-03-29 to redirect to Kanban view instead of the old Task Management popup *}
						{if $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
							<li><div><a href="index.php?module=CPKanban&view=List&source_module=Calendar&selected_picklist=taskstatus" class="far fa-check-square" data-toggle="tooltip" title="{vtranslate('Tasks', 'Calendar')}" aria-hidden="true"></a></div></li>
						{/if}
                        {* End Hieu Nguyen *}
						<li class="dropdown">
							<div>
								<a href="#" class="userName dropdown-toggle" data-toggle="dropdown" role="button">
									<span class="far fa-user" aria-hidden="true" title="{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}
										  ({$USER_MODEL->get('user_name')})"></span>
									<span class="link-text-xs-only hidden-lg hidden-md hidden-sm">{$USER_MODEL->getName()}</span>
								</a>
								<div class="dropdown-menu logout-content" role="menu">
									{* Modified by Phu Vo on 2019.04.24 to refactor profile info layout*}
									<div class="row profile-info">
										<div class="profile-img-wraper">
											{assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
											{if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
												<img src="resources/images/default-user-avatar.png" width="100px" height="100px">
											{else}
												{foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
													{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
														<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="100px" height="100px">
													{/if}
												{/foreach}
											{/if}
										</div>
										<div class="profile-info-wraper">
											<div class="profile-container">
												<h4>{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}</h4>
												<h5 class="textOverflowEllipsis" title='{$USER_MODEL->get('user_name')}'>{$USER_MODEL->get('user_name')}</h5>
												<p>{$USER_MODEL->getUserRoleName()}</p>
											</div>
										</div>
									</div>
									{* End Phu Vo *}
									<div class="logout-footer clearfix">
										<hr style="margin: 10px 0 !important">
										<div class="">
											<span class="pull-left">
												<span class="far fa-user-lock"></span>
												<a id="menubar_item_right_LBL_MY_PREFERENCES" href="{$USER_MODEL->getPreferenceDetailViewUrl()}">{vtranslate('LBL_MY_PREFERENCES')}</a>
											</span>
											<span class="pull-right">
												<span class="far fa-sign-out-alt"></span>
												<a id="menubar_item_right_LBL_SIGN_OUT" href="index.php?module=Users&action=Logout">{vtranslate('LBL_SIGN_OUT')}</a>
											</span>
										</div>
									</div>
								</div>
							</div>
						</li>

						{* Modified by Hieu Nguyen on 2022-08-26 *}
						{if $USER_MODEL->isAdminUser()}
							{include file="layouts/v7/modules/Vtiger/QuickAdminLinks.tpl"}
						{/if}
						{* End Hieu Nguyen *}
					</ul>
				</div>
			</div>
		</div>
{/strip}