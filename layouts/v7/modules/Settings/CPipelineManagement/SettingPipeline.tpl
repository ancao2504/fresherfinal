{strip}
<link rel="stylesheet"
    href="{vresource_url('layouts/v7/modules/Settings/CPipelineManagement/resources/SettingPipeline.css')}">
</link>
<script src="{vresource_url('resources/CustomColorPicker.js')}"></script>
<script src="{vresource_url('layouts/v7/modules/Settings/CPipelineManagement/resources/AddPipeline.js')}"></script>
<div class="editViewBody">
    <div class="addPipeline">
        <div class="fieldBlockContainer">
            <h4 class="fieldBlockHeader" style="margin-top:10px">Tạo mới pipleline</h4>
            <div class="contents tabbable" style="margin-top: 40px;">
                <ul class="nav nav-tabs marginBottom10px">
                    <li class="tab1 active"><a data-toggle="tab" href="#tab1"><strong>
                                Thông tin pipeline</strong></a></li>
                    <li class="tab2"><a data-toggle="tab" href="#tab2"><strong>Tự động hóa</strong></a></li>
                </ul>
                <div class="tab-content overflowVisible">
                    <div class="tab-pane active" id="tab1">
                        <table class="table table-borderless">
                            <tbody>
                                <!-- Dòng 1 -->
                                <tr>
                                    <td class="fieldLabel name alignMiddle">Tên pipeline&nbsp;
                                        <span class="redColor">*</span>
                                    </td>
                                    <td class="fieldValue name">
                                        <input type="text" class="inputElement" name="name" value=""
                                            data-rule-required="true" aria-required="true">
                                    </td>
                                    <td class="fieldLabel time alignMiddle">Thời gian pipeline&nbsp;</td>
                                    <td class="fieldValue time">
                                        <div class="input-group">
                                            <input type="text" name="lastname" value="" class="inputElement time"
                                                style="width: 30px;">
                                            <select class="inputElement select2 select2-offscreen"
                                                style="width:150px; margin-left: 25px;" name="timetype" tabindex="-1"
                                                title="">
                                                <option value="">Chọn một giá trị</option>
                                                <option value="Day">Ngày</option>
                                                <option value="Month">Tháng</option>
                                                <option value="Year">Năm</option>
                                            </select>
                                            <span data-toggle="tooltip" style="margin-top:7px; margin-left: 20px;"
                                                data-tippy-content="Chọn 1 giá trị của Loại để phân nhóm người liên hệ (Khách hàng cá nhân, Đối tác, Người liên hệ, Khác). Trường hợp để lưu trữ , báo cáo liên quan đến khách hàng cá nhân chạy được chính xác thì trường Loại bạn cần chọn = Khách hàng cá nhân.">
                                                <i class="far fa-info-circle"></i>
                                            </span>
                                        </div>

                                    </td>
                                </tr>
                                <!-- Dòng 2 -->
                                <tr>
                                    <td class="fieldLabel module alignMiddle">Module&nbsp;
                                        <span class="redColor">*</span>
                                    </td>
                                    <td class="fieldValue module">
                                        <select class="select2-container select2 inputElement col-sm-6 selectModule">
                                            {foreach item=MODULE_NAME from=$INVENTORY_MODULES}
                                            <option value={$MODULE_NAME}>{vtranslate({$MODULE_NAME},
                                                {$MODULE_NAME})}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td class="fieldLabel auto alignMiddle">Tự động chuyển bước&nbsp;</td>
                                    <td class="fieldValue donotcall" style="width:25%">
                                        <input type="hidden" name="donotcall" value="0">
                                        <input class="inputElement" style="width:16px;height:16px;"
                                            data-fieldname="donotcall" data-fieldtype="checkbox" type="checkbox"
                                            name="donotcall">
                                    </td>
                                </tr>
                                <!-- Dòng 3 -->
                                <tr>
                                    <td class="fieldLabel grant alignMiddle">
                                        Phân quyền&nbsp;
                                    </td>
                                    <td class="fieldValue grant">
                                        <select multiple name="receive_notifications_method"
                                            class="inputElement select2">
                                            <option value="all">
                                                Tất cả
                                            </option>
                                            <option value="sale">
                                                Bộ phận Sale
                                            </option>
                                            <option value="marketing">
                                                Bộ phần Marketing
                                            </option>
                                            <option value="admin">
                                                Admin
                                            </option>
                                        </select>
                                    </td>
                                    <td class="fieldLabel description alignMiddle">Mô tả&nbsp;</td>
                                    <td class="fieldValue" style="width:25%">
                                        <textarea rows="3" class="inputElement textAreaElement col-lg-12 "
                                            name="description"></textarea>
                                    </td>
                                </tr>
                                <!-- Dòng 4 -->
                                <tr>
                                    <td class="fieldLabel status alignMiddle">Trạng thái&nbsp;
                                        <span class="redColor">*</span>
                                    </td>
                                    <td class="fieldValue status">
                                        <div class="pull-left">
                                            <span style="margin-right: 50px;">
                                                <input name="status" type="radio" value="active" checked="">&nbsp;
                                                <span>Kích hoạt</span>
                                            </span>
                                            <span style="margin-right: 10px;">
                                                <input name="status" type="radio" value="inActive">
                                                &nbsp;<span>Không kích hoạt</span>
                                            </span>
                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                        <hr />
                        <div style="height: 500px;">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 350px;">Tên bước</th>
                                        <th>Tỉ lệ thành công</th>
                                        <th>Thời gian thực hiện</th>
                                        <th>Bước bắt buộc</th>
                                        <th>Bước chuyển đến cho phép</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="empty-message">
                                            Xây dựng các bước giúp theo dõi tình trạng, tiến độ trong một quy trình
                                            <br />
                                            <button style="margin-top: 20px;" type="button"
                                                class="btn btn-default module-buttons" id="addStepButton">
                                                <i class="far fa-plus"></i>&nbsp;&nbsp;Thêm bước
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab2">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="addStepPipelineNewModal" class="modal-dialog modal-content hide">
    {include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE="Thêm bước" }
    <form id="addStepPipelineModalForm" class="form-horizontal addStepPipelineNewModal" method="POST"
        style="margin-top: 20px;">
        <input type="hidden" name="leftSideModule" value="{$SELECTED_MODULE_NAME}" />
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Nhãn hiển thị (Tiếng việt)</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="product_name" class="form-control" data-rule-required="true" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Nhãn hiển thị (Tiếng Anh)</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="serial_no" class="form-control" data-rule-required="true" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Giá trị</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="website" class="form-control" data-rule-required="true" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Chọn màu</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input name="color" value="{$CURRENT_COLOR}" data-rule-required="true" />
            </div>
        </div>

        {include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}

    </form>
</div>
<div id="addStepPipelineModal" class="modal-dialog modal-content hide">
    {include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE="Thêm bước" }
    <form id="addStepPipelineModalForm" class="form-horizontal addStepPipelineModal" method="POST"
        style="margin-top: 20px;">
        <input type="hidden" name="leftSideModule" value="{$SELECTED_MODULE_NAME}" />
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Nhãn hiển thị (Tiếng việt)</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <div class="referencefield-wrapper ">
                    <select name="leadsource" class="inputElement select2" data-fieldtype="picklist"
                        data-rule-required="true" style="display: none">
                        <option value="">Chọn một giá trị</option>
                        <option value="value1">Option 1</option>
                        <option value="value2">Option 2</option>
                        <option value="value3">Option 3</option>
                    </select>
                    <button id="addNewStepModal" class="addNewStepModal cursorPointer clearfix" title="Thêm mới"
                        style="margin-left: 7px">
                        <i class="far fa-plus"></i>
                    </button>
                </div>

            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Nhãn hiển thị (Tiếng Anh)</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="serial_no" class="form-control" data-rule-required="true" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Giá trị</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input type="text" name="website" class="form-control" data-rule-required="true" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-5">
                <span>Chọn màu</span>
                <span class="redColor">*</span>
            </label>
            <div class="controls col-sm-6">
                <input name="color" value="{$CURRENT_COLOR}" data-rule-required="true" />
            </div>
        </div>

        {include file="ModalFooter.tpl"|@vtemplate_path:'Vtiger'}

    </form>
</div>
<div class="modal-overlay-footer clearfix">
    <div class="row clear-fix">
        <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
            <button type="submit" class="btn cancelButton btn-default module-buttons">Hủy</button>
            <button type="submit" class="btn nextButton btn-primary module-buttons" style="margin-left: 10px">
                Tiếp theo</button>
        </div>
    </div>
</div>
{/strip}