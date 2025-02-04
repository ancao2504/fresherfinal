{*
    Name: EditHomepageModal.tpl
    Author: Phu Vo
    Date: 2020.10.12
*}

{strip}
    <div class="edit-homepage-modal config-homepage-modal modal-dialog modal-md modal-content modal-template-md">
        {if $MODE == 'edit'}
            {assign var=TITLE value=vtranslate('LBL_DASHBOARD_EDIT_DASHBOARD_INFORMATION', $MODULE)}
        {else if $IS_DUPLICATE == true}
            {assign var=TITLE value="{vtranslate('LBL_DASHBOARD_DUPLICATE_DASHBOARD', $MODULE)}: <span style='text-transform: uppercase'>$DUPLICATE_TEMPLATE_NAME</span>"}
        {else}
            {assign var=TITLE value=vtranslate('LBL_DASHBOARD_ADD_DASHBOARD', $MODULE)}
        {/if}

        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$TITLE}
        <form name="edit_homepage" class="form">
            <input type="hidden" name="id" value="{$TEMPLATE_DATA['id']}" />
            <input type="hidden" name="module" value="Home" />
            <input type="hidden" name="action" value="DashboardAjax" />
            <input type="hidden" name="mode" value="saveDashboardTemplate" />
            <input type="hidden" name="is_duplicate" value="{$IS_DUPLICATE}" />

            <div class="editViewBody">
                <div class="container-fluid modal-body">
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            {vtranslate('LBL_DASHBOARD_NAME', $MODULE)} <span class="redColor">*</span>
                        </div>
                        <div class="col-lg-8 fieldValue">
                            <input type="text" class="inputElement" name="template_data[name]" data-rule-required="true" value="{$TEMPLATE_DATA['name']}" />
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            {vtranslate('LBL_DASHBOARD_STATUS', $MODULE)} <span class="redColor">*</span>
                        </div>
                        <div class="col-lg-8 fieldValue">
                            <select class="select2 inputElement" name="template_data[status]" data-rule-required="true">
                                <option value="">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
                                <option value="Active" {if $TEMPLATE_DATA['status'] == "Active"}selected{/if}>{vtranslate('LBL_DASHBOARD_ACTIVE', $MODULE)}</option>
                                <option value="Inactive"{if $TEMPLATE_DATA['status'] == "Inactive"}selected{/if}>{vtranslate('LBL_DASHBOARD_INACTIVE', $MODULE)}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            {vtranslate('LBL_DASHBOARD_APPLIED_ROLES', $MODULE)} <span class="redColor">*</span>
                        </div>
                        <div class="col-lg-8 fieldValue">
                            <select name="template_data[roles]"
                                multiple="true"
                                class="form-control inputElement select2" 
                                data-rule-required="true"
                            >
                                {foreach from=$ROLE_LIST item=ROLE}
                                    {assign var=roleid value=$ROLE->get('roleid')}
                                    {assign var=rolename value=$ROLE->get('rolename')}
                                    <option value="{$roleid}" {if in_array($roleid, $TEMPLATE_DATA['roles'])}selected{/if}>{$rolename}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            {vtranslate('LBL_DASHBOARD_PERMISSION', $MODULE)} <span class="redColor">*</span>
                        </div>
                        <div class="col-lg-8 fieldValue">
                            <select class="select2 inputElement" name="template_data[permission]" data-rule-required="true">
                                <option value="">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
                                <option value="Read Only" {if $TEMPLATE_DATA['permission'] == "Read Only"}selected{/if}>{vtranslate('LBL_DASHBOARD_READ_ONLY', $MODULE)}</option>
                                <option value="Full Access" {if $TEMPLATE_DATA['permission'] == "Full Access"}selected{/if}>{vtranslate('LBL_DASHBOARD_FULL_ACCESS', $MODULE)}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button class="btn btn-success" type="submit" name="save_mode" value="save">{vtranslate('LBL_SAVE', $MODULE)}</button>
                    {if $MODE == 'create'}<button class="btn btn-default" type="submit" name="save_mode" value="save_and_edit_layout">{vtranslate('LBL_DASHBOARD_SAVE_AND_EDIT_LAYOUT', $MODULE)}</button>{/if}
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                </center>
            </div>
        </form>
    </div>
{/strip}