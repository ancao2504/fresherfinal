{* Added by Hieu Nguyen on 2022-11-09 *}
{* Modified by Vu Mai on 2022 on 2022-12-08 to restyle according to mockup *}

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
			<span class="bold greenColor">{number_format($RESULT.summary.distributed_count)}</span>
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
				<th style="width:60%">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_EMPLOYEE_NAME', 'Campaigns')}</th>
				<th style="width:40%" class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_CUSTOMERS_COUNT', 'Campaigns')}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$RESULT.detail_by_user key=USER_ID item=INFO}
				<tr>
					<td>{$INFO.full_name} ({$INFO.email})</td>
					<td class="text-right">{number_format($INFO.new_data_count)}</td>
				</tr>
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<th class="text-right">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL', 'Campaigns')}</th>
				<th class="text-right">
					<span title="{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_TOTAL_DISTRIBUTED_PER_TOTAL_DISTRIBUTABLE_TOOLTIP', 'Campaigns')}" data-toggle="tooltip">
						<span>{number_format($RESULT.summary.distributed_count)}/{number_format($RESULT.summary.distributable_count)}</span>
					</span>
				</th>
			</tr>
		</tfoot>
	</table>
{strip}