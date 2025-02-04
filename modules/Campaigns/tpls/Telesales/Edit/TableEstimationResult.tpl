{* Added by Hieu Nguyen on 2022-12-05 *}
{* Modified by Vu Mai on 2022 on 2022-12-09 to restyle according to mockup *}

{strip}
	<div id="estimation-statistics">
		<div>
			{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS', 'Campaigns')}:&nbsp;
			<span class="bold ml-2">{number_format($RESULT.summary.distributable_count)}</span>&nbsp;
			<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTABLE_CUSOTMERS_TOOLTIP', 'Campaigns')}">
				<i class="far fa-info-circle"></i>
			</span>
		</div>
		<div>
			{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_ESTIMATION_TOTAL_SUCCESS_DISTRIBUTION', 'Campaigns')}:&nbsp;
			<span class="bold greenColor ml-2">{number_format($RESULT.summary.distributed_count)}</span>
		</div>
		<div>
			{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_ESTIMATION_TOTAL_SKIP_DISTRIBUTION', 'Campaigns')}:&nbsp;
			<span id="total-skipped" class="bold redColor ml-2">{number_format($RESULT.summary.skipped_count)}</span>&nbsp;
			<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_PANEL_ESTIMATION_TOTAL_SKIP_DISTRIBUTION_TOOLTIP', 'Campaigns')}">
				<i class="far fa-info-circle"></i>
			</span>
		</div>
	</div>

	<table id="tbl-estimation-result" class="table table-border-custom">
		<thead>
			<tr>
				<th rowspan="2" style="width:40%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_EMPLOYEE_NAME', $MODULE_NAME)}</th>
				<th colspan="3" style="width:60%" class="text-center">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DISTRIBUTION_CUSTOMERS_COUNT', $MODULE_NAME)}</th>
			</tr>
			<tr>
				<th class="text-right no-wrap" style="border-left: 1px solid var(--black-5) !important;">
					{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_TOTAL_CURRENT_CUSTOMERS', $MODULE_NAME)}&nbsp;
					<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_TOTAL_CURRENT_CUSTOMERS_TOOLTIP', $MODULE_NAME)}">
						<i class="far fa-info-circle"></i>
					</span>
				</th>
				<th class="text-right">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_DISTRIBUTE_NEW_CUSTOMERS', $MODULE_NAME)}</th>
				<th class="text-right">{vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_DISTRIBUTE_DATA_TOTAL_FINAL_CUSTOMERS', $MODULE_NAME)}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$RESULT.detail_by_user key=USER_ID item=INFO}
				<tr>
					<td>{$INFO.full_name} ({$INFO.email})</td>
					<td class="text-right">{number_format($INFO.current_data_count)}</td>
					<td class="text-right">{number_format($INFO.new_data_count)}</td>
					<td class="text-right">{number_format($INFO.final_data_count)}</td>
				</tr>
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<th class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL', 'Campaigns')}</th>
				<th class="text-right">{number_format($FOOTER_TOTAL.current_data_count)}</th>
				<th class="text-right">
					<span title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTED_PER_TOTAL_DISTRIBUTABLE_TOOLTIP', 'Campaigns')}" data-toggle="tooltip">
						<span>{number_format($RESULT.summary.distributed_count)}/{number_format($RESULT.summary.distributable_count)}</span>
					</span>
				</th>
				<th class="text-right">{number_format($FOOTER_TOTAL.final_data_count)}</th>
			</tr>
		</tfoot>
	</table>
{strip}