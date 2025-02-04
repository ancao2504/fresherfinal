{*
    Name: ChatbotIframe.tpl
    Author: Phu Vo
    Date: 2020.04.06
*}

{if $IFRAME_DATA.bot_name == 'Hana'}
    <style>
        html {
            font-size: 16px;
        }
    </style>
{else if $IFRAME_DATA.bot_name == 'BotBanHang'}
    <style>
        html {
            font-size: 14px;
        }
    </style>
{/if}

<b-overlay :show="overlay" id="app" :class="bot_name" style="display: none">

    {* CUSTOMER TYPE START FROM HERE *}
    <div class="position-absolute customer-type" v-show="typeof customer_data.record_module != 'undefined'">
        <span v-show="customer_data.record_module === 'Contacts'">{vtranslate('Contacts', 'Contacts')}</span>
        <span v-show="customer_data.record_module === 'Leads'">{vtranslate('Leads', 'Leads')}</span>
        <span v-show="customer_data.record_module === 'CPTarget'">{vtranslate('LBL_CHATBOT_TARGET', $MODULE)}</span>
    </div>
    {* CUSTOMER TYPE END HERE *}

    <div class="position-absolute refresh-iframe" onclick="javascript:window.location.reload()">
        <span><i class="fa fa-refresh" aria-hidden="true" style="margin-right: 4px;"></i> Refresh</span>
    </div>

    <b-container fluid>
        <div class="header">
            <div v-show="typeof customer_data.record_id === 'undefined'" class="mt-5"></div>
            <b-row class="mt-2">
                <b-col cols="auto" class="mx-auto position-relative">
                    <b-avatar :src="customer_data.avatar || 'resources/images/no_ava.png'" size="4em" class="mx-auto"></b-avatar>
                    <button v-if="customer_data.record_module === 'Contacts'" class="btn btn-circle btn-upload-avatar position-absolute" @click="openUploadAvatarModal"><i class="fa fa-picture-o" aria-hidden="true"></i></button>
                    <b-modal v-if="customer_data.record_module === 'Contacts'" ref="uploadAvatarModal" title="{vtranslate('LBL_HANA_UPDATE_CUSTOMER_AVATAR', $MODULE)}" :cancel-title="global.app.vtranslate('JS_LBL_CANCEL')" :ok-title="global.app.vtranslate('JS_LBL_SAVE')" @ok="submitAvatar">
                        <b-overlay :show="uploading_avatar">
                            <b-form name="avatar">
                                <b-form-row>
                                    <b-col cols="12">
                                        <div class="customer-avatar-wrapper">
                                            <b-img :src="form_data.Avatar.imagename || 'resources/images/no_ava.png'" style="width: 100%" alt="Avatar"></b-img>
                                            <input name="imagename[]"
                                                ref="avatarInput"
                                                type="file"
                                                accept="image/*"
                                                class="form-control-file"
                                                style="display: none"
                                                @change="updateAvatarPreviewFile"
                                            />
                                            <div class="upload-button" @click="$refs.avatarInput.click()">
                                                <h6><i class="fa fa-file-image-o" aria-hidden="true"></i> {vtranslate('LBL_HANA_SELECT_FROM_FILE', $MODULE)}</h6>
                                            </div>
                                        </div>
                                    </b-col>
                                </b-form-row>
                            </b-form>
                        </b-overlay>
                    </b-modal>
                </b-col>
            </b-row>
            <b-row class="mt-2">
                <b-col cols="auto" class="mx-auto">
                    <label v-if="customer_display.record_id">
                        <a :href="{literal}customer_data && `index.php?module=${customer_data.record_module}&view=Detail&record=${customer_data.record_id}`{/literal}" target="_black">
                            <strong v-html="customer_display.full_name"></strong>
                        </a>
                    </label>
                    <label v-if="!customer_display.record_id && customer_display.full_name"><strong v-html="customer_display.full_name"></strong></label>
                    <label v-if="!customer_display.record_id && !customer_display.full_name"><strong>{vtranslate('LBL_HANA_UNIDENTIFIED', $MODULE)}</strong></label>
                </b-col>
            </b-row>
        </div>

        {* NEW CUSTOMER HOLDER START HERE *}
        <div class="body" v-show="typeof customer_data.record_id === 'undefined'">
            <br />
            <b-row>
                <b-col cols="auto" class="text-center mx-auto">
                    <b-button variant="outline-primary" @click="openCreateCustomerWindow()">{vtranslate('LBL_HANA_ADD_TO_CRM', $MODULE)}</b-button>
                </b-col>
            </b-row>
        </div>
        {* NEW CUSTOMER HOLDER END HERE *}

        {* EXISXT CUSTOMER HOLDER START HERE *}
        <div class="body" v-show="typeof customer_data.record_id !== 'undefined'">
            <b-tabs class="fancyScrollbar">

                {* CUSTOMER INFORMATION START FORM HERE *}
                <b-tab active title="{vtranslate('LBL_HANA_CUSTOMER_INFORMATION', $MODULE)}">
                    <b-form id="customer" v-show="modes.customer === 'detail' || modes.customer === 'edit'">
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Email', 'Contacts')}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'email')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly" v-html="customer_display.email"></div>
                                <b-form-input :data-rule-required="isRequired(customer_data.record_module, 'email')" v-show="modes.customer === 'edit'" name="email" v-model="form_data.customer.email" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Mobile', 'Contacts')}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'mobile')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly" v-html="customer_display.mobile"></div>
                                <b-form-input type="number" :data-rule-required="isRequired(customer_data.record_module, 'mobile')" v-show="modes.customer === 'edit'" v-model="form_data.customer.mobile" class="mt-2"></b-form-input>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3" v-show="customer_data.record_module === 'Contacts'"><strong>{vtranslate('LBL_HANA_COMPANY', $MODULE)}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'account_id')" class="text-danger"> *</span></strong></label>
                                <label class="mt-3" v-show="customer_data.record_module === 'Leads'"><strong>{vtranslate('LBL_HANA_COMPANY', $MODULE)}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'company')" class="text-danger"> *</span></strong></label>
                                <label class="mt-3" v-show="customer_data.record_module === 'CPTarget'"><strong>{vtranslate('LBL_HANA_COMPANY', $MODULE)}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'company')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail' && customer_data.record_module === 'Leads'" class="form-control mt-2 readonly max-2" v-html="customer_display.company"></div>
                                <b-form-input :data-rule-required="isRequired(customer_data.record_module, 'company')" name="company" v-show="modes.customer === 'edit' && customer_data.record_module === 'Leads'" name="company" v-model="form_data.customer.company" class="mt-2"></b-form-input>
                                <div v-show="modes.customer === 'detail' && customer_data.record_module === 'CPTarget'" class="form-control mt-2 readonly max-2" v-html="customer_display.company"></div>
                                <b-form-input :data-rule-required="isRequired(customer_data.record_module, 'company')" name="company" v-show="modes.customer === 'edit' && customer_data.record_module === 'CPTarget'" name="company" v-model="form_data.customer.company" class="mt-2"></b-form-input>
                                <div v-show="modes.customer === 'detail' && customer_data.record_module === 'Contacts'" class="form-control mt-2 readonly max-2" v-html="customer_display.account_id"></div>
                                <parent-record :endpoint="getEntryPointUrl('index.php?module=Contacts&search_module=Accounts&action=BasicAjax')" :submit-url="getEntryPointUrl('index.php')" :data-rule-required="isRequired(customer_data.record_module, 'account_id')" name="account_id" v-if="modes.customer === 'edit' && customer_data.record_module === 'Contacts'" v-model="form_data.customer.account_id" :parent-id="form_data.customer.account_id" :parent-name="customer_display.account_id" parent-module="Accounts" module="Contacts" class="mt-2"></parent-record>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3" v-if="customer_data.record_module === 'Contacts'"><strong>{vtranslate('LBL_HANA_ADDRESS', $MODULE)}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'mailingstreet')" class="text-danger"> *</span></strong></label>
                                <label class="mt-3" v-if="customer_data.record_module === 'Leads'"><strong>{vtranslate('LBL_HANA_ADDRESS', $MODULE)}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'lane')" class="text-danger"> *</span></strong></label>
                                <label class="mt-3" v-if="customer_data.record_module === 'CPTarget'"><strong>{vtranslate('LBL_HANA_ADDRESS', $MODULE)}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'lane')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8" v-if="customer_data.record_module === 'Contacts'">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly max-2">
                                    <a v-if="customer_display.full_address"
                                    v-b-tooltip.hover.noninteractive :title="customer_display.full_address"
                                    :href="'https://www.google.com/maps/place/' + encodeURI(customer_display.full_address)"
                                    target="_blank" v-html="customer_display.full_address"
                                ></a>
                                </div>
                                <b-form-input :data-rule-required="isRequired(customer_data.record_module, 'mailingstreet')" name="mailingstreet" v-show="modes.customer === 'edit'" placeholder="{vtranslate('Mailing Street', 'Contacts')}" v-model="form_data.customer.mailingstreet" class="mt-2"></b-form-input>
                                <b-form-input v-show="modes.customer === 'edit'" placeholder="{vtranslate('Mailing State', 'Contacts')}" v-model="form_data.customer.mailingstate" class="mt-2"></b-form-input>
                                <b-form-input v-show="modes.customer === 'edit'" placeholder="{vtranslate('Mailing City', 'Contacts')}" v-model="form_data.customer.mailingcity" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="8" v-if="customer_data.record_module === 'Leads'">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly max-2">
                                    <a v-if="customer_display.full_address"
                                    v-b-tooltip.hover.noninteractive :title="customer_display.full_address"
                                    :href="'https://www.google.com/maps/place/' + encodeURI(customer_display.full_address)"
                                    target="_blank" v-html="customer_display.full_address"
                                ></a>
                                </div>
                                <b-form-input :data-rule-required="isRequired(customer_data.record_module, 'lane')" name="lane" v-show="modes.customer === 'edit'" placeholder="{vtranslate('Street', 'Leads')}" v-model="form_data.customer.lane" class="mt-2"></b-form-input>
                                <b-form-input v-show="modes.customer === 'edit'" placeholder="{vtranslate('State', 'Leads')}" v-model="form_data.customer.state" class="mt-2"></b-form-input>
                                <b-form-input v-show="modes.customer === 'edit'" placeholder="{vtranslate('City', 'Leads')}" v-model="form_data.customer.city" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="8" v-if="customer_data.record_module === 'CPTarget'">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly max-2">
                                    <a v-if="customer_display.full_address"
                                    v-b-tooltip.hover.noninteractive :title="customer_display.full_address"
                                    :href="'https://www.google.com/maps/place/' + encodeURI(customer_display.full_address)"
                                    target="_blank" v-html="customer_display.full_address"
                                ></a>
                                </div>
                                <b-form-input :data-rule-required="isRequired(customer_data.record_module, 'lane')" name="lane" v-show="modes.customer === 'edit'" placeholder="{vtranslate('Street', 'CPTarget')}" v-model="form_data.customer.lane" class="mt-2"></b-form-input>
                                <b-form-input v-show="modes.customer === 'edit'" placeholder="{vtranslate('State', 'CPTarget')}" v-model="form_data.customer.state" class="mt-2"></b-form-input>
                                <b-form-input v-show="modes.customer === 'edit'" placeholder="{vtranslate('City', 'CPTarget')}" v-model="form_data.customer.city" class="mt-2"></b-form-input>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-if="customer_data.record_module === 'Contacts'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Birthdate', 'Contacts')}:<span v-show="isRequired(customer_data.record_module, 'mailingstreet')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly" v-html="customer_display.birthday"></div>
                                <date-picker data-type="date" name="birthday" data-rule-less-than-today="true" :data-rule-required="isRequired(customer_data.record_module, 'mailingstreet')" v-show="modes.customer === 'edit'" v-model="form_data.customer.birthday" :config="datepicker_options" class="mt-2"></date-picker>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Title', 'Contacts')}:<span v-show="isRequired(customer_data.record_module, 'title')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly" v-html="customer_display.title"></div>
                                <b-form-input name="title" :data-rule-required="isRequired(customer_data.record_module, 'title')" v-show="modes.customer === 'edit'" v-model="form_data.customer.title" class="mt-2"></b-form-input>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-if="customer_data.record_module === 'Leads'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Lead Status', 'Leads')}:<span v-show="isRequired(customer_data.record_module, 'leadstatus')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail' || form_data.customer.leadstatus == 'Converted'" class="form-control mt-2 readonly" v-html="customer_display.leadstatus"></div>
                                <b-form-select name="leadstatus" :data-rule-required="isRequired(customer_data.record_module, 'leadstatus')" v-show="modes.customer === 'edit' && form_data.customer.leadstatus != 'Converted'" :options="meta_data.Leads.picklist_fields.leadstatus" v-model="form_data.customer.leadstatus" class="mt-2"></b-form-select>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Description', 'Leads')}:<span v-show="isRequired(customer_data.record_module, 'description')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly max-2" v-html="customer_display.description"></div>
                                <b-form-textarea name="description" :data-rule-required="isRequired(customer_data.record_module, 'description')" v-show="modes.customer === 'edit'" v-model="form_data.customer.description" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-if="customer_data.record_module === 'CPTarget'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Description', 'CPTarget')}:<span v-show="isRequired(customer_data.record_module, 'description')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly max-2" v-html="customer_display.description"></div>
                                <b-form-textarea name="description" :data-rule-required="isRequired(customer_data.record_module, 'description')" v-show="modes.customer === 'edit'" v-model="form_data.customer.description" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Lead Source', 'Contacts')}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'leadsource')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly" v-html="customer_display.leadsource"></div>
                                <b-select :data-rule-required="isRequired('Contacts', 'leadsource')" v-show="modes.customer === 'edit'" name="leadsource" :options="meta_data.Contacts.picklist_fields.leadsource" v-model="form_data.customer.leadsource" class="mt-2"></b-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Assigned To', 'Contacts')}:<span v-show="modes.customer === 'edit' && isRequired(customer_data.record_module, 'assigned_user_id')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <div v-show="modes.customer === 'detail'" class="form-control mt-2 readonly" v-html="customer_display.assigned_user_id"></div>
                                <multiple-owner :endpoint="getEntryPointUrl('index.php?module=Vtiger&action=HandleOwnerFieldAjax&mode=loadOwnerList')" :data-rule-required="isRequired(customer_data.record_module, 'assigned_user_id')" name="assigned_user_id" data-rule-main-owner="true" v-if="modes.customer === 'edit'" v-model="form_data.customer.assigned_user_id" :selected-tags="customer_data.assigned_user_id" class="mt-2"></multiple-owner>
                            </b-col>
                        </b-form-row>
                    </b-form>
                    <hr class="mt-2 mb-0" v-show="modes.customer === 'detail'" />
                    <b-form id="comment" v-show="modes.customer === 'detail'">
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_COMMENT', $MODULE)}: <span class="text-danger">*</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea data-rule-required="true" name="commentcontent" v-model="form_data.comment.commentcontent" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4"></b-col>
                            <b-col cols="auto" class="mr-auto">
                                <b-button @click="openChatbotIframeRelatedListPopup('ModComments')" variant="link" class="mr-2 mt-2">{vtranslate('LBL_HANA_RELATED_COMMENTS', $MODULE)}</b-button>
                            </b-col>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="primary" @click="saveComment" class="ml-2 mt-2">{vtranslate('LBL_HANA_POST_COMMENT', $MODULE)}</b-button>
                            </b-col>
                        </b-form-row>
                    </b-form>
                    <b-form id="salesorder" class="salesorder-form" v-show="customer_data.record_module === 'Leads' && modes.customer === 'salesorder'">
                        <h6 class="mt-3 form-block">{vtranslate('LBL_HANA_SALES_ORDER_DETAIL', $MODULE)}</h6>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_SELECT_PRODUCT', $MODULE)}:</strong></label>
                            </b-col>
                            <b-col cols="8" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_PRODUCT_PLACEHOLDER', $MODULE)}">
                                <select-product module="Products" v-model="form_data.SalesOrder.select_product" v-bind:ignores="form_data.SalesOrder.ignores" class="mt-2"></select-product>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_SELECT_SERVICE', $MODULE)}:</strong></label>
                            </b-col>
                            <b-col cols="8" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_SERVICE_PLACEHOLDER', $MODULE)}">
                                <select-product module="Services" v-model="form_data.SalesOrder.select_product" v-bind:ignores="form_data.SalesOrder.ignores" class="mt-2"></select-product>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-0" />
                        <table style="width: 100%">
                            <tr v-for="(item, index) in form_data.SalesOrder.items" :key="item.id">
                                <td class="product-name">
                                    <label v-b-tooltip.hover.noninteractive :title="item.label" class="mt-3"><strong v-html="item.label"></strong></label>
                                    <b-button v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_DELETE')}" size="sm" variant="outline-danger" class="mr-1 delete-product" @click="removeProduct(item.id)"><i class="fa fa-close" aria-hidden="true"></i></b-button>
                                </td>
                                <td class="fieldValue quantity" v-b-tooltip.hover.noninteractive title="{vtranslate('Quantity', 'SalesOrder')}">
                                    <b-form-input type="number" min="0" class="mt-2 quantity" v-model="item.quantity" @change="updateQuantity(item)"></b-form-input>
                                </td>
                                <td class="fieldValue price" v-b-tooltip.hover.noninteractive title="{vtranslate('List Price', 'SalesOrder')}">
                                    <div class="flex-wraper">
                                        <div class="form-control readonly mt-2" v-html="item.price && formatCurrency(item.price)"></div>
                                    </div>
                                </td>
                                <td class="fieldValue total" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_ITEM_TOTAL_PRICE', $MODULE)}">
                                    <div class="flex-wraper">
                                        <div class="form-control readonly mt-2" v-html="item.total && formatCurrency(item.total)"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <hr class="mt-2 mb-0" v-if="form_data.SalesOrder.items && form_data.SalesOrder.items.length > 0" />
                        <b-form-row>
                            <b-col sm="6" class="d-none d-sm-block"></b-col>
                            <b-col cols="12" sm="6">
                                <b-form-row>
                                    <b-col cols="3" class="text-right">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_PRETAX_PRICE', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="9" class="text-right mt-3">
                                        <label v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_PRETAX_PRICE', $MODULE)}" v-html="form_data.SalesOrder.total && global.app.convertCurrencyToUserFormat(form_data.SalesOrder.total, true)"></label>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" class="text-right">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_DISCOUNT', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-form-input v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_DISCOUNT_PERCENT', $MODULE)}" type="number" min="0" data-rule-optional-less-than-or-equal="100" v-model="form_data.SalesOrder.discount_percent" @change="calcDiscountAmount" class="mt-2"></b-form-input>
                                    </b-col>
                                    <b-col cols="6" class="fieldValue discount_amount">
                                        <b-form-input v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_DISCOUNT_AMOUNT', $MODULE)}" v-model="form_data.SalesOrder.discount_amount" @change="calcDiscountPercent" @keyup="formatDiscountAmount" class="mt-2"></b-form-input>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" class="text-right">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_TAX', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="3" class="fieldValue tax_percent">
                                        <b-form-select v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_TAX_PERCENT', $MODULE)}" :options="meta_data.SalesOrder.picklist_fields.tax" v-model="form_data.SalesOrder.tax_percent" @change="calcTaxAmount" class="mt-2"></b-form-select>
                                    </b-col>
                                    <b-col cols="6" class="fieldValue tax_amount">
                                        <b-form-input v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_TAX_AMOUNT', $MODULE)}" readonly v-model="form_data.SalesOrder.tax_amount" class="mt-2"></b-form-input>
                                    </b-col>
                                </b-form-row>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-0" />
                        <h6 class="mt-3 form-block">{vtranslate('LBL_HANA_SALES_ORDER_GENERAL_INFORMATION', $MODULE)}</h6>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Status', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'sostatus')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-select :data-rule-required="isRequired('SalesOrder', 'sostatus')" name="sostatus" :options="meta_data.SalesOrder.picklist_fields.sostatus" v-model="form_data.SalesOrder.sostatus" class="mt-2"></b-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Description', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'description')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea name="description" :data-rule-required="isRequired('SalesOrder', 'description')" class="mt-2" v-model="form_data.SalesOrder.description"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-0" />
                        <h6 class="mt-3 form-block">{vtranslate('LBL_HANA_SALES_ORDER_ADDRESS_INFORMATION', $MODULE)}</h6>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Shipping Address', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'ship_street')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea :data-rule-required="isRequired('SalesOrder', 'ship_street')" name="ship_street" v-model="form_data.SalesOrder.ship_street" class="mt-2"></b-form-textarea>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_RECEIVER_NAME', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'receiver_name')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input :data-rule-required="isRequired('SalesOrder', 'receiver_name')" name="receiver_name" v-model="form_data.SalesOrder.receiver_name" class="mt-2"></b-form-input>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_RECEIVER_PHONE', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'receiver_phone')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input type="number" :data-rule-required="isRequired('SalesOrder', 'receiver_phone')" name="receiver_phone" v-model="form_data.SalesOrder.receiver_phone" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="12">
                                <b-form-checkbox v-model="form_data.SalesOrder.issue_invoice" value="1" unchecked-value="0" class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_INVOICE', $MODULE)}</strong></b-form-checkbox>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-show="form_data.SalesOrder.issue_invoice == '1'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Account Name', 'SalesOrder')}:<span class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input data-rule-required="true" name="company" name="company" v-model="form_data.SalesOrder.company" class="mt-2"></b-form-input>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-show="form_data.SalesOrder.issue_invoice == '1'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Billing Address', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'bill_street')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea :data-rule-required="isRequired('SalesOrder', 'bill_street')" name="bill_street" v-model="form_data.SalesOrder.bill_street" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                    </b-form>
                    <div class="fixed-footer container-fluid" v-show="modes.customer !== 'salesorder'">
                        <div class="footer-content">
                            <b-row>
                                <b-col cols="auto" class="mx-auto" v-show="modes.customer === 'edit'">
                                    <b-button variant="primary" class="ml-2 mt-2" @click="saveCustomer">{vtranslate('LBL_SAVE')}</b-button>
                                    <b-button @click="toggleMode('customer', 'detail')" variant="outline-danger" class="ml-2 mt-2">{vtranslate('LBL_CANCEL')}</b-button>
                                </b-col>
                                <b-col cols="auto" class="mr-auto" v-show="customer_data.record_module === 'Leads' && modes.customer === 'detail'">
                                    <b-button @click="toggleMode('customer', 'salesorder')" variant="link" class="mr-2 mt-2"><i class="fa fa-plus" aria-hidden="true"></i> {vtranslate('LBL_HANA_CREATE_SALES_ORDER', $MODULE)}</b-button>
                                </b-col>
                                <b-col cols="auto" class="ml-auto" v-show="modes.customer === 'detail'">
                                    <b-button @click="toggleMode('customer', 'edit')" variant="link" class="ml-2 mt-2"><i class="fa fa-pencil" aria-hidden="true"></i> {vtranslate('LBL_HANA_EDIT_INFORMATION', $MODULE)}</b-button>
                                </b-col>
                            </b-row>
                        </div>
                    </div>
                    <div class="fixed-footer footer-lg container-fluid" v-show="modes.customer === 'salesorder'">
                        <div class="footer-content">
                            <b-row>
                                <b-col cols="auto" class="mr-auto">
                                    <h6 class="mt-2 mb-0"><strong>{vtranslate('LBL_HANA_SALES_ORDER_TOTAL_AMOUNT', $MODULE)}:</strong></h6>
                                </b-col>
                                <b-col cols="auto" class="ml-auto">
                                    <h6 class="text-danger mt-2 mb-0" v-html="form_data.SalesOrder.total && global.app.convertCurrencyToUserFormat(form_data.SalesOrder.grand_total, true)"></h6>
                                </b-col>
                            </b-row>
                            <hr class="mt-2 mb-0" />
                            <b-row>
                                <b-col cols="auto" class="mx-auto">
                                    <b-button variant="primary" @click="submitSalesOrderForLead" class="ml-2 mt-2">{vtranslate('LBL_HANA_CREATE_SALES_ORDER', $MODULE)}</b-button>
                                    <b-button v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_CANCEL_WARNING', $MODULE)}" variant="outline-danger" class="ml-2 mt-2" @click="toggleMode('customer', 'detail')">{vtranslate('LBL_CANCEL')}</b-button>
                                </b-col>
                            </b-row>
                        </div>
                    </div>
                    <div class="footer-padding"></div>
                </b-tab>
                {* CUSTOMER INFORMATION END HERE *}

                <b-tab v-if="false && customer_data.record_module === 'Leads'" @click="loadRelatedList('CPEventRegistration')">
                    <template v-slot:title>
                        {vtranslate('CPEventRegistration', 'CPEventRegistration')} <b-badge v-if="counters.CPEventRegistration > 0" v-html="counters.CPEventRegistration" variant="danger"></b-badge>
                    </template>
                    <div v-show="modes.CPEventRegistration === 'list'">
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.CPEventRegistration" :fields="fields.CPEventRegistration" class="mt-2">
                            <template v-slot:cell(cpeventregistration_no)="data">
                                <b-link :href="{literal}`index.php?module=CPEventRegistration&view=Detail&record=${data.item.id}`{/literal}" target="_blank" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="mr-auto"></b-col>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('CPEventRegistration')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => vtranslate('CPEventRegistration', 'CPEventRegistration')])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                </b-tab>
                
                {* RELATED ACTIVITY START FROM HERE *}
                <b-tab @click="loadRelatedList('Calendar')">
                    <template v-slot:title>
                        {vtranslate('LBL_HANA_ACTIVITIES', $MODULE)} <b-badge v-if="counters.Calendar > 0" v-html="counters.Calendar" variant="danger"></b-badge>
                    </template>
                    <div v-show="modes.Calendar === 'list'">
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.Calendar" :fields="fields.Calendar" class="mt-2">
                            <template v-slot:cell(subject)="data">
                                <b-link :href="{literal}`index.php?module=Calendar&view=Detail&record=${data.item.id}`{/literal}" target="_blank" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('Calendar')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => vtranslate('LBL_HANA_ACTIVITIES', $MODULE)])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                    <b-form id="Calendar" v-show="modes.Calendar === 'edit'">
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Subject', 'Calendar')}:<span v-show="isRequired('Calendar', 'subject')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input :data-rule-required="isRequired('Calendar', 'subject')" name="subject" v-model="form_data.Calendar.subject" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_ACTIVITYPE', $MODULE)}:<span v-show="isRequired('Calendar', 'activitytype')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-select :data-rule-required="isRequired('Calendar', 'activitytype')" name="activitytype" :options="meta_data.Calendar.picklist_fields.activitytype" v-model="form_data.Calendar.activitytype" value="Call" class="mt-2"></b-form-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3" v-show="form_data.Calendar.activitytype !== 'Task'"><strong>{vtranslate('LBL_HANA_ACTIVITY_STATUS', $MODULE)}:<span v-show="isRequired('Events', 'eventstatus')" class="text-danger"> *</span></strong></label>
                                <label class="mt-3" v-show="form_data.Calendar.activitytype === 'Task'"><strong>{vtranslate('LBL_HANA_ACTIVITY_STATUS', $MODULE)}:<span v-show="isRequired('Calendar', 'taskstatus')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-select :data-rule-required="isRequired('Events', 'eventstatus')" name="eventstatus" v-show="form_data.Calendar.activitytype !== 'Task'" :options="meta_data.Calendar.picklist_fields.eventstatus" v-model="form_data.Calendar.eventstatus" class="mt-2"></b-form-select>
                                <b-form-select :data-rule-required="isRequired('Calendar', 'taskstatus')" name="taskstatus" v-show="form_data.Calendar.activitytype === 'Task'" :options="meta_data.Calendar.picklist_fields.taskstatus" v-model="form_data.Calendar.taskstatus" class="mt-2"></b-form-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_ACTIVITY_START_TIME', $MODULE)}:<span v-show="isRequired('Calendar', 'date_start') || isRequired('Calendar', 'time_start')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <date-picker data-type="date" :data-rule-required="isRequired('Calendar', 'date_start')" name="date_start" v-model="form_data.Calendar.date_start" :config="datepicker_options" @change="calcEndDate" class="mt-2"></date-picker>
                                <b-select :data-rule-required="isRequired('Calendar', 'time_start')" name="time_start" :options="meta_data.Calendar.picklist_fields.time_start" v-model="form_data.Calendar.time_start" @change="calcEndTime" class="mt-2"></b-select>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_ACTIVITY_END_TIME', $MODULE)}:<span v-show="isRequired('Calendar', 'time_end')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <date-picker data-type="date" data-rule-required="true" v-model="form_data.Calendar.due_date" :config="datepicker_options" class="mt-2"></date-picker>
                                <b-select :data-rule-required="isRequired('Calendar', 'time_end')" name="time_end" v-if="form_data.Calendar.activitytype !== 'Task'" :options="meta_data.Calendar.picklist_fields.time_end" v-model="form_data.Calendar.time_end" class="mt-2"></b-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4" v-if="form_data.Calendar.activitytype === 'Call'">
                                <label class="mt-3"><strong>{vtranslate('LBL_EVENTS_CALL_DIRECTION', 'Events')}:<span v-show="isRequired('Events', 'events_call_direction')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8" v-if="form_data.Calendar.activitytype === 'Call'">
                                <b-form-select :data-rule-required="isRequired('Events', 'events_call_direction')" name="events_call_direction" :options="meta_data.Events.picklist_fields.events_call_direction" v-model="form_data.Calendar.events_call_direction" class="mt-2"></b-form-select>
                            </b-col>
                            <b-col cols="4" v-if="form_data.Calendar.activitytype === 'Task'">
                                <label class="mt-3"><strong>{vtranslate('Priority', 'Calendar')}:<span v-show="isRequired('Calendar', 'taskpriority')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8" v-if="form_data.Calendar.activitytype === 'Task'">
                                <b-form-select name="taskpriority" :data-rule-required="isRequired('Calendar', 'taskpriority')" :options="meta_data.Calendar.picklist_fields.taskpriority" v-model="form_data.Calendar.taskpriority" class="mt-2"></b-form-select>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Assigned To', 'Calendar')}:<span v-show="isRequired('Calendar', 'assigned_user_id')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <multiple-owner :endpoint="getEntryPointUrl('index.php?module=Vtiger&action=HandleOwnerFieldAjax&mode=loadOwnerList')":data-rule-required="isRequired('Calendar', 'assigned_user_id')" name="assigned_user_id" v-model="form_data.Calendar.assigned_user_id" v-bind:selected-tags="selected_tags" class="mt-2"></multiple-owner>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Description', 'Calendar')}:<span v-show="isRequired('Calendar', 'description')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea name="description" :data-rule-required="isRequired('Calendar', 'description')" class="mt-2" v-model="form_data.Calendar.description"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                    </b-form>
                    <div class="fixed-footer container-fluid">
                        <div class="footer-content">
                            <b-row v-show="modes.Calendar === 'edit'">
                                <b-col cols="auto" class="mx-auto">
                                    <b-button variant="primary" @click="submitSaveAjax('Calendar')" class="ml-2 mt-2">{vtranslate('LBL_SAVE')}</b-button>
                                    <b-button variant="outline-danger" class="ml-2 mt-2" @click="toggleMode('Calendar', 'list')">{vtranslate('LBL_CANCEL')}</b-button>
                                </b-col>
                            </b-row>
                            <b-row v-show="modes.Calendar === 'list'">
                                <b-col cols="4">
                                    <label class="mt-3"><strong>{vtranslate('LBL_QUICK_CREATE')}:</strong></label>
                                </b-col>
                                <b-col cols="auto" class="mr-auto">
                                    {* Modified by Hieu Nguyen on 2022-09-06 to display create buttons based on user permission *}
                                    <b-dropdown dropup variant="outline-primary" text="{vtranslate('LBL_HANA_SELECT_ACTIVITY', $MODULE)}" class="mr-2 mt-2">
                                        {if Calendar_Module_Model::canCreateActivity('Call')}
                                            <b-dropdown-item @click="toggleMode('Calendar', 'edit', 'Call')">{vtranslate('Call', 'Calendar')}</b-dropdown-item>
                                        {/if}
                                        {if Calendar_Module_Model::canCreateActivity('Meeting')}
                                            <b-dropdown-item @click="toggleMode('Calendar', 'edit', 'Meeting')">{vtranslate('Meeting', 'Calendar')}</b-dropdown-item>
                                        {/if}
                                        {if Calendar_Module_Model::canCreateActivity('Task')}
                                            <b-dropdown-item @click="toggleMode('Calendar', 'edit', 'Task')">{vtranslate('SINGLE_Calendar', 'Calendar')}</b-dropdown-item>
                                        {/if}
                                    </b-dropdown>
                                    {* End Hieu Nguyen *}
                                </b-col>
                            </b-row>
                        </div>
                    </div>
                    <div class="footer-padding"></div>
                </b-tab>
                {* RELATED ACTIVITY END HERE *}

                {* RELATED SALES ORDER START FROM HERE *}
                <b-tab v-if="customer_data.record_module === 'Contacts'" @click="loadRelatedList('SalesOrder')">
                    <template v-slot:title>
                        {vtranslate('LBL_HANA_SALES_ORDER', $MODULE)} <b-badge v-if="counters.SalesOrder > 0" v-html="counters.SalesOrder" variant="danger"></b-badge>
                    </template>
                    <div v-show="modes.SalesOrder === 'list'">
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.SalesOrder" :fields="fields.SalesOrder" class="mt-2 salesorder-table">
                            <template v-slot:cell(salesorder_no)="data">
                                <b-link href="javascript:void(0)" @click="openSalesOrderDetail(data.item)" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('SalesOrder')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => vtranslate('SalesOrder', 'SalesOrder')])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                    <b-form id="SalesOrder" class="salesorder-form" v-show="modes.SalesOrder === 'edit'">
                        <h6 class="mt-3 form-block">{vtranslate('LBL_HANA_SALES_ORDER_DETAIL', $MODULE)}</h6>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_PRODUCT', $MODULE)}:</strong></label>
                            </b-col>
                            <b-col cols="8" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_PRODUCT_PLACEHOLDER', $MODULE)}">
                                <select-product module="Products" v-model="form_data.SalesOrder.select_product" v-bind:ignores="form_data.SalesOrder.ignores" class="mt-2"></select-product>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_HANA_SERVICE', $MODULE)}:</strong></label>
                            </b-col>
                            <b-col cols="8" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_SERVICE_PLACEHOLDER', $MODULE)}">
                                <select-product module="Services" v-model="form_data.SalesOrder.select_product" v-bind:ignores="form_data.SalesOrder.ignores" class="mt-2"></select-product>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-0" />
                        <table style="width: 100%">
                            <tr v-for="(item, index) in form_data.SalesOrder.items" :key="item.id">
                                <td class="product-name">
                                    <label v-b-tooltip.hover.noninteractive :title="item.label" class="mt-3 inline-product-name"><strong v-html="item.label"></strong></label>
                                    <b-button v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_DELETE')}" size="sm" variant="outline-danger" class="mr-1 delete-product" @click="removeProduct(item.id)"><i class="fa fa-close" aria-hidden="true"></i></b-button>
                                </td>
                                <td class="fieldValue quantity" v-b-tooltip.hover.noninteractive title="{vtranslate('Quantity', 'SalesOrder')}">
                                    <b-form-input type="number" min="0" class="mt-2 quantity" v-model="item.quantity" @change="updateQuantity(item)"></b-form-input>
                                </td>
                                <td class="fieldValue price" v-b-tooltip.hover.noninteractive title="{vtranslate('List Price', 'SalesOrder')}">
                                    <div class="flex-wraper">
                                        <div class="form-control readonly mt-2" v-html="item.price && formatCurrency(item.price)"></div>
                                    </div>
                                </td>
                                <td class="fieldValue total" v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_ITEM_TOTAL_PRICE', $MODULE)}">
                                    <div class="flex-wraper">
                                        <div class="form-control readonly mt-2" v-html="item.total && formatCurrency(item.total)"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <hr class="mt-2 mb-0" v-if="form_data.SalesOrder.items && form_data.SalesOrder.items.length > 0" />
                        <b-form-row>
                            <b-col sm="6" class="d-none d-sm-block"></b-col>
                            <b-col cols="12" sm="6">
                                <b-form-row>
                                    <b-col cols="3" class="text-right">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_PRETAX_PRICE', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="9" class="text-right mt-3">
                                        <label v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_PRETAX_PRICE', $MODULE)}" v-html="form_data.SalesOrder.total && global.app.convertCurrencyToUserFormat(form_data.SalesOrder.total, true)"></label>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" class="text-right">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_DISCOUNT', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="3">
                                        <b-form-input v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_DISCOUNT_PERCENT', $MODULE)}" type="number" min="0" data-rule-optional-less-than-or-equal="100" v-model="form_data.SalesOrder.discount_percent" @change="calcDiscountAmount" class="mt-2"></b-form-input>
                                    </b-col>
                                    <b-col cols="6" class="fieldValue discount_amount">
                                        <b-form-input v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_DISCOUNT_AMOUNT', $MODULE)}" v-model="form_data.SalesOrder.discount_amount" @change="calcDiscountPercent" @keyup="formatDiscountAmount" class="mt-2"></b-form-input>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" class="text-right">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_TAX', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="3" class="fieldValue tax_percent">
                                        <b-form-select v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_TAX_PERCENT', $MODULE)}" :options="meta_data.SalesOrder.picklist_fields.tax" v-model="form_data.SalesOrder.tax_percent" @change="calcTaxAmount" class="mt-2"></b-form-select>
                                    </b-col>
                                    <b-col cols="6" class="fieldValue tax_amount">
                                        <b-form-input v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_TAX_AMOUNT', $MODULE)}" readonly v-model="form_data.SalesOrder.tax_amount" class="mt-2"></b-form-input>
                                    </b-col>
                                </b-form-row>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-0" />
                        <h6 class="mt-3 form-block">{vtranslate('LBL_HANA_SALES_ORDER_GENERAL_INFORMATION', $MODULE)}</h6>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Status', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'sostatus')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-select :data-rule-required="isRequired('SalesOrder', 'sostatus')" name="sostatus" :options="meta_data.SalesOrder.picklist_fields.sostatus" v-model="form_data.SalesOrder.sostatus" class="mt-2"></b-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Description', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'description')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea name="description" :data-rule-required="isRequired('SalesOrder', 'description')" class="mt-2" v-model="form_data.SalesOrder.description"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-0" />
                        <h6 class="mt-3 form-block">{vtranslate('LBL_HANA_SALES_ORDER_ADDRESS_INFORMATION', $MODULE)}</h6>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Shipping Address', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'ship_street')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea :data-rule-required="isRequired('SalesOrder', 'ship_street')" name="ship_street" v-model="form_data.SalesOrder.ship_street" class="mt-2"></b-form-textarea>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_RECEIVER_NAME', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'receiver_name')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input :data-rule-required="isRequired('SalesOrder', 'receiver_name')" name="receiver_name" v-model="form_data.SalesOrder.receiver_name" class="mt-2"></b-form-input>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('LBL_RECEIVER_PHONE', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'receiver_phone')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input type="number" :data-rule-required="isRequired('SalesOrder', 'receiver_phone')" name="receiver_phone" v-model="form_data.SalesOrder.receiver_phone" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="12">
                                <b-form-checkbox v-model="form_data.SalesOrder.issue_invoice" value="1" unchecked-value="0" class="mt-3"><strong>{vtranslate('LBL_HANA_SALES_ORDER_INVOICE', $MODULE)}</strong></b-form-checkbox>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-show="form_data.SalesOrder.issue_invoice == '1'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Account Name', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'account_id')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <parent-record :endpoint="getEntryPointUrl('index.php?module=SalesOrder&search_module=Accounts&action=BasicAjax')" :submit-url="getEntryPointUrl('index.php')" :data-rule-required="isRequired('SalesOrder', 'account_id')" name="account_id" v-model="form_data.SalesOrder.account_id" :parent-id="customer_data.account_id" :parent-name="customer_display.account_id" parent-module="Accounts" module="SalesOrder" class="mt-2"></parent-record>
                            </b-col>
                        </b-form-row>
                        <b-form-row v-show="form_data.SalesOrder.issue_invoice == '1'">
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Billing Address', 'SalesOrder')}:<span v-show="isRequired('SalesOrder', 'bill_street')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea :data-rule-required="isRequired('SalesOrder', 'bill_street')" name="bill_street" v-model="form_data.SalesOrder.bill_street" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                    </b-form>
                    <div v-show="modes.SalesOrder === 'list'" class="fixed-footer container-fluid">
                        <div class="footer-content"><b-button v-show="modes.customer === 'detail'" variant="link" class="mr-2 mt-2" @click="toggleMode('SalesOrder', 'edit')"><i class="fa fa-plus" aria-hidden="true"></i> {vtranslate('LBL_HANA_CREATE_SALES_ORDER', $MODULE)}</b-buton>
                        </div>
                    </div>
                    <div v-show="modes.SalesOrder === 'edit'" class="fixed-footer footer-lg container-fluid">
                        <div class="footer-content">
                            <b-row>
                                <b-col cols="auto" class="mr-auto">
                                    <h6 class="mt-2 mb-0"><strong>{vtranslate('LBL_HANA_SALES_ORDER_TOTAL_AMOUNT', $MODULE)}:</strong></h6>
                                </b-col>
                                <b-col cols="auto" class="ml-auto">
                                    <h6 class="text-danger mt-2 mb-0" v-html="form_data.SalesOrder.total && global.app.convertCurrencyToUserFormat(form_data.SalesOrder.grand_total, true)"></h6>
                                </b-col>
                            </b-row>
                            <hr class="mt-2 mb-0" />
                            <b-row>
                                <b-col cols="auto" class="mx-auto">
                                    <b-button variant="primary" @click="submitSalesOrder" class="ml-2 mt-2">{vtranslate('LBL_HANA_CREATE_SALES_ORDER', $MODULE)}</b-button>
                                    <b-button v-b-tooltip.hover.noninteractive title="{vtranslate('LBL_HANA_SALES_ORDER_CANCEL_WARNING', $MODULE)}" variant="outline-danger" class="ml-2 mt-2" @click="toggleMode('SalesOrder', 'list')">{vtranslate('LBL_CANCEL')}</b-button>
                                </b-col>
                            </b-row>
                        </div>
                    </div>
                    <div class="footer-padding"></div>
                </b-tab>
                {* RELATED SALES ORDER END HERE *}

                {* RELATED INVOICE START FROM HERE *}
                <b-tab v-if="customer_data.record_module === 'Contacts'" @click="loadRelatedList('Invoice')">
                    <template v-slot:title>
                        {vtranslate('Invoice', 'Invoice')} <b-badge v-if="counters.Invoice > 0" v-html="counters.Invoice" variant="danger"></b-badge>
                    </template>
                    <div v-show="modes.Invoice === 'list'">
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.Invoice" :fields="fields.Invoice" class="mt-2">
                            <template v-slot:cell(invoice_no)="data">
                                <b-link :href="{literal}`index.php?module=Invoice&view=Detail&record=${data.item.id}`{/literal}" target="_blank" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="mr-auto"></b-col>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('Invoice')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => vtranslate('Invoice', 'Invoice')])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                </b-tab>
                {* RELATED INVOICE END HERE *}

                {* RELATED TICKET START FROM HERE *}
                <b-tab v-if="customer_data.record_module === 'Contacts'" @click="loadRelatedList('HelpDesk')">
                    <template v-slot:title>
                        Ticket <b-badge v-if="counters.HelpDesk > 0" v-html="counters.HelpDesk" variant="danger"></b-badge>
                    </template>
                    <div v-show="modes.HelpDesk === 'list'">
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.HelpDesk" :fields="fields.HelpDesk" class="mt-2">
                            <template v-slot:cell(ticket_no)="data">
                                <b-link :href="{literal}`index.php?module=HelpDesk&view=Detail&record=${data.item.id}`{/literal}" target="_blank" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('HelpDesk')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => 'Ticket'])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                    <b-form id="HelpDesk" v-show="modes.HelpDesk === 'edit'">
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Title', 'HelpDesk')}:<span v-show="isRequired('HelpDesk', 'ticket_title')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-input :data-rule-required="isRequired('HelpDesk', 'ticket_title')" name="ticket_title" v-model="form_data.HelpDesk.ticket_title" class="mt-2"></b-form-input>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Assigned To', 'HelpDesk')}:<span v-show="isRequired('HelpDesk', 'assigned_user_id')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <multiple-owner :endpoint="getEntryPointUrl('index.php?module=Vtiger&action=HandleOwnerFieldAjax&mode=loadOwnerList')" :data-rule-required="isRequired('HelpDesk', 'assigned_user_id')" name="assigned_user_id" v-model="form_data.HelpDesk.assigned_user_id" v-bind:selected-tags="selected_tags" class="mt-2"></multiple-owner>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Status', 'HelpDesk')}:<span v-show="isRequired('HelpDesk', 'ticketstatus')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-select :data-rule-required="isRequired('HelpDesk', 'ticketstatus')" name="ticketstatus" :options="meta_data.HelpDesk.picklist_fields.ticketstatus" v-model="form_data.HelpDesk.ticketstatus" class="mt-2"></b-form-select>
                            </b-col>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Priority', 'HelpDesk')}:<span v-show="isRequired('HelpDesk', 'ticketpriorities')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-select :data-rule-required="isRequired('HelpDesk', 'ticketpriorities')" name="ticketpriorities" :options="meta_data.HelpDesk.picklist_fields.ticketpriorities" v-model="form_data.HelpDesk.ticketpriorities" class="mt-2"></b-form-select>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Descriptions', 'HelpDesk')}:<span v-show="isRequired('HelpDesk', 'description')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea name="description" :data-rule-required="isRequired('HelpDesk', 'description')" v-model="form_data.HelpDesk.description" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="4">
                                <label class="mt-3"><strong>{vtranslate('Solution', 'HelpDesk')}:<span v-show="isRequired('HelpDesk', 'solution')" class="text-danger"> *</span></strong></label>
                            </b-col>
                            <b-col cols="8">
                                <b-form-textarea name="solution" :data-rule-required="isRequired('HelpDesk', 'solution')" v-model="form_data.HelpDesk.solution" class="mt-2"></b-form-textarea>
                            </b-col>
                        </b-form-row>
                    </b-form>
                    <div class="fixed-footer container-fluid">
                        <div class="footer-content">
                            <b-row v-show="modes.HelpDesk === 'edit'">
                                <b-col cols="auto" class="mx-auto">
                                    <b-button variant="primary" @click="submitSaveAjax('HelpDesk')" class="ml-2 mt-2">{vtranslate('LBL_SAVE')}</b-button>
                                    <b-button variant="outline-danger" class="ml-2 mt-2" @click="toggleMode('HelpDesk', 'list')">{vtranslate('LBL_CANCEL')}</b-button>
                                </b-col>
                            </b-row>
                            <b-row v-show="modes.HelpDesk === 'list'">
                                {* Modified by Hieu Nguyen on 2022-03-11 to check forbidden feature *}
					            {if !isForbiddenFeature('CaptureTicketsViaChatbot')}
                                    <b-button variant="link" class="mr-2 mt-2" @click="toggleMode('HelpDesk', 'edit')"><i class="fa fa-plus" aria-hidden="true"></i> {vtranslate('LBL_HANA_CREATE_TICKET', $MODULE)}</b-buton>
                                {/if}
                                {* End Hieu Nguyen *}
                            </b-row>
                        </div>
                    </div>
                    <div class="footer-padding"></div>
                </b-tab>
                {* RELATED TICKET END HERE *}

                {* RELATED PRODUCTS START HERE *}
                <b-tab v-if="customer_data.record_module === 'Leads'" @click="loadRelatedList('Products')">
                    <template v-slot:title>
                        {vtranslate('LBL_HANA_INTERESTED_PRODUCT', $MODULE)} <b-badge v-if="counters.Products > 0" v-html="counters.Products" variant="danger"></b-badge>
                    </template>
                    <div>
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.Products" :fields="fields.Products" class="mt-2">
                            <template v-slot:cell(product_no)="data">
                                <b-link :href="{literal}`index.php?module=Products&view=Detail&record=${data.item.id}`{/literal}" target="_blank" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="mr-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="toggleMode('Products', 'edit')"><i class="fa fa-plus" aria-hidden="true"></i> {vtranslate('LBL_HANA_ADD_RELATED_PRODUCTS', $MODULE)}</b-buton>
                            </b-col>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('Products')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => vtranslate('Products', 'Products')])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                    <b-form id="Products" v-if="modes.Products === 'edit'">
                        <b-form-row>
                            <b-col>
                                <multiple-product data-rule-required="true" name="product_ids" module="Products" v-model="form_data.Products.product_ids" class="mt-2"></multiple-product>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="outline-danger" class="ml-2 mt-2" @click="toggleMode('Products', 'list')">{vtranslate('LBL_CANCEL')}</b-button>
                                <b-button variant="primary" class="ml-2 mt-2" @click="submitProduct('Products')">{vtranslate('LBL_SAVE')}</b-button>
                            </b-col>
                        </b-form-row>
                    </b-form>
                </b-tab>
                {* RELATED PRODUCTS END HERE *}

                {* RELATED SERVICES START HERE *}
                <b-tab v-if="customer_data.record_module === 'Leads'" @click="loadRelatedList('Services')">
                    <template v-slot:title>
                        {vtranslate('LBL_HANA_INTERESTED_SERVICE', $MODULE)} <b-badge v-if="counters.Services > 0" v-html="counters.Services" variant="danger"></b-badge>
                    </template>
                    <div>
                        <b-table show-empty empty-text="{vtranslate('LBL_HANA_TABLE_EMPTY_WARNING', $MODULE)}" striped :items="data.Services" :fields="fields.Services" class="mt-2">
                            <template v-slot:cell(service_no)="data">
                                <b-link :href="{literal}`index.php?module=Services&view=Detail&record=${data.item.id}`{/literal}" target="_blank" v-html="data.value"></b-link>
                            </template>
                        </b-table>
                        <b-row>
                            <b-col cols="auto" class="mr-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="toggleMode('Services', 'edit')"><i class="fa fa-plus" aria-hidden="true"></i> {vtranslate('LBL_HANA_ADD_RELATED_SERVICES', $MODULE)}</b-buton>
                            </b-col>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="link" class="mr-2 mt-2" @click="openChatbotIframeRelatedListPopup('Services')">{vtranslate('LBL_HANA_SHOW_ALL_RELATED_LIST', $MODULE, ['%module_name' => vtranslate('Services', 'Services')])}</b-buton>
                            </b-col>
                        </b-row>
                    </div>
                    <b-form id="Services" v-if="modes.Services === 'edit'">
                        <b-form-row>
                            <b-col>
                                <multiple-product data-rule-required="true" name="product_ids" module="Services" v-model="form_data.Services.product_ids"  class="mt-2"></multiple-product>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="outline-danger" class="ml-2 mt-2" @click="toggleMode('Services', 'list')">{vtranslate('LBL_CANCEL')}</b-button>
                                <b-button variant="primary" class="ml-2 mt-2" @click="submitProduct('Services')">{vtranslate('LBL_SAVE')}</b-button>
                            </b-col>
                        </b-form-row>
                    </b-form>
                </b-tab>
                {* RELATED SERVICES END HERE *}
            </b-tabs>
        </div>
        {* EXISXT CUSTOMER HOLDER END HERE *}

    </b-container>
</b-overlay>