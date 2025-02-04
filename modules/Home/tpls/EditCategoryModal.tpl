{*
    Name: EditCategoryModal.tpl
    Author: Phu Vo
    Date: 2020.10.12
*}

{strip}
    <div class="edit-homepage-modal config-homepage-modal modal-dialog modal-md modal-content modal-template-md">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_DASHBOARD_ADD_CATEGORY', $MODULE)}
        <form name="edit_category" class="form">
            <input type="hidden" name="module" value="Home" />
            <input type="hidden" name="action" value="DashboardAjax" />
            <input type="hidden" name="mode" value="saveWidgetCategory" />
            <input type="hidden" name="category_id" value="{$CATEGORY_DATA['id']}" />

            <div class="editViewBody">
                <div class="container-fluid modal-body">
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            <h4>{vtranslate('LBL_DASHBOARD_WIDGET_CATEGORY_NAME', $MODULE)} <span class="redColor">*</span></h4>
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            {vtranslate('LBL_DASHBOARD_WIDGET_CATEGORY_NAME_EN', $MODULE)}
                        </div>
                        <div class="col-lg-8 fieldValue">
                            <input type="text" class="inputElement" name="data[name_en]" value="{$CATEGORY_DATA['name_en']}" data-rule-required="true" />
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-lg-4 fieldLabel">
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            {vtranslate('LBL_DASHBOARD_WIDGET_CATEGORY_NAME_VN', $MODULE)}
                        </div>
                        <div class="col-lg-8 fieldValue">
                            <input type="text" class="inputElement" name="data[name_vn]" value="{$CATEGORY_DATA['name_vn']}" data-rule-required="true" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button class="btn btn-success" type="submit">OK</button>
                    <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', 'Vtiger')}</a>
                </center>
            </div>
        </form>
    </div>
{/strip}