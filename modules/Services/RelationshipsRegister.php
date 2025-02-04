<?php

/* System auto-generated on 2020-06-29 02:17:11 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'Services',
        'rightSideModule' => 'HelpDesk',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_HELPDESK_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'service_id'
    )
);

