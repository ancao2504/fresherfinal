{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div class="modal-dialog modal-md modal-content modal-edit-report-menu-item">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

		<form name="edit-report-menu-item" class="form-horizontal">
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
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_MODAL_EDIT_REPORT_MENU_ITEM_REPORT', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8">
								<select name="report_id" class="report-id select2" data-rule-required="true" style="width: 100%">
									<option value="">{vtranslate('LBL_MODAL_EDIT_REPORT_MENU_ITEM_REPORT_PLACEHOLDER', $QUALIFIED_MODULE)}</option>

									{foreach key=KEY item=REPORT from=$ALL_REPORTS}
										<option value="{$REPORT.reportid}" {if $REPORT.reportid == $MENU_ITEM_INFO.value.report_id}selected{/if}>{$REPORT.reportname}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('Icon', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8">
								<div class="btn-group dropup">
									<button type="button" class="btn btn-default iconpicker-component"><i class="fal {if $MENU_ITEM_INFO.value.icon}{$MENU_ITEM_INFO.value.icon}{else}fa-chart-bar{/if}"></i></button>
									<button type="button" class="btn btn-default iconpicker-trigger dropdown-toggle" data-selected="{if $MENU_ITEM_INFO.value.icon}{$MENU_ITEM_INFO.value.icon}{else}fa-chart-bar{/if}" data-toggle="dropdown">
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