{* Added by Tin Bui on 2022.03.16 - Email template datatable UI *}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($MODULE, $MODULE)}}
            <div class="modal-body">
                <table class="table dataTable table-highlighted" id="emailTemplateTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{vtranslate('LBL_TEMPLATE_NAME', $MODULE)}</th>
                            <th>{vtranslate('LBL_SUBJECT', $MODULE)}</th>
                            <th>{vtranslate('LBL_DESCRIPTION', $MODULE)}</th>
                            <th>{vtranslate('LBL_MODULE_NAME', $MODULE)}</th>
                        </tr>
                    <thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="{vresource_url('modules/EmailTemplates/resources/PopupDatatable.js')}"></script>
{/strip}