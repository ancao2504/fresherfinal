{* Added by Hieu Nguyen on 2022-02-15 *}

{strip}
	{$RECORD->get('customer_phone')}
	{PBXManager_Logic_Helper::renderButtonCall($RECORD->get('customer_phone'), $RECORD->getId())}
{/strip}