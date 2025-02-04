{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div id="main-menu-list">
		{assign var=MAIN_MENUS value=Settings_MenuEditor_Data_Model::getAllMainMenus()}

		{foreach item=MAIN_MENU key=KEY from=$MAIN_MENUS}
			<div class="main-menu {if $MAIN_MENU.id == $SELECTED_MAIN_MENU_ID}selected{/if}" data-id="{$MAIN_MENU.id}" style="border-left: 5px solid {$MAIN_MENU.color};">
				<i class="{$MAIN_MENU.icon} icon pull-left"></i>
				<div class="name textOverflowEllipsis">{$MAIN_MENU.name}</div>

				<div class="actions pull-right">
					<span class="btn-edit cursorPointer"><i class="fal fa-pen"></i></span>
					<span class="btn-remove cursorPointer"><i class="fal fa-circle-xmark"></i></span>
					<span class="btn-move cursorDrag"><i class="fal fa-grip-lines"></i></span>
				</div>
			</div>
		{/foreach}
	</div>
{/strip}