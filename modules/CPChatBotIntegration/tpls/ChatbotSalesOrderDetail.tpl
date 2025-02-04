{*
    ChatbotIframeSalesOrderDetailPopup.tpl
    Author: Phu Vo
    Date: 2020.09.11
*}

{strip}
    <b-overlay id="app" :show="overlay" style="display: none">
        <b-container fluid>
            <div class="header">
                <h5 class="mt-2 text-center" v-html="title"></h5>
            </div>
            <div class="body mt-3">
                <b-form @submit.stop.prevent>
                    <b-card>
                        <b-form-row>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Subject', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.subject"></div>
                            </b-col>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('SalesOrder No', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.salesorder_no"></div>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Contact Name', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.contact_id"></div>
                            </b-col>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Account Name', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.account_id"></div>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Billing Address', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.bill_street"></div>
                            </b-col>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Shipping Address', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.ship_street"></div>
                            </b-col>
                        </b-form-row>
                        <b-form-row>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Status', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.sostatus"></div>
                            </b-col>
                            <b-col cols="3" sm="2" class="bg-light">
                                <label class="mt-3"><strong>{vtranslate('Description', 'SalesOrder')}:</label></strong>
                            </b-col>
                            <b-col cols="9" sm="4">
                                <div class="mt-2 form-control readonly" v-html="salesorder.description"></div>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-2" />
                        <table class="mb-0" style="width: 100%">
                            <tbody>
                                <tr v-for="item in salesorder.items" :key="item.id">
                                    <td class="product-no">
                                        <label v-b-tooltip.hover :title="item.hdnProductcode" class="mt-2"><strong v-html="item.hdnProductcode"></strong></label>
                                    </td>
                                    <td class="product-name">
                                        <label v-b-tooltip.hover :title="item.productName" class="mt-2"><strong v-html="item.productName"></strong></label>
                                    </td>
                                    <td class="fieldValue quantity">
                                        <div class="flex-wraper">
                                            <div class="form-control readonly quantity"><span v-html="item.qty" v-b-tooltip.hover title="{vtranslate('Quantity', 'SalesOrder')}"></span></div>
                                        </div>
                                    </td>
                                    <td class="fieldValue price">
                                        <div class="flex-wraper">
                                            <div class="form-control readonly">
                                                <span v-html="item.listPrice && global.app.convertCurrencyToUserFormat(item.listPrice, true)" v-b-tooltip.hover title="{vtranslate('List Price', 'SalesOrder')}"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fieldValue total">
                                        <div class="flex-wraper">
                                            <div class="form-control readonly">
                                                <span v-html="item.productTotal && global.app.convertCurrencyToUserFormat(item.productTotal, true)" v-b-tooltip.hover title="{vtranslate('LBL_NET_PRICE')}"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <hr class="mt-2 mb-2" />
                        <b-form-row>
                            <b-col cols="6" class="ml-auto">
                                <b-form-row>
                                    <b-col cols="6" class="text-right">
                                        <label class="mt-1"><strong>{vtranslate('LBL_TOTAL')}:</strong>
                                    </b-col>
                                    <b-col cols="6" class="text-right">
                                        <label v-b-tooltip.hover title="{vtranslate('LBL_TOTAL')}" class="mt-1" v-html="salesorder.hdnSubTotal && global.app.convertCurrencyToUserFormat(salesorder.hdnSubTotal, true)"></label>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="6" class="text-right">
                                        <label class="mt-1"><strong>{vtranslate('LBL_DISCOUNT')}:</strong>
                                    </b-col>
                                    <b-col cols="6" class="text-right">
                                        <label v-b-tooltip.hover title="{vtranslate('Discount Amount')}" v-html="salesorder.hdnDiscountAmount && global.app.convertCurrencyToUserFormat(salesorder.hdnDiscountAmount, true)" class="mt-1"></label>
                                    </b-col>
                                </b-form-row>
                                <b-form-row>
                                    <b-col cols="6" class="text-right">
                                        <label class="mt-1"><strong>{vtranslate('Tax')}:</strong>
                                    </b-col>
                                    <b-col cols="6" class="text-right">
                                        <label v-b-tooltip.hover title="{vtranslate('LBL_HANA_SALES_ORDER_TAX_AMOUNT', $MODULE)}" v-html="salesorder.final_details.tax_totalamount && global.app.convertCurrencyToUserFormat(salesorder.final_details.tax_totalamount, true)" class="mt-1"></label>
                                    </b-col>
                                </b-form-row>
                            </b-col>
                        </b-form-row>
                        <hr class="mt-2 mb-2" />
                        <b-form-row>
                            <b-col cols="6" class="mr-auto">
                                <b-link :href="{literal}`index.php?module=SalesOrder&view=Detail&record=${salesorder.record_id}`{/literal}" target="_blank">Xem đầy đủ trên CRM</b-link>
                            </b-col>
                            <b-col cols="3" class="ml-auto text-right">
                                <h6 class="mt-1"><strong>{vtranslate('LBL_GRAND_TOTAL')}:</strong></h6>
                            </b-col>
                            <b-col cols="3" class="ml-auto text-right mt-1">
                                <h6 v-b-tooltip.hover title="{vtranslate('LBL_GRAND_TOTAL')}" class="text-danger d-inline" v-html="salesorder.hdnGrandTotal && global.app.convertCurrencyToUserFormat(salesorder.hdnGrandTotal, true)"></h6>
                            </b-col>
                        </b-form-row>
                    </b-card>
                <b-form>
            </div>
        </b-container>
    </b-overlay>
{/strip}