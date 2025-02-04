{* Added by Hieu Nguyen on 2020-10-12 *}

{strip}
    <div class="modal-dialog modelContainer">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_EDIT_DASHBOARD_TAB_POPUP_TITLE', $MODULE)}
        
        <div class="modal-content">
            <form id="EditDashBoardTab" name="EditDashBoardTab" method="POST" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="action" value="DashBoardTab"/>
                <input type="hidden" name="mode" value="renameTab"/>
                <input type="hidden" name="tab_id" value="{$TAB_INFO['id']}"/>

                <div class="modal-body clearfix">
                    <div class="row">
                        <div class="col-lg-5">
                            <label class="control-label pull-right marginTop5px">
                                {vtranslate('LBL_DASHBOARD_TAB_NAME_EN',$MODULE)}&nbsp;<span class="redColor">*</span>
                            </label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="tab_name_en" data-rule-required="true" size="25" class="inputElement" maxlength="30" value="{$TAB_INFO['name_en']}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-5">
                            <label class="control-label pull-right marginTop5px">
                                {vtranslate('LBL_DASHBOARD_TAB_NAME_VN',$MODULE)}&nbsp;<span class="redColor">*</span>
                            </label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="tab_name_vn" data-rule-required="true" size="25" class="inputElement" maxlength="30"  value="{$TAB_INFO['name_vn']}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12" style="margin-top: 10px; padding: 5px;">
                            <div class="alert-info">
                                <center>
                                    <i class="far fa-info-circle"></i>&nbsp;&nbsp;
                                    {vtranslate('LBL_DASHBOARD_TAB_NAME_HINT', $MODULE)}
                                </center>
                            </div>
                        </div>
                    </div>
                </div>

                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}