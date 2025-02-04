{* Created By Vu Mai on 2022-10-24 to render telesales customer status view *}

{strip}
	<div id="customer-status-container" class="flex fancyScrollbar">
		<div class="customer-status all" data-status="all">
			<div class="status">All</div>&nbsp;
			<div class="amount" data-amount="{$TOTAL}">({$TOTAL})</div>
		</div>

		{foreach from=$STATUS_LIST item=STATUS key=KEY}
			<div class="customer-status" data-status="{$STATUS.status}" data-field-name="{$STATUS.field_name}">
				<div class="status">{$STATUS.label}</div>&nbsp;
				<div class="amount" data-amount="{$STATUS.amount}">({$STATUS.amount})</div>
			</div>
		{/foreach}
	</div>
{/strip}	