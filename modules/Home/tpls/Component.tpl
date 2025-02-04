{strip}
<div class="components-container container-fluid">
    <h4>Color</h4>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Blue</h5>
                    <div class="colors">
                        <div class="color" style="background-color: var(--blue-dark-1)"></div>
                        <div class="color" style="background-color: var(--blue-dark-2)"></div>
                        <div class="color" style="background-color: var(--blue-1)"></div>
                        <div class="color" style="background-color: var(--blue-2)"></div>
                        <div class="color" style="background-color: var(--blue-3)"></div>
                        <div class="color" style="background-color: var(--blue-4)"></div>
                        <div class="color" style="background-color: var(--blue-5)"></div>
                        <div class="color" style="background-color: var(--blue-light-1)"></div>
                        <div class="color" style="background-color: var(--blue-light-2)"></div>
                    </div>
                    <h5 class="card-title">Gray</h5>
                    <div class="colors">
                        <div class="color" style="background-color: var(--gray-dark-1)"></div>
                        <div class="color" style="background-color: var(--gray-dark-2)"></div>
                        <div class="color" style="background-color: var(--gray-1)"></div>
                        <div class="color" style="background-color: var(--gray-2)"></div>
                        <div class="color" style="background-color: var(--gray-3)"></div>
                        <div class="color" style="background-color: var(--gray-4)"></div>
                        <div class="color" style="background-color: var(--gray-5)"></div>
                        <div class="color" style="background-color: var(--gray-light-1)"></div>
                        <div class="color" style="background-color: var(--gray-light-2)"></div>
                    </div>
                    <h5 class="card-title">Cyan</h5>
                    <div class="colors">
                        <div class="color" style="background-color: var(--cyan-dark-1)"></div>
                        <div class="color" style="background-color: var(--cyan-dark-2)"></div>
                        <div class="color" style="background-color: var(--cyan-1)"></div>
                        <div class="color" style="background-color: var(--cyan-2)"></div>
                        <div class="color" style="background-color: var(--cyan-3)"></div>
                        <div class="color" style="background-color: var(--cyan-4)"></div>
                        <div class="color" style="background-color: var(--cyan-5)"></div>
                        <div class="color" style="background-color: var(--cyan-light-1)"></div>
                        <div class="color" style="background-color: var(--cyan-light-2)"></div>
                    </div>
                    <h5 class="card-title">Green</h5>
                    <div class="colors">
                        <div class="color" style="background-color: var(--green-dark-1)"></div>
                        <div class="color" style="background-color: var(--green-dark-2)"></div>
                        <div class="color" style="background-color: var(--green-1)"></div>
                        <div class="color" style="background-color: var(--green-2)"></div>
                        <div class="color" style="background-color: var(--green-3)"></div>
                        <div class="color" style="background-color: var(--green-4)"></div>
                        <div class="color" style="background-color: var(--green-5)"></div>
                        <div class="color" style="background-color: var(--green-light-1)"></div>
                        <div class="color" style="background-color: var(--green-light-2)"></div>
                    </div>
                    <h5 class="card-title">Red</h5>
                    <div class="colors">
                        <div class="color" style="background-color: var(--red-dark-1)"></div>
                        <div class="color" style="background-color: var(--red-dark-2)"></div>
                        <div class="color" style="background-color: var(--red-1)"></div>
                        <div class="color" style="background-color: var(--red-2)"></div>
                        <div class="color" style="background-color: var(--red-3)"></div>
                        <div class="color" style="background-color: var(--red-4)"></div>
                        <div class="color" style="background-color: var(--red-5)"></div>
                        <div class="color" style="background-color: var(--red-light-1)"></div>
                        <div class="color" style="background-color: var(--red-light-2)"></div>
                    </div>
                    <h5 class="card-title">Yellow</h5>
                    <div class="colors">
                        <div class="color" style="background-color: var(--yellow-dark-1)"></div>
                        <div class="color" style="background-color: var(--yellow-dark-2)"></div>
                        <div class="color" style="background-color: var(--yellow-1)"></div>
                        <div class="color" style="background-color: var(--yellow-2)"></div>
                        <div class="color" style="background-color: var(--yellow-3)"></div>
                        <div class="color" style="background-color: var(--yellow-4)"></div>
                        <div class="color" style="background-color: var(--yellow-5)"></div>
                        <div class="color" style="background-color: var(--yellow-light-1)"></div>
                        <div class="color" style="background-color: var(--yellow-light-2)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h4>Input Field</h4>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body fieldBlockContainer">
                    <h5 class="card-title">Text Field</h5>
                    <input type="text" class="inputElement" />
                    <input type="text" class="inputElement input-error" />
                    <input type="text" class="inputElement" disabled />
                    <h5 class="card-title">Reference Field</h5>
                    <div class="referencefield-wrapper "><input name="popupReferenceModule" type="hidden"
                            value="Accounts">
                        <div class="input-group">
                            <input name="account_id" type="hidden" value="" class="sourceField" data-displayvalue="" />
                            <input id="account_id_display" name="account_id_display" data-fieldname="account_id"
                                data-fieldtype="reference" type="text"
                                class="marginLeftZero autoComplete inputElement ui-autocomplete-input" value=""
                                placeholder="Nhập để tìm kiếm" autocomplete="off" />
                            <a href="#" class="clearReferenceSelection hide"><i class="far fa-times-circle"></i></a>
                            <span class="input-group-addon relatedPopup cursorPointer" title="Chọn">
                                <i id="Accounts_editView_fieldName_account_id_select" class="far fa-search"></i>
                            </span>
                        </div>
                        <span class="createReferenceRecord cursorPointer clearfix" title="Thêm mới">
                            <i id="Accounts_editView_fieldName_account_id_create" class="far fa-plus"></i>
                        </span>
                    </div>
                    <div class="referencefield-wrapper "><input name="popupReferenceModule" type="hidden"
                            value="Accounts">
                        <div class="input-group">
                            <input name="account_id" type="hidden" value="" class="sourceField" data-displayvalue="" />
                            <input id="account_id_display" name="account_id_display" data-fieldname="account_id"
                                data-fieldtype="reference" type="text"
                                class="marginLeftZero autoComplete inputElement ui-autocomplete-input input-error"
                                value="" placeholder="Nhập để tìm kiếm" autocomplete="off" />
                            <a href="#" class="clearReferenceSelection hide"><i class="far fa-times-circle"></i></a>
                            <span class="input-group-addon relatedPopup cursorPointer" title="Chọn">
                                <i id="Accounts_editView_fieldName_account_id_select" class="far fa-search"></i>
                            </span>
                        </div>
                        <span class="createReferenceRecord cursorPointer clearfix" title="Thêm mới">
                            <i id="Accounts_editView_fieldName_account_id_create" class="far fa-plus"></i>
                        </span>
                    </div>
                    <div class="referencefield-wrapper ">
                        <input name="popupReferenceModule" type="hidden" value="Accounts">
                        <div class="input-group">
                            <input name="parent_id" type="hidden" value="602" class="sourceField" data-displayvalue="" />
                            <input id="parent_id_display" name="parent_id_display" data-fieldname="parent_id" data-fieldtype="reference" type="text" class="marginLeftZero autoComplete inputElement ui-autocomplete-input" data-displayvalue="Công Ty Cổ Phần Giải Pháp Đóng Gói Hoàng Gia" value="Công Ty Cổ Phần Giải Pháp Đóng Gói Hoàng Gia" placeholder="Nhập để tìm kiếm" autocomplete="off" aria-invalid="false" readonly="readonly" readonly />
                            <a href="#" class="clearReferenceSelection">
                                <i class="far fa-times-circle"></i>
                            </a>
                            <span class="input-group-addon relatedPopup cursorPointer" title="Chọn">
                                <i id="Events_editView_fieldName_parent_id_select" class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                    <div class="referencefield-wrapper  selected">
                        <input name="popupReferenceModule" type="hidden" value="Accounts">
                        <div class="input-group">
                            <input name="parent_id" type="hidden" value="618" class="sourceField" data-displayvalue="Công Ty Cổ Phần Giải Pháp Đóng Gói Hoàng Gia" />
                            <input id="parent_id_display" name="parent_id_display" data-fieldname="parent_id" data-fieldtype="reference" type="text"
                                class="marginLeftZero autoComplete inputElement ui-autocomplete-input"
                                value="Công Ty Cổ Phần Giải Pháp Đóng Gói Hoàng Gia" placeholder="Nhập để tìm kiếm"
                                disabled="disabled" autocomplete="off" aria-invalid="false" value="Công ty TNHH Mega"
                            />
                            <a href="#" class="clearReferenceSelection ">
                                <i class="far fa-times-circle"></i>
                            </a>
                            <span class="input-group-addon relatedPopup cursorPointer" title="Chọn">
                                <i id="Events_editView_fieldName_parent_id_select" class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                    <h5 class="card-title">Picklist</h5>
                    <select data-fieldname="account" data-fieldtype="picklist" class="inputElement select2" type="picklist" name="account" data-selected-value=''>
                        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                        <option value="Apparel">Apparel</option>
                        <option value="Banking">Banking</option>
                        <option value="Biotechnology">Biotechnology</option>
                        <option value="Chemicals">Chemicals</option>
                        <option value="Communications">Communications</option>
                        <option value="Construction">Construction</option>
                        <option value="Consulting">Consulting</option>
                        <option value="Education">Education</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Energy">Energy</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Environmental">Environmental</option>
                        <option value="Finance">Finance</option>
                        <option value="Food & Beverage">Food & Beverage</option>
                        <option value="Government">Government</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Hospitality">Hospitality</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Machinery">Machinery</option>
                        <option value="Manufacturing">Manufacturing</option>
                        <option value="Media">Media</option>
                        <option value="Not For Profit">Not For Profit</option>
                        <option value="Recreation">Recreation</option>
                        <option value="Retail">Retail</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Technology">Technology</option>
                        <option value="Telecommunications">Telecommunications</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Other">Other</option>
                    </select>
                    <select data-fieldname="account" data-fieldtype="picklist" class="inputElement select2 input-error" type="picklist" name="account" data-selected-value=''>
                        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                        <option value="Apparel">Apparel</option>
                        <option value="Banking">Banking</option>
                        <option value="Biotechnology">Biotechnology</option>
                        <option value="Chemicals">Chemicals</option>
                        <option value="Communications">Communications</option>
                        <option value="Construction">Construction</option>
                        <option value="Consulting">Consulting</option>
                        <option value="Education">Education</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Energy">Energy</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Environmental">Environmental</option>
                        <option value="Finance">Finance</option>
                        <option value="Food & Beverage">Food & Beverage</option>
                        <option value="Government">Government</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Hospitality">Hospitality</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Machinery">Machinery</option>
                        <option value="Manufacturing">Manufacturing</option>
                        <option value="Media">Media</option>
                        <option value="Not For Profit">Not For Profit</option>
                        <option value="Recreation">Recreation</option>
                        <option value="Retail">Retail</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Technology">Technology</option>
                        <option value="Telecommunications">Telecommunications</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Other">Other</option>
                    </select>
                    <select data-fieldname="account" data-fieldtype="picklist" class="inputElement select2" type="picklist" name="account" data-selected-value='' disabled>
                        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                        <option value="Apparel">Apparel</option>
                        <option value="Banking">Banking</option>
                        <option value="Biotechnology">Biotechnology</option>
                        <option value="Chemicals">Chemicals</option>
                        <option value="Communications">Communications</option>
                        <option value="Construction">Construction</option>
                        <option value="Consulting">Consulting</option>
                        <option value="Education">Education</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Energy">Energy</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Environmental">Environmental</option>
                        <option value="Finance">Finance</option>
                        <option value="Food & Beverage">Food & Beverage</option>
                        <option value="Government">Government</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Hospitality">Hospitality</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Machinery">Machinery</option>
                        <option value="Manufacturing">Manufacturing</option>
                        <option value="Media">Media</option>
                        <option value="Not For Profit">Not For Profit</option>
                        <option value="Recreation">Recreation</option>
                        <option value="Retail">Retail</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Technology">Technology</option>
                        <option value="Telecommunications">Telecommunications</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Other">Other</option>
                    </select>
                    <h5>Checkbox</h5>
                    <input id="Accounts_editView_fieldName_notify_owner" class="inputElement" style="width:15px;height:15px;" data-fieldname="notify_owner" data-fieldtype="checkbox" type="checkbox" name="notify_owner">
                    <input checked id="Accounts_editView_fieldName_notify_owner" class="inputElement" style="width:15px;height:15px;" data-fieldname="notify_owner" data-fieldtype="checkbox" type="checkbox" name="notify_owner">
                    <input id="Accounts_editView_fieldName_notify_owner" class="inputElement input-error" style="width:15px;height:15px;" data-fieldname="notify_owner" data-fieldtype="checkbox" type="checkbox" name="notify_owner">
                    <input id="Accounts_editView_fieldName_notify_owner" class="inputElement" style="width:15px;height:15px;" data-fieldname="notify_owner" data-fieldtype="checkbox" type="checkbox" name="notify_owner" disabled>
                    <h5>Radio Button</h5>
                    <input name="radio" class="inputElement" value="1" type="radio" />
                    <input name="radio" class="inputElement" value="1" type="radio" checked />
                    <input name="radio" class="inputElement input-error" value="2" type="radio" />
                    <input name="radio" class="inputElement" disabled value="3" type="radio" />
                    <h5>Currency</h5>
                    <div class="referencefield-wrapper ">
                        <div class="input-group"><span class="input-group-addon">₫</span><input id="Accounts_editView_fieldName_annual_revenue" type="text" class="inputElement currencyField" onkeyup="formatNumber (this, 'float')" maxlength="22" data-field-type="currency" name="annual_revenue" value="" data-rule-currency="true"></div>
                    </div>
                    <div class="referencefield-wrapper ">
                        <div class="input-group"><span class="input-group-addon">₫</span><input id="Accounts_editView_fieldName_annual_revenue" type="text" class="inputElement currencyField input-error" onkeyup="formatNumber (this, 'float')" maxlength="22" data-field-type="currency" name="annual_revenue" value="" data-rule-currency="true"></div>
                    </div>
                    <div class="referencefield-wrapper ">
                        <div class="input-group"><span class="input-group-addon">₫</span><input id="Accounts_editView_fieldName_annual_revenue" type="text" class="inputElement currencyField input-error" onkeyup="formatNumber (this, 'float')" maxlength="22" data-field-type="currency" name="annual_revenue" value="" data-rule-currency="true" disabled></div>
                    </div>
                    <h5>Date Field</h5>
                    <div class="input-group inputElement" style="margin-bottom: 3px">
                        <input id="Events_editView_fieldName_date_start" type="text" class="dateField form-control " data-fieldname="date_start" data-fieldtype="date" name="date_start" data-date-format="dd-mm-yyyy" value="11-05-2021" data-rule-required="true" data-rule-date="true" aria-required="true" />
                        <span class="input-group-addon"><i class="far fa-calendar "></i></span>
                    </div>
                    <h5>Time Field</h5>
                    <div class="input-group inputElement time">
                        <input id="Events_editView_fieldName_time_start" type="text" data-format="12" class="timepicker-default form-control ui-timepicker-input" value="11:58:09" name="time_start" data-rule-required="true" data-rule-time="true" autocomplete="off" aria-required="true" aria-invalid="false">
                        <span class="input-group-addon" style="width: 30px;"><i class="far fa-clock"></i></span>
                    </div>
                    <h5>Switch</h5>
                    <input type="checkbox" class="bootstrap-switch" />
                </div>
            </div>
        </div>
    </div>
    <h4>Button</h4>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Contained Button</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-primary card-link">Button</button>
                    <button class="btn btn-primary card-link" disabled>Button</button>
                    <button class="btn btn-secondary card-link">Button</button>
                    <button class="btn btn-secondary card-link" disabled>Button</button>
                    <button class="btn btn-success card-link">Button</button>
                    <button class="btn btn-success card-link" disabled>Button</button>
                    <button class="btn btn-danger card-link">Button</button>
                    <button class="btn btn-danger card-link" disabled>Button</button>
                    <button class="btn btn-warning card-link">Button</button>
                    <button class="btn btn-warning card-link" disabled>Button</button>
                    <button class="btn btn-info card-link">Button</button>
                    <button class="btn btn-info card-link" disabled>Button</button>
                    <button class="btn btn-light card-link">Button</button>
                    <button class="btn btn-light card-link" disabled>Button</button>
                    <button class="btn btn-dark card-link">Button</button>
                    <button class="btn btn-dark card-link" disabled>Button</button>
                    <h6>Button input tag</h6>
                    <input type="button" class="btn btn-primary card-link" value="Button">
                    <input type="button" class="btn btn-primary card-link" disabled value="Button">
                    <input type="button" class="btn btn-secondary card-link" value="Button">
                    <input type="button" class="btn btn-secondary card-link" disabled value="Button">
                    <input type="button" class="btn btn-success card-link" value="Button">
                    <input type="button" class="btn btn-success card-link" disabled value="Button">
                    <input type="button" class="btn btn-danger card-link" value="Button">
                    <input type="button" class="btn btn-danger card-link" disabled value="Button">
                    <input type="button" class="btn btn-warning card-link" value="Button">
                    <input type="button" class="btn btn-warning card-link" disabled value="Button">
                    <input type="button" class="btn btn-info card-link" value="Button">
                    <input type="button" class="btn btn-info card-link" disabled value="Button">
                    <input type="button" class="btn btn-light card-link" value="Button">
                    <input type="button" class="btn btn-light card-link" disabled value="Button">
                    <input type="button" class="btn btn-dark card-link" value="Button">
                    <input type="button" class="btn btn-dark card-link" disabled value="Button">
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-primary card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-primary card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-secondary card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-secondary card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-success card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-success card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-danger card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-danger card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-warning card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-warning card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-info card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-info card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-light card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-light card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-dark card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-dark card-link" disabled>Button</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Contained Button (Shadowed)</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-primary btn-shadowed card-link">Button</button>
                    <button class="btn btn-primary btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-secondary btn-shadowed card-link">Button</button>
                    <button class="btn btn-secondary btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-success btn-shadowed card-link">Button</button>
                    <button class="btn btn-success btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-danger btn-shadowed card-link">Button</button>
                    <button class="btn btn-danger btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-warning btn-shadowed card-link">Button</button>
                    <button class="btn btn-warning btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-info btn-shadowed card-link">Button</button>
                    <button class="btn btn-info btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-light btn-shadowed card-link">Button</button>
                    <button class="btn btn-light btn-shadowed card-link" disabled>Button</button>
                    <button class="btn btn-dark btn-shadowed card-link">Button</button>
                    <button class="btn btn-dark btn-shadowed card-link" disabled>Button</button>
                    <h6>Button input tag</h6>
                    <input type="button" class="btn btn-primary btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-primary btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-secondary btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-secondary btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-success btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-success btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-danger btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-danger btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-warning btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-warning btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-info btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-info btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-light btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-light btn-shadowed card-link" disabled value="Button">
                    <input type="button" class="btn btn-dark btn-shadowed card-link" value="Button">
                    <input type="button" class="btn btn-dark btn-shadowed card-link" disabled value="Button">
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-primary btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-primary btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-secondary btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-secondary btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-success btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-success btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-danger btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-danger btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-warning btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-warning btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-info btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-info btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-light btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-light btn-shadowed card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-dark btn-shadowed card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-dark btn-shadowed card-link" disabled>Button</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Outlined Button</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-outline-primary card-link">Button</button>
                    <button class="btn btn-outline-primary card-link" disabled>Button</button>
                    <button class="btn btn-outline-secondary card-link">Button</button>
                    <button class="btn btn-outline-secondary card-link" disabled>Button</button>
                    <button class="btn btn-outline-success card-link">Button</button>
                    <button class="btn btn-outline-success card-link" disabled>Button</button>
                    <button class="btn btn-outline-danger card-link">Button</button>
                    <button class="btn btn-outline-danger card-link" disabled>Button</button>
                    <button class="btn btn-outline-warning card-link">Button</button>
                    <button class="btn btn-outline-warning card-link" disabled>Button</button>
                    <button class="btn btn-outline-info card-link">Button</button>
                    <button class="btn btn-outline-info card-link" disabled>Button</button>
                    <button class="btn btn-outline-light card-link">Button</button>
                    <button class="btn btn-outline-light card-link" disabled>Button</button>
                    <button class="btn btn-outline-dark card-link">Button</button>
                    <button class="btn btn-outline-dark card-link" disabled>Button</button>
                    <h6>Button input tag</h6>
                    <input type="button" class="btn btn-outline-primary card-link" value="Button">
                    <input type="button" class="btn btn-outline-primary card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-secondary card-link" value="Button">
                    <input type="button" class="btn btn-outline-secondary card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-success card-link" value="Button">
                    <input type="button" class="btn btn-outline-success card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-danger card-link" value="Button">
                    <input type="button" class="btn btn-outline-danger card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-warning card-link" value="Button">
                    <input type="button" class="btn btn-outline-warning card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-info card-link" value="Button">
                    <input type="button" class="btn btn-outline-info card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-light card-link" value="Button">
                    <input type="button" class="btn btn-outline-light card-link" disabled value="Button">
                    <input type="button" class="btn btn-outline-dark card-link" value="Button">
                    <input type="button" class="btn btn-outline-dark card-link" disabled value="Button">
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-outline-primary card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-primary card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-secondary card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-secondary card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-success card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-success card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-danger card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-danger card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-warning card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-warning card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-info card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-info card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-light card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-light card-link" disabled>Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-dark card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-outline-dark card-link" disabled>Button</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Text Button with icon</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-link card-link"><i class="far fa-plus"></i> Button</button>
                    <button class="btn btn-link card-link" disabled><i class="far fa-plus"></i> Button</button>
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-link card-link"><i class="far fa-plus"></i> Button</a>
                    <a href="javascript:void(0)" class="btn btn-link card-link" disabled><i class="far fa-plus"></i>
                        Button</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Icon Button</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-link card-link"><i class="far fa-pen"></i></button>
                    <button class="btn btn-link card-link" disabled><i class="far fa-pen"></i></button>
                    <button class="btn btn-link card-link"><i class="far fa-comment"></i></button>
                    <button class="btn btn-link card-link" disabled><i class="far fa-comment"></i></button>
                    <button class="btn btn-link-danger card-link"><i class="far fa-trash-alt"></i></button>
                    <button class="btn btn-link-danger card-link" disabled><i class="far fa-trash-alt"></i></button>
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-link card-link">
                        <i class="far fa-pen"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-link card-link" disabled>
                        <i class="far fa-pen"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-link card-link"><i class="far fa-comment"></i></a>
                    <a href="javascript:void(0)" class="btn btn-link card-link" disabled>
                        <i class="far fa-comment"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-link-danger card-link">
                        <i class="far fa-trash-alt"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-link-danger card-link" disabled>
                        <i class="far fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Outlined Icon Button</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-outline-primary card-link"><i class="far fa-pen"></i></button>
                    <button class="btn btn-outline-primary card-link" disabled><i class="far fa-pen"></i></button>
                    <button class="btn btn-outline-primary card-link"><i class="far fa-comment"></i></button>
                    <button class="btn btn-outline-primary card-link" disabled><i class="far fa-comment"></i></button>
                    <button class="btn btn-outline-danger card-link"><i class="far fa-trash-alt"></i></button>
                    <button class="btn btn-outline-danger card-link" disabled><i class="far fa-trash-alt"></i></button>
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-outline-primary card-link">
                        <i class="far fa-pen"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-outline-primary card-link" disabled>
                        <i class="far fa-pen"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-outline-primary card-link">
                        <i class="far fa-comment"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-outline-primary card-link" disabled>
                        <i class="far fa-comment"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-outline-danger card-link">
                        <i class="far fa-trash-alt"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-outline-danger card-link" disabled>
                        <i class="far fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Text Button</h5>
                    <h6>Button button tag</h6>
                    <button class="btn btn-link card-link">Button</button>
                    <button class="btn btn-link card-link" disabled>Button</button>
                    <h6>Button input tag</h6>
                    <input type="button" class="btn btn-link card-link" value="Button">
                    <input type="button" class="btn btn-link card-link" disabled value="Button">
                    <h6>Button link tag</h6>
                    <a href="javascript:void(0)" class="btn btn-link card-link">Button</a>
                    <a href="javascript:void(0)" class="btn btn-link card-link" disabled>Button</a>
                </div>
            </div>
        </div>
    </div>
</div>
{/strip}