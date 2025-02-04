<?php

// Add attributes to override EditView layout
$editViewBlocks = array(
	'LBL_ACCOUNT_INFORMATION' => array(
		'sequence' => '1',	// This attribute will override block layout of customer and developer
	),
	'LBL_CUSTOM_INFORMATION' => array(
		'sequence' => '2',	// This attribute will override field layout of customer and developer
	),
	'LBL_ADDRESS_INFORMATION' => array(
		'sequence' => '3',	// This attribute will override field layout of customer and developer
	),
	'LBL_DESCRIPTION_INFORMATION' => array(
		'sequence' => '4',	// This attribute will override field layout of customer and developer
	)
);

// Add attributes to override DetailView layout
$detailViewBlocks = array(
	// Leave empty if you have nothing to override in DetailView
);

// Add attributes to override Fields layout
$fields = array(
	'phone' => array(
		'editview_block_name' => 'LBL_ACCOUNT_INFORMATION',	// This attribute will override field layout of customer and developer
		'editview_sequence' => '3'							// This attribute will override field layout of customer and developer
	),
	'website' => array(
		'editview_block_name' => 'LBL_ACCOUNT_INFORMATION',	// This attribute will override field layout of customer and developer
		'editview_sequence' => '4'							// This attribute will override field layout of customer and developer
	),
);