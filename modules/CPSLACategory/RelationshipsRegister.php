<?php

/*
	System auto-generated on 2021-12-17 12:04:29 pm by admin. 
*/

$relationships = array(
    array(
        'leftSideModule' => 'CPSLACategory',
        'rightSideModule' => 'HelpDesk',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_HELPDESK_LIST',
        'enabledActions' => array(
            
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_cpslacategory'
    )
);

