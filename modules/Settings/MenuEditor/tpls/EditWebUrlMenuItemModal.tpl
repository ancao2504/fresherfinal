{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div class="modal-dialog modal-md modal-content modal-edit-web-url-menu-item">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

		<form name="edit-web-url-menu-item" class="form-horizontal">
			<input type="hidden" name="main_menu_id" value="{$MAIN_MENU_ID}" />
			<input type="hidden" name="menu_group_id" value="{$MENU_GROUP_ID}" />
			<input type="hidden" name="menu_item_id" value="{$MENU_ITEM_INFO.id}" />

			<div class="form-content fancyScrollbar padding20">
				<table class="table no-border fieldBlockContainer">
					<tbody>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_VIETNAMESE_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="name_vn" value="{$MENU_ITEM_INFO.value.name_vn}" data-rule-required="true" class="inputElement" /></td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_ENGLISH_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="name_en" value="{$MENU_ITEM_INFO.value.name_en}" data-rule-required="true" class="inputElement" /></td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('URL', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="url" value="{$MENU_ITEM_INFO.value.url}" data-rule-required="true" class="inputElement" placeholder="https://www.cloudgo.vn" /></td> {*Modified by VU Mai on 2023-03-17*}
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_MODAL_EDIT_WEB_URL_MENU_ITEM_OPEN_IN_NEW_TAB', $QUALIFIED_MODULE)}</td>
							<td class="fieldValue col-lg-8">
								<input type="checkbox" name="open_in_new_tab" class="bootstrap-switch" {if $MENU_ITEM_INFO.value.open_in_new_tab == 'true'}checked{/if}>&nbsp;&nbsp;
								<span data-toggle="tooltip" title="{vtranslate('LBL_MODAL_EDIT_WEB_URL_MENU_ITEM_OPEN_IN_NEW_TAB_TOOLTIP', $QUALIFIED_MODULE)}"><i class="fal fa-info-circle"></i></span>
							</td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('Icon', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8">
								<div class="btn-group dropup">
									<button type="button" class="btn btn-default iconpicker-component"><i class="fal {if $MENU_ITEM_INFO.value.icon}{$MENU_ITEM_INFO.value.icon}{else}fa-link{/if}"></i></button>
									<button type="button" class="btn btn-default iconpicker-trigger dropdown-toggle" data-selected="{if $MENU_ITEM_INFO.value.icon}{$MENU_ITEM_INFO.value.icon}{else}fa-link{/if}" data-toggle="dropdown">
										<span class="caret"></span>
									</button>
									<div class="dropdown-menu"></div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}