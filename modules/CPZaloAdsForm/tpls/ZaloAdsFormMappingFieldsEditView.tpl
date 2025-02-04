{*
	Name: ZaloAdsFormMappingFields.tpl
	Author: Phu Vo
	Date: 2021.11.09
*}

{strip}
	{if !$FORM_MAPPING_FIELDS}
		{assign var="FORM_MAPPING_FIELDS" value=$RECORD->getFormMappingFields()}
	{/if}

	{assign var="MODULES_FIELDS" value=CPZaloAdsForm_Data_Helper::getModulesFields(['CPTarget', 'Leads', 'Contacts'])}

	<div class="mapping-fields-container">
		<table class="table mapping-fields-table">
			<thead>
				<tr>
					<th class="zalo-field">
						<span class="header-content">
							<a href="javascript:void(0)" class="mapping-fields-refresh"><i class="far fa-sync"></i></a>
						</span>
						<span class="header-content">{vtranslate('LBL_FORM_MAPPING_FIELDS', $MODULE)}</span>
					</th>
					<th class="target-field">
						{$requiredFields = ['firstname' => vtranslate('LBL_FIRSTNAME', 'CPTarget')]}
						<input tyep="text" name="validator" data-module="CPTarget" data-rule-mapping-required='{ZEND_JSON::encode($requiredFields)}' class="hidden-input">
						<span class="header-content">{vtranslate('LBL_FORM_CPTARGET_FIELDS', $MODULE)}</span>
						<span class="header-content">
							<span data-toggle="tooltip" title="{vtranslate('LBL_FORM_CPTARGET_FIELDS_DESCRIPTION', $MODULE)}"><i class="far fa-info-circle"></i></span>
						</span>
					</th>
					<th class="lead-field">
						{$requiredFields = ['firstname' => vtranslate('First Name', 'Leads'), 'mobile' => vtranslate('Mobile', 'Leads')]}
						<input tyep="text" name="validator" data-module="Leads" data-rule-mapping-required='{ZEND_JSON::encode($requiredFields)}' class="hidden-input">
						<span class="header-content">{vtranslate('LBL_FORM_LEADS_FIELDS', $MODULE)}</span>
						<span class="header-content">
							<span data-toggle="tooltip" title="{vtranslate('LBL_FORM_LEADS_FIELDS_DESCRIPTION', $MODULE)}"><i class="far fa-info-circle"></i></span>
						</span>
					</th>
					<th class="contact-field">
						{$requiredFields = ['firstname' => vtranslate('First Name', 'Contacts'), 'mobile' => vtranslate('Mobile', 'Contacts')]}
						<input tyep="text" name="validator" data-module="Contacts" data-rule-mapping-required='{ZEND_JSON::encode($requiredFields)}' class="hidden-input">
						<span class="header-content">{vtranslate('LBL_FORM_CONTACTS_FIELDS', $MODULE)}</span>
						<span class="header-content">
							<span data-toggle="tooltip" title="{vtranslate('LBL_FORM_CONTACTS_FIELDS_DESCRIPTION', $MODULE)}"><i class="far fa-info-circle"></i></span>
						</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=array_values($FORM_MAPPING_FIELDS) item=FORM_FIELD key=INDEX}
					<tr>
						<td class="zalo-field">
							<input type="hidden" name="mapping_fields[{$INDEX}][question_id]" value="{$FORM_FIELD.question_id}">
							<input type="hidden" name="mapping_fields[{$INDEX}][title]" value="{$FORM_FIELD.title}">
							<span class="body-content">{$FORM_FIELD.title}</span>
						</td>
						<td class="target-field mapping-field" data-module="CPTarget">
							<select name="mapping_fields[{$INDEX}][cptarget_field]" class="select2 inputElement">
								<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
								{foreach from=$MODULES_FIELDS['CPTarget'] key=FIELD_NAME item=FIELD_MODEL}
									{if $FIELD_MODEL->isMandatory()}
										<option value="{$FIELD_NAME}" {if $FORM_FIELD.cptarget_field == $FIELD_NAME}selected{/if}>{vtranslate($FIELD_MODEL->label, 'CPTarget')} (*)</option>
									{else}
										<option value="{$FIELD_NAME}" {if $FORM_FIELD.cptarget_field == $FIELD_NAME}selected{/if}>{vtranslate($FIELD_MODEL->label, 'CPTarget')}</option>
									{/if}
								{/foreach}
							</select>
						</td>
						<td class="lead-field mapping-field" data-module="Leads">
							<select name="mapping_fields[{$INDEX}][lead_field]" class="select2 inputElement">
								<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
								{foreach from=$MODULES_FIELDS['Leads'] key=FIELD_NAME item=FIELD_MODEL}
									{if $FIELD_MODEL->isMandatory()}
										<option value="{$FIELD_NAME}" {if $FORM_FIELD.lead_field == $FIELD_NAME}selected{/if}>{vtranslate($FIELD_MODEL->label, 'Leads')} (*)</option>
									{else}
										<option value="{$FIELD_NAME}" {if $FORM_FIELD.lead_field == $FIELD_NAME}selected{/if}>{vtranslate($FIELD_MODEL->label, 'Leads')}</option>
									{/if}
								{/foreach}
							</select>
						</td>
						<td class="contact-field mapping-field" data-module="Contacts">
							<select name="mapping_fields[{$INDEX}][contact_field]" class="select2 inputElement">
								<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
								{foreach from=$MODULES_FIELDS['Contacts'] key=FIELD_NAME item=FIELD_MODEL}
									{if $FIELD_MODEL->isMandatory()}
										<option value="{$FIELD_NAME}" {if $FORM_FIELD.contact_field == $FIELD_NAME}selected{/if}>{vtranslate($FIELD_MODEL->label, 'Contacts')} (*)</option>
									{else}
										<option value="{$FIELD_NAME}" {if $FORM_FIELD.contact_field == $FIELD_NAME}selected{/if}>{vtranslate($FIELD_MODEL->label, 'Contacts')}</option>
									{/if}
								{/foreach}
							</select>
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan="4">
							<span class="no-data">{vtranslate('LBL_FORM_NO_MAPPING_FIELDS', $MODULE)}</span>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>

		<div class="warnning-container redColor">
			<p>{vtranslate('LBL_FORM_WARNING', $MODULE)}
			<ul>
				<li>{vtranslate('LBL_FORM_WARNING_SYNC_EXPLAIN', $MODULE)}</li>
				<li>{vtranslate('LBL_FORM_WARNING_FROM_DROPDOWN_EXPLAIN', $MODULE)}</li>
			</ul>
		</div>
	</div>
{/strip}