{*
    ChatbotIframeCreateCustomerPopup.tpl
    Author: Phu Vo
    Date: 2020.09.11
*}

{strip}
    <div id="app" style="display: none">
        <b-container fluid class="mb-3">
            <b-overlay :show="overlay">
                <div class="header">
                    <h5 class="mt-2 text-center">{vtranslate('LBL_HANA_ADD_TO_CRM', $MODULE)}</h5>
                </div>
                <div class="body">
                    <b-row>
                        <b-col>
                            <b-avatar :src="customer.avatar || 'resources/images/no_ava.png'" size="3em" class="mr-2"></b-avatar>
                            <label v-html="customer.name"></label>
                        </b-col>
                    </b-row>
                    <b-tabs class="mt-2">
                        <b-tab title="{vtranslate('LBL_ADD_NEW', $MODULE)}">
                            <b-form id="customer">
                                <b-form-row>
                                    <b-col cols="3" sm="2">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_CUSTOMER_TYPE', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <b-form-radio inline v-model="form_data.customer_type" value="Leads" class="mt-3" checked>{vtranslate('Leads', 'Leads')}</b-form-radio>
                                        <b-form-radio inline v-model="form_data.customer_type" value="Contacts" class="mt-3">{vtranslate('Contacts', 'Contacts')}</b-form-radio>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" sm="2">
                                        <label class="mt-3" v-show="form_data.customer_type === 'Contacts'"><strong>{vtranslate('First Name')}:<span v-show="isRequired('Leads', 'firstname')" class="text-danger"> *</span></strong></label>
                                        <label class="mt-3" v-show="form_data.customer_type === 'Leads'"><strong>{vtranslate('First Name')}:<span v-show="isRequired('Contacts', 'firstname')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <b-form-row>
                                            <b-col cols="4">
                                                <b-form-select class="mt-2" :options="meta_data.Contacts.picklist_fields.salutationtype" placeholder="Xưng hô" v-model="form_data.salutationtype"></b-form-select>
                                            </b-col>
                                            <b-col cols="8">
                                                <b-form-input v-show="form_data.customer_type === 'Contacts'" :data-rule-required="isRequired('Leads', 'firstname')" name="firstname" class="mt-2" v-model="form_data.firstname"></b-form-input>
                                                <b-form-input v-show="form_data.customer_type === 'Leads'" :data-rule-required="isRequired('Contacts', 'firstname')" name="firstname" class="mt-2" v-model="form_data.firstname"></b-form-input>
                                            </b-col>
                                        </b-form-row>
                                    </b-col>
                                    <b-col cols="3" sm="2">
                                        <label class="mt-3" v-show="form_data.customer_type === 'Contacts'"><strong>{vtranslate('Last Name')}:<span v-show="isRequired('Contacts', 'lastname')" class="text-danger"> *</span></strong></label>
                                        <label class="mt-3" v-show="form_data.customer_type === 'Leads'"><strong>{vtranslate('Last Name')}:<span v-show="isRequired('Leads', 'lastname')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <b-form-input v-show="form_data.customer_type === 'Contacts'" :data-rule-required="isRequired('Contacts', 'lastname')" name="lastname" class="mt-2" v-model="form_data.lastname"></b-form-input>
                                        <b-form-input v-show="form_data.customer_type === 'Leads'" :data-rule-required="isRequired('Leads', 'lastname')" name="lastname" class="mt-2" v-model="form_data.lastname"></b-form-input>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" sm="2">
                                        <label class="mt-3" v-show="form_data.customer_type === 'Contacts'"><strong>{vtranslate('Mobile')}:<span v-show="isRequired('Contacts', 'mobile')" class="text-danger"> *</span></strong></label>
                                        <label class="mt-3" v-show="form_data.customer_type === 'Leads'"><strong>{vtranslate('Mobile')}:<span v-show="isRequired('Leads', 'mobile')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <b-form-input v-show="form_data.customer_type === 'Contacts'" :data-rule-required="isRequired('Contacts', 'mobile')" name="mobile" class="mt-2" v-model="form_data.mobile"></b-form-input>
                                        <b-form-input v-show="form_data.customer_type === 'Leads'" :data-rule-required="isRequired('Leads', 'mobile')" name="mobile" class="mt-2" v-model="form_data.mobile"></b-form-input>
                                    </b-col>
                                    <b-col cols="3" sm="2">
                                        <label v-show="form_data.customer_type === 'Contacts'" class="mt-3"><strong>{vtranslate('Email')}:<span v-show="isRequired('Contacts', 'email')" class="text-danger"> *</span></strong></label>
                                        <label v-show="form_data.customer_type === 'Leads'" class="mt-3"><strong>{vtranslate('Email')}:<span v-show="isRequired('Leads', 'email')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <b-form-input v-show="form_data.customer_type === 'Contacts'" :data-rule-required="isRequired('Contacts', 'email')" name="email" class="mt-2" v-model="form_data.email"></b-form-input>
                                        <b-form-input v-show="form_data.customer_type === 'Leads'" :data-rule-required="isRequired('Leads', 'email')" name="email" class="mt-2" v-model="form_data.email"></b-form-input>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" sm="2">
                                        <label v-show="form_data.customer_type === 'Contacts'" class="mt-3"><strong>{vtranslate('LBL_HANA_COMPANY', $MODULE)}:<span v-show="isRequired('Contacts', 'account_id')" class="text-danger"> *</span></strong></label>
                                        <label v-show="form_data.customer_type === 'Leads'" class="mt-3"><strong>{vtranslate('LBL_HANA_COMPANY', $MODULE)}:<span v-show="isRequired('Leads', 'company')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <b-form-input v-if="form_data.customer_type === 'Leads'" :data-rule-required="isRequired('Contacts', 'company')" name="company" class="mt-2" v-model="form_data.company"></b-form-input>
                                        <parent-record v-if="form_data.customer_type === 'Contacts'"
                                            :endpoint="getEntryPointUrl('index.php?module=Contacts&search_module=Accounts&action=BasicAjax')"
                                            :submit-url="getEntryPointUrl('index.php')"
                                            class="mt-2"
                                            v-model="form_data.account_id"
                                            v-bind:parent-id="form_data.account_id"
                                            v-bind:parent-name="form_data.account_id_display"
                                            module="Contacts"
                                            parent-module="Accounts"
                                            :data-rule-required="isRequired('Contacts', 'account_id')"
                                            name="account_id"
                                        ></parent-record>
                                    </b-col>
                                    <b-col cols="3" sm="2" v-if="form_data.customer_type === 'Contacts'">
                                        <label class="mt-3"><strong>{vtranslate('Birthdate', 'Contacts')}:<span v-show="isRequired('Contacts', 'birthday')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4" v-if="form_data.customer_type === 'Contacts'">
                                        <date-picker data-type="date" name="birthday" data-rule-less-than-today="true" :data-rule-required="isRequired('Contacts', 'birthday')" v-model="form_data.birthday" :config="datepicker_options" class="mt-2"></date-picker>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" sm="2">
                                        <label class="mt-3"><strong>{vtranslate('LBL_ASSIGNED_TO', 'Contacts')}:<span v-show="isRequired('Contacts', 'assigned_user_id')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="4">
                                        <multiple-owner :endpoint="getEntryPointUrl('index.php?module=Vtiger&action=HandleOwnerFieldAjax&mode=loadOwnerList')" :data-rule-required="isRequired('Contacts', 'assigned_user_id')" name="assigned_user_id" v-model="form_data.assigned_user_id" :selected-tags='selected_tags' class="mt-2"></multiple-owner>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="3" sm="2">
                                        <label v-show="form_data.customer_type === 'Contacts'" class="mt-3"><strong>{vtranslate('Description', 'Contacts')}:<span v-show="isRequired('Contacts', 'description')" class="text-danger"> *</span></strong></label>
                                        <label v-show="form_data.customer_type === 'Leads'" class="mt-3"><strong>{vtranslate('Description', 'Leads')}:<span v-show="isRequired('Leads', 'description')" class="text-danger"> *</span></strong></label>
                                    </b-col>
                                    <b-col cols="9" sm="10">
                                        <b-form-textarea v-show="form_data.customer_type === 'Contacts'" :data-rule-required="isRequired('Contacts', 'description')" name="description" class="mt-2" v-model="form_data.description"></b-form-textarea>
                                        <b-form-textarea v-show="form_data.customer_type === 'Leads'" :data-rule-required="isRequired('Leads', 'description')" name="description" class="mt-2" v-model="form_data.description"></b-form-textarea>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="auto" class="ml-auto">
                                        <b-button variant="primary" class="ml-2 mt-2" @click="submit">{vtranslate('LBL_SAVE')}</b-button>
                                    </b-col>
                                </b-form-row>
                            </b-form>
                        </b-tab>
                        <b-tab title="{vtranslate('LBL_LINK', $MODULE)}">
                            <b-form name="filters">
                                <b-form-row>
                                    <b-col cols="2">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_CUSTOMER_NAME', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="4">
                                        <b-form-input name="full_name" v-model="filter_data.full_name" class="mt-2"></b-form-input>
                                    </b-col>
                                    <b-col cols="2">
                                        <label class="mt-3"><strong>{vtranslate('Mobile', 'Contacts')}:</strong></label>
                                    </b-col>
                                    <b-col cols="4">
                                        <b-form-input name="number" v-model="filter_data.number" class="mt-2"></b-form-input>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="2">
                                        <label class="mt-3"><strong>{vtranslate('Email', 'Contacts')}:</strong></label>
                                    </b-col>
                                    <b-col cols="4">
                                        <b-form-input name="email" v-model="filter_data.email" class="mt-2"></b-form-input>
                                    </b-col>
                                    <b-col cols="2">
                                        <label class="mt-3"><strong>{vtranslate('LBL_HANA_COMPANY', $MODULE)}:</strong></label>
                                    </b-col>
                                    <b-col cols="4">
                                        <b-form-input name="company" v-model="filter_data.company" class="mt-2"></b-form-input>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="auto" class="ml-auto mt-2">
                                        <b-button variant="link" @click="clearFilter" class="text-danger">{vtranslate('LBL_CLEAR_FILTERS')}</b-button>
                                        <b-button variant="primary" @click="searchCustomer">{vtranslate('LBL_SEARCH')}</b-button>
                                    </b-col>
                                </b-form-row>
                            </b-form>
                            <hr class="mt-2 mb-2" />
                            <table id="find-customer" class="table table-striped table-bordered mb-2"  style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="lastname">{vtranslate('Last Name', 'Contacts')}</th>
                                        <th class="firstname">{vtranslate('First Name', 'Contacts')}</th>
                                        <th class="customer_type">{vtranslate('LBL_CUSTOMER_TYPE', $MODULE)}</th>
                                        <th class="mobile">{vtranslate('Mobile', 'Contacts')}</th>
                                        <th class="email">{vtranslate('Email', 'Contacts')}</th>
                                        <th class="company">{vtranslate('LBL_HANA_COMPANY', $MODULE)}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </b-tab>
                    </b-tabs>
                </div>
            </b-overlay>
        </b-container>
    </div>
{/strip}