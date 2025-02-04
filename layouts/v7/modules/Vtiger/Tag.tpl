{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
 
 <span class="tag {if $ACTIVE eq true} active {/if}" data-toggle="tooltip" title="{$TAG_MODEL->getName()}" data-type="{$TAG_MODEL->getType()}" data-id="{$TAG_MODEL->getId()}">
    {* Modified by Phu Vo on 2021.11.18 *}
    {if $POPUP == true && !$NO_DELETE}
        <i class="deleteTag far fa-close"></i>
    {/if}
    {* End Phu Vo *}
    <i class="activeToggleIcon fal {if $ACTIVE eq true} fa-circle {else} fa-circle {/if}"></i>
    <span class="tagLabel display-inline-block textOverflowEllipsis" title="{$TAG_MODEL->getName()}">{$TAG_MODEL->getName()}</span>
    {if !$NO_EDIT || !NO_DELETE}
        <div class="tag-actions-wrapper">
            {if !$NO_EDIT}
                <i class="editTag far fa-pen"></i>
            {/if}
            {if $POPUP != true && !$NO_DELETE} {* Modified by Phu Vo on 2021.11.18 *}
                <i class="deleteTag far fa-trash-alt"></i>
            {/if}
        </div>
    {/if}
</span>