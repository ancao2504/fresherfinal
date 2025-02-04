{* Added by Hieu Nguyen on 2022-11-28 *}
{* Modified by Vu Mai on 2023-02-15 to restyle according to mockup *}

{strip}
	<table id="tbl-data-statistics" class="table">
		<tbody>
			<tr>
				<th class="text-right" style="width:70%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_DUPLICATED_CUSTOMERS_BY_MOBILE_NUMBER', 'Campaigns')}</th>
				<th class="text-right" style="width:30%">
					<span id="duplicate-mobile-count"><a {if $RESULT.duplicate_mobile_count > 0}href="index.php?module=Campaigns&action=TelesalesAjax&mode=exportDuplicatedCustomersByMobileNumber&mkt_list_ids={join(',', $MKT_LIST_IDS)}"{/if} target="_blank">{$RESULT.duplicate_mobile_count}</a></span>&nbsp;
					
					{if $RESULT.duplicate_mobile_count > 0}
						<span id="download-duplicate-mobile-list" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_EXPORT_LISTS_DUPLICATED_CUSTOMERS_BY_MOBILE_NUMBER_TOOLTIP', 'Campaigns')}">
							<a href="index.php?module=Campaigns&action=TelesalesAjax&mode=exportDuplicatedCustomersByMobileNumber&mkt_list_ids={join(',', $MKT_LIST_IDS)}" target="_blank"><i class="far fa-file-arrow-down"></i></a>
						</span>
					{/if}

					<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_DUPLICATED_CUSTOMERS_BY_MOBILE_NUMBER_TOOLTIP', 'Campaigns')}">
						<i class="far fa-info-circle"></i>
					</span>
				</th>
			</tr>
			<tr>
				<th class="text-right" style="width:70%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_CUSTOMERS_HAVE_EMPTY_MOBILE_NUMBER', 'Campaigns')}</th>
				<th class="text-right" style="width:30%">
					<span id="empty-mobile-count">{$RESULT.empty_mobile_count}</span>&nbsp;
					<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LISTS_CUSTOMERS_HAVE_EMPTY_MOBILE_NUMBER_TOOLTIP', 'Campaigns')}">
						<i class="far fa-info-circle"></i>
					</span>
				</th>
			</tr>
			<tr>
				<th class="text-right" style="width:70%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS', 'Campaigns')}</th>
				<th class="text-right" style="width:30%">
					<span id="distributable-count">{$RESULT.distributable_count}</span>&nbsp;
					<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS_TOOLTIP', 'Campaigns')}">
						<i class="far fa-info-circle"></i>
					</span>
				</th>
			</tr>
		</tbody>
	</table>
{strip}