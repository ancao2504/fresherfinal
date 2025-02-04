<?php

/* System auto-generated on 2021-04-02 05:50:31 pm.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPEmployee',
        'rightSideModule' => 'CPEmployeeCheckinLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_EMPLOYEE_CHECKIN_LOGS',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_employee'
    )
);

