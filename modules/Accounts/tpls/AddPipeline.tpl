{strip}
<link rel="stylesheet" href="{vresource_url('modules/Accounts/resources/AddPipeline.css')}">
</link>
<div class="addPipeline">
    <div class="fieldBlockContainer">
        <h4 class="fieldBlockHeader" style="margin-top:10px">Tạo mới pipleline</h4>
        <div class="contents tabbable" style="margin-top: 40px;">
            <ul class="nav nav-tabs marginBottom10px">
                <li class="tab1 active"><a data-toggle="tab" href="#tab1"><strong>Thông tin pipeline</strong></a></li>
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
                                <td class="fieldLabel module alignMiddle">
                                    Phân quyền&nbsp;
                                </td>
                                <td class="fieldValue module">
                                    <select multiple name="receive_notifications_method" class="inputElement select2">
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
                                <td class="fieldLabel auto alignMiddle">Mô tả&nbsp;</td>
                                <td class="fieldValue" style="width:25%">
                                    <textarea rows="3" class="inputElement textAreaElement col-lg-12 "
                                        name="description"></textarea>
                                </td>
                            </tr>
                            <!-- Dòng 4 -->
                            <tr>
                                <td class="fieldLabel module alignMiddle">Trạng thái&nbsp;
                                    <span class="redColor">*</span>
                                </td>
                                <td class="fieldValue module">
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
                </div>
                <div class="tab-pane" id="tab2">
                    Tab2 content
                </div>
            </div>
        </div>
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
</div>

{/strip}