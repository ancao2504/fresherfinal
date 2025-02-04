{*
    Author: Phu Vo
    Date: 2019.05.13
    Last Update: 2019.05.13
    Purpose: Template for Inventory modules export PDF
*}

{if $LINEITEM_FIELDS['discount_amount']}
    {assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['discount_amount']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['discount_percent']}
    {assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['discount_percent']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnS_H_Percent']}
    {assign var=SH_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnS_H_Percent']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountAmount']}
    {assign var=DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountAmount']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountPercent']}
    {assign var=DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountPercent']->isViewable()}
{/if}

<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<head>
		{literal}
			<style type="text/css">
				html {
					background-color: white;
                    font-size: 15pt;
				}

				table {
					width: 100%;
                    margin: auto;
					border-collapse: collapse;
					page-break-after: always;
					font-size: 15pt;
				}

                table, tr, td, th, tbody, thead, tfoot {
                    page-break-inside: avoid !important;
                    page-break-after: auto !important;
                }

				table th, table td {
					padding: 1px;
				}

				table.content tr th, table.content tr td {
					border: 1px solid #000000;
				}

				.text-right {
					text-align: right !important;
				}

				.text-center {
					text-align: center !important;
				}

				.text-left {
					text-align: left !important;
				}

			</style>
		{/literal}
	</head>

    <table>
        <tr>
            <td colspan="7">
                {if !empty($COMPANY_MODEL->getLogoPath())}<img style="margin-bottom: 35px;" src="{$COMPANY_MODEL->getLogoPath()}" width="370px"/>{/if}
            </td>
            <td style="text-align: right; font-size: 15pt;">
                {if !empty($COMPANY_MODEL->get('organizationname'))}<b>{$COMPANY_MODEL->get('organizationname')}</b><br>{/if}
                {if !empty($COMPANY_MODEL->get('address'))}{$COMPANY_MODEL->get('address')}<br>{/if}
                {if !empty($COMPANY_MODEL->get('city'))}{$COMPANY_MODEL->get('city')}, {$COMPANY_MODEL->get('state')}, {$COMPANY_MODEL->get('country')}<br>{/if}
                {if !empty($COMPANY_MODEL->get('phone'))}{vtranslate('Phone', $MODULE_NAME)}: {$COMPANY_MODEL->get('phone')}<br>{/if}
                {if !empty($COMPANY_MODEL->get('fax'))}{vtranslate('Fax: ', $MODULE_NAME)} {$COMPANY_MODEL->get('fax')}<br>{/if}
                {if !empty($COMPANY_MODEL->get('website'))}{vtranslate('Website: ', $MODULE_NAME)} {$COMPANY_MODEL->get('website')}<br>{/if}
            </td>
        </tr>
    </table>

    <table style="margin-top: 40px;font-size: 15pt;">
		<tbody>
			<tr>
				<td style="font-weight: bold; font-size: 24pt; text-transform: uppercase;" class="text-center" colspan="8">{vtranslate('LBL_PDF_TITLE', $MODULE_NAME)}<br><br>
                </td>
                
			</tr>
            
            {if $MODULE_NAME neq 'PurchaseOrder'} 
                {if !empty($RECORD_MODEL->get('contact_id'))}
                    <tr>
                        <td colspan="8">
                            <b>{vtranslate('LBL_INVENTORY_PDF_DEAR_PERSON', $MODULE_NAME)}: {getParentName($RECORD_MODEL->get('contact_id'))}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            <b>{vtranslate('SINGLE_Accounts', $MODULE_NAME)}: {getParentName($RECORD_MODEL->get('account_id'))}</b>
                        </td>
                    </tr>
                {else}
                    <tr>
                        <td colspan="8">
                            <b>{vtranslate('LBL_INVENTORY_PDF_DEAR', $MODULE_NAME)}: {getParentName($RECORD_MODEL->get('account_id'))}</b>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td colspan="8">
                        <b>{vtranslate('LBL_INVENTORY_PDF_PURPOSE', $MODULE_NAME)}: {vtranslate("SINGLE_`$MODULE_NAME`", $MODULE_NAME)} - {$RECORD_MODEL->get('label')}</b>
                    </td>
                </tr>
                 <tr>
                    <td colspan="8">
                        <b>{$COMPANY_MODEL->get('organizationname')}</b> {vtranslate('LBL_INVENTORY_PDF_DEALING', $MODULE_NAME)}
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        {$TXT_OPENNING}:
                    </td>
                </tr>
            {else}
                <tr>
                    <td colspan="8">
                        <b>{vtranslate("SINGLE_`$MODULE_NAME`", $MODULE_NAME)} - {$RECORD_MODEL->get('label')}</b>
                    </td>
                </tr>
            
            {/if}
		</tbody>
	</table>

    <table style="margin-top: 20px;" class="content" >
        <thead>
            <tr style="font-weight: bold; background-color: #ccc">
                <th width="50px" style="text-align: center">{vtranslate('Product Code', $MODULE_NAME)}</th>
                <th width="170px" style="text-align: center">{vtranslate('Product Name', $MODULE_NAME)}</th>
                <th width="50px" style="text-align: center">{vtranslate('SHORT_LBL_QUANTITY', $MODULE_NAME)}</th>
                <th width="65px" style="text-align: center">{vtranslate('LBL_LIST_PRICE', $MODULE_NAME)}</th>
                <th width="65px" style="text-align: center">{vtranslate('LBL_DISCOUNT', $MODULE_NAME)}</th>
                <th width="107px" style="text-align: center">{vtranslate('LBL_TOTAL', $MODULE_NAME)}</th>
            </tr>
        </thead>

        <tbody>
            {foreach item=PRODUCT from=$PRODUCTS_DETAILS}
               <tr>
                    <td width="50px">{$PRODUCT.product_code}</td>
                    <td width="170px">
                        {$PRODUCT.product_name} <br/>
                    </td>
                    <td width="50px" style="text-align: right">{$PRODUCT.quantity}</td>
                    <td width="65px" style="text-align: right">
                        {$PRODUCT.price_symbol}
                    </td>
                    <td width="65px" style="text-align: right">
                        {if $PRODUCT.discount > 0} 
                             {$PRODUCT.discount_symbol}
                        {/if}
                    </td>
                        
                    <td width="107px" style="text-align: right">
                        {$PRODUCT.price_after_discount_symbol}
                        {if $TAX_TYPE eq 'individual'} 
                            <br/>
                            {vtranslate('LBL_TAX', $MODULE_NAME)}: 
                            {$PRODUCT.tax_amount_symbol} 
                        {/if}
                    </td>
                </tr>
            {/foreach}
             <tr style="border-bottom:none!important;">
                <td colspan="2"></td>
                <td colspan="2" class="text-left" style="border-right: none !important;">
                    {vtranslate('LBL_ITEMS_TOTAL', $MODULE_NAME)}:<br>
                    {vtranslate('Discount', $MODULE_NAME)}: {if $SUMMARY_DETAILS.discount_final_percent > 0} ({$SUMMARY_DETAILS.discount_final_percent}%){/if}<br>

                    {if ($TAX_TYPE neq 'individual') and ($SUMMARY_DETAILS.tax > 0)} 
                        {vtranslate('Tax', $MODULE_NAME)}: ({$SUMMARY_DETAILS.group_total_tax_percent}%)<br>
                    {/if}

                    {if ($SUMMARY_DETAILS.shipping_charges > 0)} 
                        {vtranslate('Shipping & Handling Charges', $MODULE_NAME)}<br>
                    {/if}

                    {if ($SUMMARY_DETAILS.shipping_tax > 0)}  
                        {vtranslate('Shipping & Handling Tax:', $MODULE_NAME)} ({$SUMMARY_DETAILS.sh_tax_percent}%)<br>
                    {/if}

                    {if ($SUMMARY_DETAILS.adjustment > 0)} 
                        {vtranslate('Adjustment', $MODULE_NAME)}:<br>
                    {/if}

                    {vtranslate('LBL_GRAND_TOTAL', $MODULE_NAME)}:<br>

                    {if $MODULE_NAME == 'Invoice'}   
                        {vtranslate('LBL_RECEIVED', $MODULE_NAME)}<br>
                        {vtranslate('LBL_REMAINING', $MODULE_NAME)}
                    {/if}

                </th>
                <th colspan="2" class="text-right" style="border-left: none !important;">
                    {$SUMMARY_DETAILS.net_total_symbol}<br>
                    {$SUMMARY_DETAILS.discount_symbol}<br>

                    {if ($TAX_TYPE neq 'individual') and ($SUMMARY_DETAILS.tax > 0)} 
                        {$SUMMARY_DETAILS.tax_symbol}<br>
                    {/if}

                    {if ($SUMMARY_DETAILS.shipping_charges > 0)} 
                        {$SUMMARY_DETAILS.shipping_charges_symbol}<br>
                    {/if}

                    {if ($SUMMARY_DETAILS.shipping_tax > 0)}  
                        {$SUMMARY_DETAILS.shipping_tax_symbol}<br>
                    {/if}

                    {if ($SUMMARY_DETAILS.adjustment > 0)} 
                        {$SUMMARY_DETAILS.adjustment_symbol}<br>
                    {/if}

                    {$SUMMARY_DETAILS.grand_total_symbol}<br>

                    {if $MODULE_NAME == 'Invoice'}   
                        {$EXT_SUMMARY_DETAILS.received_symbol}<br>
                        {$EXT_SUMMARY_DETAILS.balance_symbol}
                    {/if}
                </th>
            </tr>
        </tbody>
    </table>

    <br />

    <div style="font-size: 15pt; line-height: 1.2em">{vtranslate('LBL_CONVERT_INT2STRING', 'Vtiger')} : {$GRAND_TOTAL_STRING}</div>

    <br />

   {if !empty($ASSIGNED_USER_MODEL->getId())}
        <div style="font-size: 15pt; line-height: 1.2em">
            <div>{$TXT_ENDING}:</div>
            <div style="font-size: 15pt;">
                <div><i>{trim(getUserFullName($ASSIGNED_USER_MODEL->getId()))}</i></div>
                {if !empty($ASSIGNED_USER_MODEL->get('phone_mobile'))}<div><i>{vtranslate('Phone', $MODULE_NAME)}: {$ASSIGNED_USER_MODEL->get('phone_mobile')}</i></div>{/if}
                {if !empty($ASSIGNED_USER_MODEL->get('email1'))}<div><i>{vtranslate('Email', $MODULE_NAME)}: {$ASSIGNED_USER_MODEL->get('email1')}</i></div>{/if}
            </div>
        </div>
    {/if}

    <br />

    <div style="font-size: 15pt;">{vtranslate('LBL_INVENTORY_PDF_KINDY_FAREWELL', $MODULE_NAME)}</div>
</html>