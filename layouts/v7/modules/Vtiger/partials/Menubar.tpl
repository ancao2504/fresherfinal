{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* [CustomMenu] Modified by Vu Mai on 2022-02-08 *}

{if $MENU_STRUCTURE}
{assign var="topMenus" value=$MENU_STRUCTURE->getTop()}
{assign var="moreMenus" value=$MENU_STRUCTURE->getMore()}

{assign var=MENU_ITEMS value=Settings_MenuEditor_Structure_Model::getModuleNavicatorStructure($MENU_ID, $MENU_GROUP_ID)}

<div id="modules-menu" class="modules-menu">
	{foreach item=ITEM from=$MENU_ITEMS}
		<ul title="{$ITEM.name}" data-toggle="tooltip" data-placement="right">
			<li class="{if $ITEM.id == $MENU_ITEM_ID}active{/if}">
				<a href="{if $ITEM.type == 'module'}{$ITEM.url}{else}{$ITEM.value.url}{/if}">
				<i class="{if $ITEM.type == 'module'}{$ITEM.icon}{else}{$ITEM.value.icon}{/if}"></i>
					<span>{$ITEM.name}</span>
				</a>
			</li>
		</ul>
	{/foreach}
</div>
{/if}
