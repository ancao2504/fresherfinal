{strip}
    <div class="modal-dialog modal-lg call-popup-search-faq-full" data-mode="SEARCH">
        <div class="modal-content binding-form faq-search">
            <form class="form-horizontal" name="faq_search">
                {assign var=HEADER_TITLE value="FAQs"}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="input-group">
                                <input type="text" name="keyword" class="inputElement" placeholder="Nhập từ khóa để tìm kiếm" />
                                <span class="input-group-addon searchButton cursorPointer" title="Search">
                                    <i class="far fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <br />
                    <table id="faqListViewTable" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="fieldLabel number col-lg-1">#</th>
                                <th class="fieldValue question col-lg-11">{vtranslate('LBL_CALL_POPUP_FAQ_QUESTION', 'PBXManager')}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </form>
        </div>
        <div class="modal-content binding-form faq-detail">
            
        </div>
    </div>
{/strip}