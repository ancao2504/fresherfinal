<?php
    /*
    *   EditView.php
    *   Author: Phuc Lu
    *   Date: 2019.08.01
    *   Purpose: customize the layout
    */

    $displayParams = array(
        'scripts' => '
            <script type="text/javascript" src="{vresource_url("modules/Invoice/resources/EditView.js")}"></script>
        ',
        'form' => array(
            'hiddenFields' => '
                {* Added by Hieu Nguyen on 2020-12-08 *}
                <input type="hidden" id="hidden_related_purchaseorder" value="{$RECORD->get("related_purchaseorder")}" data-name="{Vtiger_Util_Helper::getRecordName($RECORD->get("related_purchaseorder"))}" />
                <input type="hidden" id="hidden_related_vendor" value="{$RECORD->get("related_vendor")}" data-name="{Vtiger_Util_Helper::getRecordName($RECORD->get("related_vendor"))}" />
                <input type="hidden" id="hidden_related_salesorder" value="{$RECORD->get("salesorder_id")}" data-name="{Vtiger_Util_Helper::getRecordName($RECORD->get("salesorder_id"))}" />
                <input type="hidden" id="hidden_related_account" value="{$RECORD->get("account_id")}" data-name="{Vtiger_Util_Helper::getRecordName($RECORD->get("account_id"))}" />
                {* End Hieu Nguyen *}
            ',
        ),
        // Modified by Hieu Nguyen on 2020-12-07
        'fields' => array(
            'salesorder_id' => array(
                'customLabel' => '{if $RECORD->get("invoice_type") eq "buy"}{vtranslate("LBL_PURCHASE_ORDER", $MODULE_NAME)}{else}{vtranslate("LBL_SALE_ORDER", $MODULE_NAME)}{/if}',
            ),
            'invoice_type' => array(
                'customTemplate' => '{include file="modules/Invoice/InvoiceTypeFieldEditView.tpl"}'
            ),
            'account_id' => array(
                'customLabel' => '{if $RECORD->get("invoice_type") eq "buy"}{vtranslate("LBL_VENDOR", $MODULE_NAME)}{else}{vtranslate("LBL_ACCOUNT", $MODULE_NAME)}{/if}',
            ),
        ),
        // End Hieu Nguyen
    );