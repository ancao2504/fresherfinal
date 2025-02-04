{* Added by Hieu Nguyen on 2022-06-14. Inherited from ConfigEditorEdit.tpl *}

{strip}
<link rel="stylesheet" href="{vresource_url('layouts/v7/modules/Settings/Vtiger/resources/ConfigEditor.css')}">
</link>
<div id="editViewContent" class="editViewPageDiv">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="contents">
			<form id="ConfigEditorForm" class="form-horizontal" method="POST">
				{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}

				<div>
					<h4>{vtranslate('LBL_CONFIG_EDITOR', $QUALIFIED_MODULE)}</h4>
				</div>
				<hr />
				<br />

				<div class="detailViewInfo">
					{foreach key=FIELD_NAME item=FIELD_INFO from=$MODEL->getEditableFields()}
					{if $FIELD_INFO.fieldType == 'separator'}
					<div class="row form-group">
						<div class="col-lg-4"></div>
						<div class="col-lg-2">
							<hr />
						</div>
					</div>
					{else}
					<div class="row form-group">
						<div class="col-lg-4 control-label fieldLabel">
							<label>{vtranslate($FIELD_INFO.label, $QUALIFIED_MODULE)}</label>
						</div>
						<div class="{$WIDTHTYPE} col-lg-4 fieldValue">
							{if $FIELD_INFO.fieldType == 'module_list'}
							<select name="{$FIELD_NAME}"
								class="inputElement select2 col-lg-11 {$FIELD_INFO.customClass}"
								data-type={$FIELD_INFO.fieldType}>
								{foreach key=VALUE item=LABEL from=$MODEL->getPicklistValues($FIELD_NAME)}
								<option value="{$VALUE}" {if $LABEL==$FIELD_INFO.value}selected{/if}>{vtranslate($LABEL,
									$LABEL)}</option>
								{/foreach}
							</select>
							{elseif $FIELD_INFO.fieldType == 'picklist'}
							<select name="{$FIELD_NAME}"
								class="inputElement select2 col-lg-11 {$FIELD_INFO.customClass}"
								data-type={$FIELD_INFO.fieldType}>
								{foreach key=VALUE item=LABEL from=$MODEL->getPicklistValues($FIELD_NAME)}
								{if $FIELD_INFO.fieldType}
								<option {if $LABEL==$FIELD_INFO.value}selected{/if}>{vtranslate($LABEL,
									$QUALIFIED_MODULE)}</option>
								{else}
								<option value="{$VALUE}" {if $LABEL==$FIELD_INFO.value}selected{/if}>{vtranslate($LABEL,
									$LABEL)}</option>
								{/if}
								{/foreach}
							</select>
							{elseif $FIELD_INFO.fieldType == 'multi_picklist'}
							<select name="{$FIELD_NAME}"
								class="inputElement select2 col-lg-11 {$FIELD_INFO.customClass}" multiple
								data-type={$FIELD_INFO.fieldType}>
								{foreach key=VALUE item=LABEL from=$MODEL->getPicklistValues($FIELD_NAME)}
								<option value="{$VALUE}" {if in_array($LABEL, $FIELD_INFO.value)}selected{/if}>
									{vtranslate($LABEL, $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
							{elseif $FIELD_INFO.fieldType == 'custom_picklist'}
							<select name="{$FIELD_NAME}"
								class="inputElement select2 col-lg-11 {$FIELD_INFO.customClass}" multiple
								data-type={$FIELD_INFO.fieldType}>
								{foreach key=INDEX item=VALUE from=$FIELD_INFO.values}
								<option value="{$VALUE}" selected>{$VALUE}</option>
								{/foreach}
							</select>
							{else}
							<div class="input-group">
								<input type="text" name="{$FIELD_NAME}" class="inputElement {$FIELD_INFO.customClass}"
									data-type={$FIELD_INFO.fieldType} {if
									$FIELD_INFO.validation}{$FIELD_INFO.validation}{/if} value="{$FIELD_INFO.value}" />

								{if $FIELD_INFO.valueUnit}
								<div class="input-group-addon">{$FIELD_INFO.valueUnit}</div>
								{/if}
							</div>
							{/if}
						</div>
					</div>
					{/if}
					{/foreach}
				</div>

				<div class="modal-overlay-footer clearfix">
					<div class="row clearfix">
						<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
							<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE',
								$MODULE)}</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
{/strip}