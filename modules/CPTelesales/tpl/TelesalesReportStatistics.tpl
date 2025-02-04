{* Added by Vu Mai on 2022-11-28 to display report statistic *}

{strip}
	<div id="report-statistics" class="box shadowed">
		<div class="box-body">
			<div id="statistics-items" class="flex">
				<div class="statistics-item-wrapper">
					<div id="customer-total" class="increase statistics-item">
						<div class="name">
							{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CUSTOMER_TOTAL', $MODULE)}
							<span class="info-tooltip ml-2" data-toggle="tooltip" title="{{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_CUSTOMER_TOTAL_TOOLTIP', $MODULE)}}">
								<i class="far fa-info-circle"></i>
							</span>
						</div>
						<div class="current-value mt-1">{if $CUSTOMER_TOTAL}{$CUSTOMER_TOTAL}{else}0{/if}</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					<div id="outbound-call-total" class="increase statistics-item">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_OUTBOUND_CALL_TOTAL', $MODULE)}</div>
						<div class="current-value mt-1">{if $OUTBOUND_CALL_TOTAL}{$OUTBOUND_CALL_TOTAL}{else}0{/if}</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					<div id="outbound-call-minutes-total" class="increase statistics-item">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_OUTBOUND_CALL_MINUTES_TOTAL', $MODULE)}</div>
						<div class="current-value mt-1">{if $OUTBOUND_CALL_MINUTES_TOTAL}{$OUTBOUND_CALL_MINUTES_TOTAL}{else}0{/if}</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					<div id="sale-total" class="increase statistics-item">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_SALE_TOTAL', $MODULE)}</div>
						<div class="current-value mt-1">
							{if $SALE_TOTAL}
								{Vtiger_Currency_UIType::transformDisplayValue($SALE_TOTAL, null, false)}
							{else}
								0
							{/if}
						</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					<div id="orders-total" class="increase statistics-item">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDERS_TOTAL', $MODULE)}</div>
						<div class="current-value mt-1">{if $ORDERS_TOTAL}{$ORDERS_TOTAL}{else}0{/if}</div>
					</div>
				</div>
				<div class="statistics-item-wrapper">
					<div id="order-confirmed-rate" class="increase statistics-item">
						<div class="name">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_CALL_STATISTICS_ORDER_CONFIRMED_RATE', $MODULE)}</div>
						<div class="current-value mt-1">{if $ORDERS_CONFIRMED_RATE}{$ORDERS_CONFIRMED_RATE}%{else}0%{/if}</div>
					</div>
				</div>
			</div>
			<div id="statistics-filter-wrapper">
				<span>{vtranslate('LBL_TELESALES_CAMPAIGN_REPORT_EMPLOYEE', $MODULE)}</span>
				<select class="statistics-filter dropdown-filter ml-2">
					{foreach item=USER key=KEY from=$SELECTED_USERS}
						{assign var=USER_NAME value=explode(" (", $USER.name)}

						<option value="{$USER.id}" {if $USER.id == $USER_FILTER}selected{/if}>{$USER_NAME[0]}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
{/strip}