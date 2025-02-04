{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* Modified by Hieu Nguyen on 2020-10-12 *}

{strip}
    <div class="modal-dialog modelContainer">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_ADD_DASHBOARD_TAB_POPUP_TITLE', $MODULE)}

        <div class="modal-content">
            <form id="AddDashBoardTab" name="AddDashBoardTab" method="POST" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="action" value="DashBoardTab"/>
                <input type="hidden" name="mode" value="addTab"/>

                <div class="modal-body clearfix">
                    <div class="row">
                        <div class="col-lg-5">
                            <label class="control-label pull-right marginTop5px">
                                {vtranslate('LBL_DASHBOARD_TAB_NAME_EN', $MODULE)}&nbsp;<span class="redColor">*</span>
                            </label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="tab_name_en" data-rule-required="true" size="25" class="inputElement" maxlength="30" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-5">
                            <label class="control-label pull-right marginTop5px">
                                {vtranslate('LBL_DASHBOARD_TAB_NAME_VN', $MODULE)}&nbsp;<span class="redColor">*</span>
                            </label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="tab_name_vn" data-rule-required="true" size="25" class="inputElement" maxlength="30" />
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
