{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{foreach key=index item=jsModel from=$SCRIPTS}
    <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<input type="hidden" id="recordId" value="{$RECORD->getId()}"/>
{if $FIELDS_INFO neq null}
    <script type="text/javascript">
        var related_uimeta = (function() {
            var fieldInfo = {$FIELDS_INFO};
            return {
                field: {
                    get: function(name, property) {
                        if (name && property === undefined) {
                            return fieldInfo[name];
                        }
                        if (name && property) {
                            return fieldInfo[name][property]
                        }
                    },
                    isMandatory: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].mandatory;
                        }
                        return false;
                    },
                    getType: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].type
                        }
                        return false;
                    }
                },
            };
        })();
    </script>
{/if}

{* Refactored code by Hieu Nguyen on 2021-08-03 to support custom header in Overlay Detailview *}
<div class="fc-overlay-modal overlayDetail">
    <div class="modal-content">
        <div class="row-fluid overlayDetailHeader" style="z-index:1;">
            <div class="row-fluid" style="padding-left:0px;">
                {include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE_NAME MODULE_MODEL=$MODULE_MODEL RECORD=$RECORD}

                <div class="pull-right">
                    <div class="clearfix">
                        <div class="btn-group">
                            <button class="btn btn-default fullDetailsButton" onclick="window.location.href='{$RECORD->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}'">{vtranslate('LBL_DETAILS', $MODULE_NAME)}</button>
                            
                            {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                                {if $DETAIL_VIEW_BASIC_LINK && $DETAIL_VIEW_BASIC_LINK->getLabel() == 'LBL_EDIT'}
                                    <button class="btn btn-default editRelatedRecord" value="{$RECORD->getEditViewUrl()}">{vtranslate('LBL_EDIT', $MODULE_NAME)}</button>
                                {/if}
                            {/foreach}
                        </div> 
                        <div class="pull-right">
                            <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                                <span aria-hidden="true" class="far fa-close"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            {* Added by Hieu Nguyen on 2021-08-03 to support custom header in DetailView *}
            <div class="row-fluid customDetailViewHeader">
                {assign var="CUSTOM_HEADER_FILE" value="modules/$MODULE/tpls/DetailViewCustomHeader.tpl"}

                {if file_exists($CUSTOM_HEADER_FILE)}
                    {include file=$CUSTOM_HEADER_FILE}
                {/if}
            </div>
            {* End Hieu Nguyen *}
        </div>

        <div class="modal-body">
            <div class="detailViewContainer">   
                {* Added by Hieu Nguyen on 2019-06-11 to show hidden input for main_owner_id *}
                {if !$RECORD && $RECORD_STRUCTURE_MODEL}
                    {assign var="RECORD" value=$RECORD_STRUCTURE_MODEL->getRecord()}
                {/if}

                <input type="hidden" name="main_owner_id" value="{if $RECORD}{$RECORD->fetchedRow['main_owner_id']}{/if}" />
                {* End Hieu Nguyen *}

                {include file='DetailViewFullContents.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
            </div>
        </div>
    </div>
</div>
{* End Hieu Nguyen *}