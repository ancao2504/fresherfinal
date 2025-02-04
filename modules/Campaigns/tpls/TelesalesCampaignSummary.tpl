{* Added by Vu Mai on 2022-11-30 for telesales campaign summary template *}

{strip}
	<div class="summaryView">
		<div class="summaryViewHeader">
			<h4 class="display-inline-block">{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_TELESALES_CAMPAIGN_SUMMARY', $MODULE)}</h4>
		</div>
		<div class="summary-view-content">
			<div class="customer-statistics">
				<h5 class="ml-3">{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CUSTOMER_STATISTICS', $MODULE)}</h5>
				<table class="table ml-3 mt-3">
					<thead>
						<tr>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CUSTOMER_TOTAL_COLUMN', 'CPTelesales')}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CUSTOMER_STATISTICS_NOT_DISTRIBUTED_YET', $MODULE)}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CUSTOMER_STATISTICS_PROCESSING', $MODULE)}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CUSTOMER_STATISTICS_FAILED', $MODULE)}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CUSTOMER_STATISTICS_SUCCESS', $MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{$CUSTOMER_STATISTICS.customer_total}</td>
							<td>{$CUSTOMER_STATISTICS.customer_not_distributed_yet}</td>
							<td>{$CUSTOMER_STATISTICS.customer_processing}</td>
							<td>{$CUSTOMER_STATISTICS.customer_failed}</td>
							<td>{$CUSTOMER_STATISTICS.customer_success}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="call-statistics">
				<h5 class="ml-3">{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CALL_STATISTICS', $MODULE)}</h5>
				<table class="table ml-3 mt-3">
					<thead>
						<tr>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CALL_STATISTICS_CALL_TOTAL', $MODULE)}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CALL_STATISTICS_CALL_MINUTES_TOTAL', $MODULE)}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_AVERAGE_CALL_TIME', 'CPTelesales')}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_AVERAGE_NUMBER_OF_CALLS_PER_DAY', 'CPTelesales')}</th>
							<th>{vtranslate('LBL_TELESALES_CAMPAIGN_SUMMARY_VIEW_CALL_STATISTICS_CALL_ANSWERED_RATE', $MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{$CALL_STATISTICS.outbound_call_total}</td>
							<td>{$CALL_STATISTICS.outbound_call_minutes_total}</td>
							<td>{$CALL_STATISTICS.average_call_time}</td>
							<td>{$CALL_STATISTICS.average_call_per_day}</td>
							<td>{$CALL_STATISTICS.call_answered_rate}%</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}