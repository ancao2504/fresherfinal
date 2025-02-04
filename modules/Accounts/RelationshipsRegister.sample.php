<?php

/*
*	Sample Relationship Register structure
*	Author: Hieu Nguyen
*	Date: 2018-08-25
*	Usage: Copy this file into a new file named RelationshipsRegister.php to handle register relationships for Account. Do that the same for other modules
	Note: REMOVE this comment on the file you create youself to avoid messing comments
*/

$relationships = array(
	array(
		'leftSideModule' => 'Accounts',
		'rightSideModule' => 'Project',
		'relationshipType' => '1:N',
		'relationshipName' => 'LBL_PROJECT_LIST',
		'enabledActions' => array('ADD'),
		'listingFunctionName' => 'get_related_projects',
		'leftSideReferenceFieldName' => null,
		'rightSideReferenceFieldName' => 'related_account'
	),
	array(
		'leftSideModule' => 'Accounts',
		'rightSideModule' => 'Products',
		'relationshipType' => 'N:N',
		'relationshipName' => 'LBL_PRODUCT_LIST',
		'enabledActions' => array('ADD', 'SELECT'),
		'listingFunctionName' => 'get_favorite_products',
		'leftSideReferenceFieldName' => null,
		'rightSideReferenceFieldName' => null
	)
);