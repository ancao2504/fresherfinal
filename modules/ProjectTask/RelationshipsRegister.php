<?php

/* System auto-generated on 2019-03-12 04:20:10 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'ProjectTask',
        'rightSideModule' => 'Calendar',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CALENDAR_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldId' => null,
        'rightSideReferenceFieldId' => 899
    )
);

