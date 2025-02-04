{*
	Name: ZaloAdsFormMappingFields.tpl
	Author: Phu Vo
	Date: 2021.11.09
*}

{strip}
	{if $RECORD}
		{assign var="RECORD_ID" value=$RECORD->getId()}
	{/if}
	
	{if !$FORM_MAPPING_FIELDS}
		{assign var="FORM_MAPPING_FIELDS" value=$RECORD->getFormMappingFields()}
	{/if}

	{assign var="MODULES_FIELDS" value=CPZaloAdsForm_Data_Helper::getModulesFields(['CPTarget', 'Leads', 'Contacts'])}

	<div class="mapping-fields-container">
		<table class="table mapping-fields-table">
			<thead>
				<tr>
					<th class="zalo-field">
						<span class="header-content">{vtranslate('LBL_FORM_MAPPING_FIELDS', $MODULE)}</span>
					</th>
					<th class="target-field">
						<span class="header-content">{vtranslate('LBL_FORM_CPTARGET_FIELDS', $MODULE)}</span>
					</th>
					<th class="lead-field">
						<span class="header-content">{vtranslate('LBL_FORM_LEADS_FIELDS', $MODULE)}</span>
					</th>
					<th class="contact-field">
						<span class="header-content">{vtranslate('LBL_FORM_CONTACTS_FIELDS', $MODULE)}</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=array_values($FORM_MAPPING_FIELDS) item=FORM_FIELD key=INDEX}
					<tr>
						<td class="zalo-field">
							<span class="body-content">{$FORM_FIELD.title}</span>
						</td>
						<td class="target-field">
							{if $FORM_FIELD.cptarget_field}
								<span class="body-content">
									{vtranslate($MODULES_FIELDS['CPTarget'][$FORM_FIELD.cptarget_field]->label, 'CPTarget')}
								</span>
							{else}
								<span class="body-content">--</span>
							{/if}
						</td>
						<td class="lead-field">
							{if $FORM_FIELD.lead_field}
								<span class="body-content">
									{vtranslate($MODULES_FIELDS['Leads'][$FORM_FIELD.lead_field]->label, 'Leads')}
								</span>
							{else}
								<span class="body-content">--</span>
							{/if}
						</td>
						<td class="contact-field">
							{if $FORM_FIELD.contact_field}
								<span class="body-content">
									{vtranslate($MODULES_FIELDS['Contacts'][$FORM_FIELD.contact_field]->label, 'Contacts')}
								</span>
							{else}
								<span class="body-content">--</span>
							{/if}
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
	</div>
{/strip}