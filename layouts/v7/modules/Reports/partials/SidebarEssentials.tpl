{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="sidebar-menu sidebar-menu-full">
		<div class="module-filters" id="module-filters">
			<div class="sidebar-container lists-menu-container">
				<div class="sidebar-header clearfix">
					<h5 class="pull-left">{vtranslate('LBL_FOLDERS', $MODULE)}</h5>
					<button id="createFilter" onclick='Reports_List_Js.triggerAddFolder("index.php?module=Reports&view=EditFolder");' class="btn btn-default pull-right sidebar-btn" title="{vtranslate('LBL_ADD_NEW_FOLDER', $MODULE)}">
						<div class="far fa-plus" aria-hidden="true"></div>
					</button> 
				</div>
				<hr>
				<div>
					<input class="search-list" type="text" placeholder="{vtranslate('LBL_SEARCH_FOR_FOLDERS',$MODULE)}">
				</div>
				{* Modified by Phu Vo on 2021.05.21 to allow report sidebar display all folder *}
				<div class="menu-scroller scrollContainer">
					<div class="list-menu-content">
						<div class="list-group">
							<ul class="lists-menu">
								<li style="font-size:12px;" class="listViewFilter" >
									<a href="#" class='filterName' data-toggle="tooltip" title="{vtranslate('LBL_ALL_REPORTS', $MODULE)}" data-filter-id="All"><i class="far fa-folder foldericon"></i>&nbsp;{vtranslate('LBL_ALL_REPORTS', $MODULE)}</a>
								</li>
								{foreach item=FOLDER from=$FOLDERS name="folderview"}
									{* Added by Hieu Nguyen on 2021-08-19 to check if this feature can be displayed *}
									{if isForbiddenFeature($FOLDER->get('code'))}{continue}{/if}
									{* End Hieu Nguyen *}

									<li style="font-size:12px;" class="listViewFilter" >
										{assign var=VIEWNAME value={vtranslate($FOLDER->getName(),$MODULE)}}
										{* Modified by Hieu Nguyen on 2021-05-26 to show button delete folder *}
										<a href="#" class="filterName" data-toggle="tooltip" title="{$FOLDER->get('description')}" data-filter-id="{$FOLDER->getId()}" {if $FOLDER->isDeletable()}data-can-delete="1"{/if}>
											<i class="far fa-folder foldericon"></i>&nbsp;
											<span class="name">{$VIEWNAME}</span>
										</a>
										{* End Hieu Nguyen *}

										{* Modified by Hieu Nguyen on 2021-09-17 to set folder id in data-filter-id attribute *}
										<div class="pull-right">
											{assign var="FOLDERID" value=$FOLDER->get('folderid')}
											<span class="js-popover-container">
												<span class="far fa-ellipsis-v" data-filter-id="{$FOLDERID}" data-deletable="true" data-editable="true" rel="popover" data-toggle="popover" data-deleteurl="{$FOLDER->getDeleteUrl()}" data-editurl="{$FOLDER->getEditUrl()}" data-toggle="dropdown" aria-expanded="true"></span>
											</span>
										</div>
										{* End Hieu Nguyen *}
									</li>
								{/foreach}
								<li style="font-size:12px;" class="listViewFilter" >
									<a href="#" class='filterName' data-toggle="tooltip" title="{vtranslate('LBL_SHARED_REPORTS', $MODULE)}" data-filter-id="shared"><i class="far fa-folder foldericon"></i>&nbsp;{vtranslate('LBL_SHARED_REPORTS', $MODULE)}</a>
								</li>
							</ul>

							{* Modified by Hieu Nguyen on 2021-09-17 to add class editFolder and deleteFolder *}
							<div id="filterActionPopoverHtml">
								<ul class="listmenu hide" role="menu">
									<li role="presentation" class="editFilter editFolder">
										<a role="menuitem"><i class="far fa-pen"></i>&nbsp;{vtranslate('LBL_EDIT', $MODULE)}</a>
									</li>
									<li role="presentation" class="deleteFilter deleteFolder">
										<a role="menuitem"><i class="far fa-trash-alt"></i>&nbsp;{vtranslate('LBL_DELETE', $MODULE)}</a>
									</li>
								</ul>
							</div>
							{* End Hieu Nguyen *}
						</div>
					</div>
				</div>
				{* End Phu Vo *}
			</div>
		</div>
	</div>
{/strip}