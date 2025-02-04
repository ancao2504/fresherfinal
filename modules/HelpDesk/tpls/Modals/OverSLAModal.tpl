{* Added by Tin Bui on 2022.03.29 - Over SLA modal UI *}
{strip}
    <div id="overSLAModal" class="modal-dialog modal-content statusModal custom-modal">
        <div class="modal-header">
            <div class="clearfix">
                <h4 class="pull-left">{vtranslate('LBL_OVERSLA_MODAL_TITLE', $MODULE)}</h4>
            </div>
        </div>
        <form method="POST" class="form-horizontal overSLAForm">
            <input type="hidden" name="action" value="SaveAjax" />
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="record" value="{$RECORD_ID}" />
            {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
            {/if}
            <div style="padding: 20px 10px 0px 10px">
                <table class="table detailview-table no-border">
                    {foreach from=$STRUCTURE item=FIELD}
                        <tr>
                            <td class="fieldLabel alignMiddle {$FIELD->getName()}">
                                {vtranslate($FIELD->get('label'), $MODULE)}
                                &nbsp;
                                {if $FIELD->isMandatory() eq true} <span class="redColor">*</span> {/if}
                            </td>
                            <td class="fieldValue {$FIELD->getName()}">
                                {* Tin Bui: set fieldname = NULL, it will be re-assigned from field model in field template *}
                                {include file=vtemplate_path($FIELD->getUITypeModel()->getTemplateName(), $MODULE) FIELD_MODEL=$FIELD USER_MODEL=$USER_MODEL FIELD_NAME=NULL}
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
    
            <div class="modal-footer">
                <center>
                    <button class="btn btn-success js-save" type="button">{vtranslate('LBL_SAVE', 'Vtiger')}</button>
                </center>
            </div>
        </form>
    </div>
{/strip}
{* Ended by Tin Bui *}