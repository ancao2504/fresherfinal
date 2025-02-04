<?php
    /*
    *	EditView.php
    *	Author: Phuc Lu
    *	Date: 2019.11.14
    *   Purpose: handle leadstatus picklist
    */

    $displayParams = array(
        'scripts' => '
			<script type="text/javascript" src="{vresource_url("modules/Leads/resources/EditView.js")}"></script>
        ',
        'form' => array(
            'hiddenFields' => '

            ',
        ),
        'fields' => array(
            'leadstatus' => array(
                'customTemplate' => '{if $RECORD->get("leadstatus") == "Converted"}<span class="picklistColor_leadstatus_Converted">{vtranslate("Converted", $MODULE)}</span><span style="display: none;">{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}</span>{else}{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}{/if}'
            )
        ),
    );