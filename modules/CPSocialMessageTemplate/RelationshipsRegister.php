<?php

/* System auto-generated on 2019-08-02 09:01:19 am.  */

$relationships = array(
    array(
        'leftSideModule' => 'CPSocialMessageTemplate',
        'rightSideModule' => 'CPSocialMessageLog',
        'relationshipType' => '1:N',
        'relationshipName' => 'LBL_CPSOCIALMESSAGELOG_LIST',
        'enabledActions' => array(
            'ADD'
        ),
        'listingFunctionName' => 'get_dependents_list',
        'leftSideReferenceFieldName' => null,
        'rightSideReferenceFieldName' => 'related_social_message_template'
    )
);

