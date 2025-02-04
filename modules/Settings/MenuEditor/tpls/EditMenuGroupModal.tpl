{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div class="modal-dialog modal-md modal-content modal-edit-menu-group">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

		<form name="edit-menu-group" class="form-horizontal">
			<input type="hidden" name="main_menu_id" value="{$MAIN_MENU_ID}" />
			<input type="hidden" name="menu_group_id" value="{$MENU_GROUP_INFO.id}" />

			<div class="form-content fancyScrollbar padding20">
				<table class="table no-border fieldBlockContainer">
					<tbody>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_VIETNAMESE_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="name_vn" value="{$MENU_GROUP_INFO.name_vn}" data-rule-required="true" class="inputElement" /></td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_ENGLISH_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="name_en" value="{$MENU_GROUP_INFO.name_en}" data-rule-required="true" class="inputElement" /></td>
						</tr>
					</tbody>
				</table>
			</div>

			{include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}