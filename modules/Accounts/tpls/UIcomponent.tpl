{strip}
<link rel="stylesheet"
    href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrapswitch.min.css')}" />
<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
<div id="ui-compoent">
    <td class="fieldValue">
        <div class="referencefield-wrapper">
            <input name="popupReferenceModule" type="hidden" value="Accounts">
            <div class="input-group">
                <input name="account_id" type="hidden" value="" class="sourceField" data-displayvalue="" />
                <input id="account_id_display" name="account_id_display" data-fieldname="account_id"
                    datafieldtype="reference" type="text"
                    class="marginLeftZero autoComplete inputElement ui-autocomplete-input" value=""
                    placeholder="{vtranslate('LBL_TYPE_SEARCH', 'CRM')}" autocomplete="off" />
                <a href="#" class="clearReferenceSelection hide">&nbsp;x&nbsp;</a>
                <span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', 'CRM')}"><i
                        id="account_id_select" class="fa fa-search"></i></span>
            </div>
            <span class="createReferenceRecord cursorPointer clearfix" title="{vtranslate('LBL_CREATE', 'CRM')}"><i
                    id="account_id_create" class="fa fa-plus"></i></span>
        </div>
    </td>

    <div class="input-group inputElement" style="margin-bottom: 3px">
        <input type="text" name="<Date-Field-Name>" class="form-control dateField" data-fieldtype="date"
            datadate-format="{$USER_MODEL->get('date_format')}" data-rule-required="true" />
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
    </div>

    <div class="input-group inputElement time">
        <input type="text" name="<Time-Field-Name>" class="timepicker-default form-control" data-format="12"
            data-rule-required="true" />
        <span class="input-group-addon">
            <i class="fa fa-clock-o"></i>
        </span>
    </div>


    <select name="leadsource" class="inputElement select2" data-fieldtype="picklist">
        <option value="">--</option>
        <option value="value1">Option 1</option>
        <option value="value2">Option 2</option>
        <option value="value3">Option 3</option>
    </select>
    <!-- <div class="contents tabbable">
        <ul class="nav nav-tabs marginBottom10px">
            <li class="tab1 active"><a data-toggle="tab" href="#tab1"><strong>Tab1</strong></a></li>
            <li class="tab2"><a data-toggle="tab" href="#tab2"><strong>Tab2</strong></a></li>
        </ul>
        <div class="tab-content overflowVisible">
            <div class="tab-pane active" id="tab1">
                Tab1 content
            </div>
            <div class="tab-pane" id="tab2">
                Tab2 content
            </div>
        </div>
    </div> -->


    <label class="control-label fieldLabel col-sm-5">
        <select class="referenceModulesList select2" tabindex="-1" style="width: 140px;">
            <option value="Accounts">Accounts</option>
            <option value="Contacts">Contacts</option>
            <option value="Leads">Leads</option>
        </select>
    </label>

    <div class="controls fieldValue col-sm-6">
        <div class="referencefield-wrapper">
            <input name="popupReferenceModule" type="hidden" value="Accounts">
            <div class="input-group">
                <input name="parent_id" type="hidden" value="" class="sourceField" data-displayvalue="" />
                <input id="parent_id_display" name="parent_id_display" data-fieldname="parent_id"
                    datafieldtype="reference" type="text"
                    class="marginLeftZero autoComplete inputElement ui-autocomplete-input" value=""
                    placeholder="{vtranslate('LBL_TYPE_SEARCH', 'CRM')}" autocomplete="off" />
                <a href="#" class="clearReferenceSelection hide">&nbsp;x&nbsp;</a>
                <span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', 'CRM')}"><i
                        id="parent_id_select" class="fa fa-search"></i></span>
            </div>
            <span class="createReferenceRecord cursorPointer clearfix" title="{vtranslate('LBL_CREATE', 'CRM')}"><i
                    id="parent_id_create" class="fa fa-plus"></i></span>
        </div>
    </div>



</div>

<script>
    $(document).ready(function () {
        var form = $('#checkWarrantyForm');
        form.find('.bootstrap-switch').bootstrapSwitch(); // Bind all buttons
        form.find('[name="enable_notification"]').bootstrapSwitch(); // Bind a specific button
    });
</script>
{/strip}