{*
    Name: CustomerTypeConfig.tpl
    Author: Phu Vo
    Date: 2021.03.19
    Description: HTML for customer type config
*}

{strip}
    <form autocomplete="off" name="config" class="customer-type-config">
        <input type="hidden" name="module" value="Vtiger" />
        <input type="hidden" name="parent" value="Settings" />
        <input type="hidden" name="action" value="SaveCustomerTypeConfig" />

        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <div class="fieldBlockHeader">{vtranslate('LBL_CUSTOMER_TYPE_AUTO_CONVERT_CONFIGURATION_BASE_ON_CUSTOMER_TYPE')}</div>
                    <hr />
                    <div class="fieldBlockContent">
                        <div class="config-description-container">
                            <h4>{vtranslate('LBL_CUSTOMER_TYPE_SELECT_YOUR_CUSTOMER_TYPE')}:</h4>
                        </div>
                        <div class="customer-types-container">
                            <div class="customer-type-container {if $CONFIG.customer_type == 'personal'}checked{/if}">
                                <div class="custoner-type-title">
                                    <input type="radio" name="config[customer_type]" value="personal" {if $CONFIG.customer_type == 'personal'}checked{/if} />
                                    <span>{vtranslate('LBL_CUSTOMER_TYPE_PERSONAL')}</span>
                                </div>
                                <div class="customer-type-content">
                                    <p>{vtranslate('LBL_CUSTOMER_TYPE_PERSONAL_DESCRIPTION')}</p>
                                </div>
                            </div>
                            <div class="customer-type-container {if $CONFIG.customer_type == 'company'}checked{/if}">
                                <div class="custoner-type-title">
                                    <input type="radio" name="config[customer_type]" value="company" {if $CONFIG.customer_type == 'company'}checked{/if} />
                                    <span>{vtranslate('LBL_CUSTOMER_TYPE_COMPANY')}</span>
                                </div>
                                <div class="customer-type-content">
                                    <p>{vtranslate('LBL_CUSTOMER_TYPE_COMPANY_DESCRIPTION')}</p>
                                </div>
                            </div>
                            <div class="customer-type-container {if $CONFIG.customer_type == 'both'}checked{/if}">
                                <div class="custoner-type-title">
                                    <input type="radio" name="config[customer_type]" value="both" {if $CONFIG.customer_type == 'both'}checked{/if} />
                                    <span>{vtranslate('LBL_CUSTOMER_TYPE_BOTH')}</span>
                                </div>
                                <div class="customer-type-content">
                                    <p>{vtranslate('LBL_CUSTOMER_TYPE_BOTH_DESCRIPTION')}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-overlay-footer clearfix">
            <div class="row clear-fix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
                    <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
                </div>
            </div> 
        </div>
    </form>
{/strip}