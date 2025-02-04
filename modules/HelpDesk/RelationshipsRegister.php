<?php

/*
	System auto-generated on 2022-01-06 02:13:56 pm by admin. 
*/

$relationships = array(
    array(
        'leftSideModule' => 'HelpDesk',
        'rightSideModule' => 'CPTicketCommunicationLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPTICKETCOMMUNICATIONLOG_LIST',
        'enabledActions' => array(

        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'ticket_id'
    )
);

