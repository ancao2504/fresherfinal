{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div class="modal-dialog modal-lg modal-content modal-edit-modules-menu-item">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

		<form name="edit-modules-menu-item" class="form-horizontal">
			<input type="hidden" name="main_menu_id" value="{$MAIN_MENU_ID}" />
			<input type="hidden" name="menu_group_id" value="{$MENU_GROUP_ID}" />

			<div class="form-content fancyScrollbar padding20">
				<div>
					<span>{vtranslate('LBL_MODAL_ADD_MODULES_SELECT_MODULES_TO_DISPLAY', $QUALIFIED_MODULE)}</span>
				</div>
				<div>
					<div class="filter-container inputElement">
						<input type="text" name="filter" placeholder="{vtranslate('LBL_MODAL_ADD_MODULES_SELECT_FILTER_PLACEHOLDER', $QUALIFIED_MODULE)}" />
						<i class="fal fa-magnifying-glass"></i>						
					</div>
					<br/>
				</div>
				<div>
					<ul class="module-list fancyScrollbar">
						{foreach item=MODULE_MODEL key=KEY from=$ALL_MODULES}
							{assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
							<li class="module">
								<label class="textOverflowEllipsis" style="cursor:pointer;"><input type="checkbox" value="{$MODULE_NAME}" {if in_array($MODULE_NAME, $SELECTED_MODULES)}checked{/if} />&nbsp;{vtranslate($MODULE_NAME, $MODULE_NAME)}</label>
							</li>
						{/foreach}
					</ul>
				</div>
				<div class="clearFix"></div>
			</div>

			{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}