{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div class="row-fluid">
		<input type="hidden" name="layout" value="{$MAIN_MENU_INFO.layout}" />
		{assign var=MENU_GROUPS value=Settings_MenuEditor_Data_Model::getMenuGroupsByMainMenu($MAIN_MENU_INFO.id)}

		<div class="menu-groups">
			{foreach item=MENU_GROUP key=KEY from=$MENU_GROUPS}
				{assign var=MENU_ITEMS value=Settings_MenuEditor_Data_Model::getMenuItemsByMenuGroup($MAIN_MENU_INFO.id, $MENU_GROUP.id)}

				{if !empty($MENU_ITEMS) || $MENU_GROUP.id != 'uncategorized'}
					<div class="menu-group box shadowed {if $MENU_GROUP.id != 'uncategorized'}sortable{/if}" data-id="{$MENU_GROUP.id}">
						<div class="box-header">
							<div class="name textOverflowEllipsis">{$MENU_GROUP.name}</div>

							{if $MENU_GROUP.id != 'uncategorized'}
								<div class="actions pull-right">
									<span class="btn-edit cursorPointer show-on-hover"><i class="fal fa-pen"></i></span>
									<span class="btn-remove cursorPointer show-on-hover"><i class="fal fa-circle-xmark"></i></span>
									<span class="btn-move cursorDrag"><i class="fal fa-grip-lines"></i></span>
								</div>
							{/if}
						</div>
						<div class="box-body">
							<div class="menu-items fancyScrollbar">
								{foreach item=MENU_ITEM key=KEY from=$MENU_ITEMS}
									<div class="menu-item {if $MENU_GROUP.id != 'uncategorized'}sortable{/if}" data-type="{$MENU_ITEM.type}" data-id="{$MENU_ITEM.id}">
										<i class="{if $MENU_ITEM.type == 'module'}{$MENU_ITEM.icon}{else}{$MENU_ITEM.value.icon}{/if} icon pull-left"></i>
										<div class="name textOverflowEllipsis">{$MENU_ITEM.name}</div>

										{if $MENU_GROUP.id != 'uncategorized'}
											<div class="actions pull-right">
												{if $MENU_ITEM.type != 'module'}
													<span class="btn-edit cursorPointer show-on-hover"><i class="fal fa-pen"></i></span>
												{/if}

												<span class="btn-remove cursorPointer show-on-hover"><i class="fal fa-circle-xmark"></i></span>
												<span class="btn-move cursorDrag"><i class="fal fa-grip-lines"></i></span>
											</div>
										{/if}
									</div>
								{/foreach}
							</div>
						</div>

						{if $MENU_GROUP.id != 'uncategorized'}
							<div class="box-footer">
								<div class="actions text-center">
									<span class="btn-add-menu-item cursorPointer"><i class="fal fa-circle-plus"></i></span>
								</div>
							</div>
						{/if}
					</div>
				{/if}
			{/foreach}

			<div class="clearFix"></div>
		</div>

		<div class="clearFix"></div>
	</div>
{/strip}