{* Added by Vu Mai on 2022-11-28 to display telesales summary by employee table *}

{strip}
	<div id="telesales-summary" class="box shadowed">
		<div class="box-header">{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_SUMMARY_OF_TELESALES_EFFICIENCY_BY_EMPLOYEEE', $MODULE)}</div>
		<div class="box-body mt-3 ml-3 mr-3">
			<table class="table">
				<thead>
					<tr>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_EMPLOYEE', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CUSTOMER_AMOUNT', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CALL_AMOUNT', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CALL_MINUTES_AMOUNT', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_AVERAGE_CALL_TIME', $MODULE)}
							<span class="info-tooltip ml-2" data-toggle="tooltip" title="{{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_AVERAGE_CALL_TIME_TOOLTIP', $MODULE)}}">
								<i class="far fa-info-circle"></i>
							</span>
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_AVERAGE_NUMBER_OF_CALLS_PER_DAY', $MODULE)}
							<span class="info-tooltip ml-2" data-toggle="tooltip" title="{{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_AVERAGE_NUMBER_OF_CALLS_PER_DAY_TOOLTIP', $MODULE)}}">
								<i class="far fa-info-circle"></i>
							</span>
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_SALE_TOTAL', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDERS_TOTAL', $MODULE)}
						</th>
						<th class="text-center">
							{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDER_CONFIRMED_RATE', $MODULE)}
						</th>
					</tr>
				<thead>
				<tbody>
					{foreach key=ID item=EMPLOYEE from=$TELESALES_SUMMARY_BY_EMPLOYEE}
						{assign var=USER_NAME value=explode(" (", $EMPLOYEE.name)}

						<tr>
							<td class="text-center">
								{$USER_NAME[0]}
							</td>
							<td class="text-center">
								{$EMPLOYEE.total}
							</td>
							<td class="text-center">
								{$EMPLOYEE.outbound_call_total}
							</td>
							<td class="text-center">
								{$EMPLOYEE.outbound_call_minutes_total}
							</td>
							<td class="text-center">
								{$EMPLOYEE.average_call_time}
							</td>
							<td class="text-center">
								{$EMPLOYEE.average_call_per_day}
							</td>
							<td class="text-center">
								{if $EMPLOYEE.sale_total}
									{Vtiger_Currency_UIType::transformDisplayValue($EMPLOYEE.sale_total, null, false)}
								{else}
									0
								{/if}
							</td>
							<td class="text-center">
								{$EMPLOYEE.orders_total}
							</td>
							<td class="text-center">
								{$EMPLOYEE.orders_confirmed_rate}%
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}