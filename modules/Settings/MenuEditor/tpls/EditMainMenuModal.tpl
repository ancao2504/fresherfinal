{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<div class="modal-dialog modal-md modal-content modal-main-menu">
		{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$MODAL_TITLE}

		<form name="main-menu" class="form-horizontal">
			<input type="hidden" name="main_menu_id" value="{$MAIN_MENU_INFO.id}" />

			<div class="form-content fancyScrollbar padding20">
				<table class="table no-border fieldBlockContainer">
					<tbody>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_VIETNAMESE_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="name_vn" value="{$MAIN_MENU_INFO.name_vn}" data-rule-required="true" class="inputElement" /></td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_ENGLISH_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8"><input type="text" name="name_en" value="{$MAIN_MENU_INFO.name_en}" data-rule-required="true" class="inputElement" /></td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8">
								<input name="color" value="{$MAIN_MENU_INFO.color}" data-rule-required="true" />
							</td>
						</tr>
						<tr>
							<td class="fieldLabel col-lg-4">{vtranslate('Icon', $QUALIFIED_MODULE)} <span class="redColor">*</span></td>
							<td class="fieldValue col-lg-8">
								<div class="btn-group dropup">
									<button type="button" class="btn btn-default iconpicker-component"><i class="fal {if $MAIN_MENU_INFO.icon}{$MAIN_MENU_INFO.icon}{else}fa-chart-bar{/if}"></i></button>
									<button type="button" class="btn btn-default iconpicker-trigger dropdown-toggle" data-selected="{if $MAIN_MENU_INFO.icon}{$MAIN_MENU_INFO.icon}{else}fa-chart-bar{/if}" data-toggle="dropdown">
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