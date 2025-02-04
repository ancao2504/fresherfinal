{strip}
    {assign var="RELATED_PRODUCTS_SECTION" value=$RELATED_PRODUCTS_SECTION}

    <table class="table table-bordered ">
        {if $template eq 1} {*-- Added by Kelvin Thang on 2020-01-01 apply for template --*}
            <tr id="row0" class="hide lineItemCloneCopy" data-row-num="0">
                {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=0 data=[] IGNORE_UI_REGISTRATION=true}
            </tr>

            <!--Added by Kelvin Thang -- OnlineCRM -- 2018-09-10 -->
            <tr id="section0" class="hide lineItemsSectionCloneCopy" data-section-num="0">
                {include file="partials/LineItemsSectionContent.tpl"|@vtemplate_path:'Inventory' section_no=0 data=[] IGNORE_UI_REGISTRATION=true}
            </tr>
        {/if}

        {if count($RELATED_PRODUCTS) neq 1 && $section_no neq 0}
            <tr id="section{$section_no}"  class="lineItemsSection  {if count($RELATED_PRODUCTS) eq 0}hide{/if}" data-section-num="{$section_no}">
                {include file="partials/LineItemsSectionContent.tpl"|@vtemplate_path:'Inventory' section_no=$section_no sectionNumTabName=array_column($RELATED_PRODUCTS_SECTION, 'sectionNumTabName') IGNORE_UI_REGISTRATION=true}
            </tr>

        {/if}

        {if ((count($RELATED_PRODUCTS) eq 0 and $create eq 1 ) || count($RELATED_PRODUCTS) eq 1) and ($template neq 1 )and ($PRODUCT_ACTIVE eq 'true' || $SERVICE_ACTIVE eq 'true')}
            <tr id="section1"  class="lineItemsSection hide" data-section-num="1">
                {include file="partials/LineItemsSectionContent.tpl"|@vtemplate_path:'Inventory' section_no=1 sectionNumTabName=[] IGNORE_UI_REGISTRATION=true}
            </tr>
        {/if}

        <tr class="lineItemHeaders">
            <td width="5%"><strong>{vtranslate('LBL_ACTIONS',$MODULE)}</strong></td>
            {if $IMAGE_EDITABLE}
                <td width="10%">
                    <strong>{vtranslate({$LINEITEM_FIELDS['image']->get('label')},$MODULE)}</strong>
                </td>
            {/if}
            {if $PRODUCT_EDITABLE}
                <td width="30%">
                    <span class="redColor">*</span><strong>{vtranslate({$LINEITEM_FIELDS['productid']->get('label')},$MODULE)}</strong>
                </td>
            {/if}
            <td width="8%">
                <strong>{vtranslate('LBL_QTY',$MODULE)}</strong>
            </td>
            {if $PURCHASE_COST_EDITABLE}
                <td width="10%">
                    <strong class="pull-right">{vtranslate({$LINEITEM_FIELDS['purchase_cost']->get('label')},$MODULE)}</strong>
                </td>
            {/if}
            {if $LIST_PRICE_EDITABLE}
                <td width="15%">
                    <strong>{vtranslate({$LINEITEM_FIELDS['listprice']->get('label')},$MODULE)}</strong>
                </td>
            {*{/if}*} {*-- Comment out by Kelvin Thang on 2020-29-02 --@To: Hide TOTAL HEADERS when field list price is hidden --*}
                <td width="10%"><strong class="pull-right">{vtranslate('LBL_TOTAL',$MODULE)}</strong></td>
            {/if} {*-- Added by Kelvin Thang on 2020-29-02 --@To: Hide TOTAL HEADERS when field list price is hidden --*}

            {if $MARGIN_EDITABLE && $PURCHASE_COST_EDITABLE}
                <td width="10%">
                    <strong class="pull-right">{vtranslate({$LINEITEM_FIELDS['margin']->get('label')},$MODULE)}</strong>
                </td>
            {/if}

            {if $LIST_PRICE_EDITABLE} {*--Begin: Added by Kelvin Thang on 2020-29-02 --@To: Hide NET PRICE HEADERS when field list price is hidden -->*}
                <td width="10%"><strong class="pull-right">{vtranslate('LBL_NET_PRICE',$MODULE)}</strong></td>
            {/if}{*--End: Added by Kelvin Thang on 2020-29-02 --@To: Hide NET PRICE HEADERS when field list price is hidden -->*}

        </tr>

        {foreach key=row_no item=data from=$RELATED_PRODUCTS_SECTION}
            <tr id="row{$row_no}" data-row-num="{$row_no}" class="lineItemRow" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"]}{/if}>
                {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=$row_no section_no=$section_no data=$data}
            </tr>
        {/foreach}

        {if count($RELATED_PRODUCTS) eq 0 and $create eq 1 and ($PRODUCT_ACTIVE eq 'true' || $SERVICE_ACTIVE eq 'true')}
            <tr id="row1" class="lineItemRow" data-row-num="1">
                {include file="partials/LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=1 section_no=1 data=[] IGNORE_UI_REGISTRATION=false}
            </tr>
        {/if}

        <!--Added by Kelvin Thang -- OnlineCRM -- 2018-09-10 -->
        <tr class="p-t-16">
            <td colspan="7">
                {if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
                    <div class="btn-toolbar">
                                <span class="btn-group">
                                    <button type="button" class="btn btn-link addProduct" data-module-name="Products" >
                                        <i class="far fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                                    </button>
                                </span>
                                <span class="btn-group">
                                    <button type="button" class="btn btn-link addService" data-module-name="Services" >
                                        <i class="far fa-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                                    </button>
                                </span>
                    </div>
                {elseif $PRODUCT_ACTIVE eq 'true'}
                    <div class="btn-group">
                        <button type="button" class="btn btn-link addProduct" data-module-name="Products">
                            <i class="far fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                        </button>
                    </div>
                {elseif $SERVICE_ACTIVE eq 'true'}
                    <div class="btn-group">
                        <button type="button" class="btn btn-link addService" data-module-name="Services">
                            <i class="far fa-plus"></i><strong>&nbsp;&nbsp;{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                        </button>
                    </div>
                {/if}

            </td>
        </tr>

    </table>

{/strip}

