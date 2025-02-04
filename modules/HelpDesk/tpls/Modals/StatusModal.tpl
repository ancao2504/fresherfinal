{* Added by Tin Bui on 2022.03.16 - Change ticket status modal UI *}
{strip}
    <div id="statusModal" class="myModal modal-dialog modal-lg modal-content statusModal custom-modal">
        <div class="modal-header">
            <div class="clearfix">
                <div class="pull-right">
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true" class="far fa-close"></span>
                    </button>
                </div>
                <h4 class="pull-left">{vtranslate('LBL_CHANGE_STATUS_MODAL_TITLE', $MODULE)}</h4>
            </div>
        </div>
        <form method="POST" class="form-horizontal statusForm">
            <input type="hidden" name="action" value="SaveAjax" />
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="record" value="{$RECORD_ID}" />
            <input type="hidden" name="main_owner_id" value="{$RECORD->get('main_owner_id')}" />
            
            {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
            {/if}
            <div style="padding: 20px 10px 0px 10px">
                <table class="table detailview-table no-border">
                    {foreach from=$STATUS_STRUCTURE item=ROW}
                        <tr>
                            {foreach from=$ROW item=FIELD}
                                <td class="fieldLabel alignMiddle {$FIELD->getName()}">
                                    {vtranslate($FIELD->get('label'), $MODULE)}
                                    &nbsp;
                                    {if $FIELD->isMandatory() eq true} <span class="redColor">*</span> {/if}
                                </td>
                                <td class="fieldValue {$FIELD->getName()}">
                                    {* Tin Bui: set fieldname = NULL, it will be re-assigned from field model in field template *}
                                    {include file=vtemplate_path($FIELD->getUITypeModel()->getTemplateName(), $MODULE) FIELD_MODEL=$FIELD USER_MODEL=$USER_MODEL FIELD_NAME=NULL}
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </table>
            </div>
    
            <div class="modal-footer">
                <center>
                    <button class="btn btn-success js-save" type="button">{vtranslate('LBL_SAVE', 'Vtiger')}</button>
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </center>
            </div>
        </form>
    </div>
{/strip}
{* Ended by Tin Bui *}