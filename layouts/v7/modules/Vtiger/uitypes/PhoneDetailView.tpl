{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{assign var=MODULE value='PBXManager'}
{assign var=MODULEMODEL value=Vtiger_Module_Model::getInstance($MODULE)}
{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{if $MODULEMODEL and $MODULEMODEL->isActive() and $FIELD_VALUE}
    {assign var=PERMISSION value=PBXManager_Server_Model::checkPermissionForOutgoingCall()}
    {if $PERMISSION}
        {* Modified by Hieu Nguyen on 2019-12-20 *}
        {$FIELD_VALUE}

        {if PBXManager_Logic_Helper::isClick2CallEnabled($RECORD->getModuleName())}
            {PBXManager_Logic_Helper::renderButtonCall($FIELD_VALUE, $RECORD->getId())}
        {/if}
        {* End Hieu Nguyen *}
    {else}
        {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
    {/if}
{else}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
