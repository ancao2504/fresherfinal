<?php
    /*
    *   DetailView.php
    *   Author: Phuc Lu
    *   Date: 2019.08.01
    *   Purpose: customize the layout
    */

    $displayParams = array(
        'scripts' => '
            <script type="text/javascript" src="{vresource_url("modules/Invoice/resources/DetailView.js")}"></script>
        ',
        'form' => array(
            'hiddenFields' => '
            ',
        ),
        'fields' => array(
            'salesorder_id' => array(
                'customTemplate' => '{$ORDER_LINK}'
            ),
            'account_id' => array(
                'customTemplate' => '{$PARTNER_LINK}'
            ),            
            'invoice_type' => array(
                'customTemplate' => '<span class="numberCircle" style="padding: 1px 7px; font-size: 12px;" data-value="{$RECORD->get("invoice_type")}">{vtranslate($RECORD->get("invoice_type"), $MODULE_NAME)}</span>'
            ),
        ),
    );