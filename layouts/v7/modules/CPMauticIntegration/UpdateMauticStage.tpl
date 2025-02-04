{strip}
{* Declare modal for update Mautic stage modal *}
    <div id="div-update-mautic-stage" class="button_modal modal-dialog modal-content hide">
        {assign var="HEADER_TITLE" value={vtranslate('LBL_MAUTIC_UPDATE_MAUTIC_STAGE', 'Vtiger')}}
        {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
        
        <form class="form-horizontal formUpdateMauticStage" method="POST">
            <input type="hidden" name="leftSideModule" value="{$MODULE_NAME}"/>
            <textarea style="display:none" name="params" id="params" value=""></textarea>

            <div class="form-group" style = "margin-top: 15px;">
                <label class="control-label fieldLabel col-sm-4">
                    <span>{vtranslate('LBL_MAUTIC_STAGE_LIST', 'Vtiger')}</span>&nbsp;<span class="redColor">*</span>
                </label>
                
                <div class="control referencefield-wrapper col-sm-7">
                    <select name="stage_mautic_id" class="slc_stage" data-rule-required="true" class='input-group' style="width:90%;">
                    {foreach from=$STAGES key=key item=item}
                        <option value="{$item.id}">{$item.name}</option>
                    {/foreach}
                    </select>
                    <span class="new-button" onclick="addNewStage(this)">
                        <i class="far fa-plus"></i>
                    </span>
                </div>
            </div>

            {assign var="BUTTON_NAME" value={vtranslate('LBL_CONFIRM', 'Vtiger')}}
            {include file="ModalFooter.tpl"|@vtemplate_path:Vtiger BUTTON_NAME=$BUTTON_NAME}
        </form>
    </div>

    <div id="div-add-new-mautic-stage" class="button_modal modal-dialog modal-content hide">
        {assign var="HEADER_TITLE" value={vtranslate('LBL_MAUTIC_ADD_NEW_STAGE', 'Vtiger')}}
        {include file="ModalHeader.tpl"|@vtemplate_path:$MODULE_NAME TITLE=$HEADER_TITLE}
        <div class="formAddNewMauticStage">
            <input type="hidden" name="leftSideModule" value="{$MODULE_NAME}"/>
            <div class="form-group" style = "margin-top: 15px;">
                <label class="control-label fieldLabel col-sm-4">
                    <span>{vtranslate('LBL_MAUTIC_STAGE_NAME', 'Vtiger')}</span>&nbsp;<span class="redColor">*</span>
                </label>
                
                <div class="control col-sm-7">
                    <input type="text" data-fieldname="name" data-fieldtype="string" class="inputElement nameField" name="name" value="" data-rule-required="true" aria-required="true">
                </div>
            </div>

            {assign var="BUTTON_NAME" value={vtranslate('LBL_SAVE', 'Vtiger')}}
            {include file="ModalFooter.tpl"|@vtemplate_path:Vtiger BUTTON_NAME=$BUTTON_NAME}
        </div>
    </div>
{/strip}

{literal}
<style>
.formUpdateMauticStage .new-button{
    float: right !important;
    margin-left: 5px;
    margin-top: 1px;
    border: 1px solid #DDDDDD;
    padding: 3px 7px;
    text-align: center;
    color: #666;
    background: #F3F3F3;
}
</style>
{/literal}