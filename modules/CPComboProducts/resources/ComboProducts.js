/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * To: Register event Select Combo Products
 */
jQuery(function ($) {
    registerInitEventSelectComboProducts();
});

/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * Function which will Register event select template to add
 */
function registerInitEventSelectComboProducts() {
    $('select[name="combo_products_template"]').change(function () {
        var data = $(this).select2('data');

        if (data.id !== '0') {
            addComboProductsSection(data.id, data.text);
            $(this).select2("val", "0");
        }
    });
}

/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * Function which will Add a Template Section
 * @params: id - Id of PS Template
 *          name - name of PS Template
 */
function addComboProductsSection(id, name) {
    //-- Create a new empty section
    var sectionInserted = createEmptySection();

    //-- Add template name into section
    sectionInserted.find('.nameField').val(name);

    //-- Add template products into section
    addProductsIntoSection(id, sectionInserted);
}

/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * Function which will clone a empty section and insert before another sections
 * @return : lineItemsSectionNew - New section was inserted
 */
function createEmptySection() {
    var inventory = Inventory_Edit_Js.getInstance();
    var form = $('#EditView');

    //--clone a new line tab
    var lineItemTabClone = form.find(".lineItemTabCloneCopy").clone(true, true);
    lineItemTabClone.addClass("lineItemTab").removeClass("hide lineItemTabCloneCopy");

    $('.lineitemTableContainer').append(lineItemTabClone);

    if ( form.find(".lineItemTab").length == 2) {
        var lineItemTab = form.find(".lineItemTab:nth-child(1)");
        var lineItemsSection = lineItemTab.find(".lineItemsSection");
        lineItemsSection.removeClass("hide");
    }

    var lineItemsSectionNew = inventory.getNewLineItemsSection();
    lineItemsSectionNew.closest(".lineItemsSection").addClass("newSectionAdded");
    lineItemsSectionNew.insertBefore(lineItemTabClone.find(".lineItemHeaders"));

    //--show tax container in Tax Mode
    if (inventory.isIndividualTaxMode()) {
        lineItemsSectionNew.closest(".lineItemTab").find(".individualTaxContainer").removeClass("hide");
    }

    return lineItemTabClone;
}

/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * Function which will add list product into section
 * @params: id - Id of PS Template
 *          section - The section to add product into
 */
// Modified by Tung Nguyen on 2022-10-31 to change logic add line item inventory
function addProductsIntoSection(id, section) {
    let inventory = Inventory_Edit_Js.getInstance();

    var params = {
        module: 'CPComboProducts',
        action: 'GetAllProduct',
        id: id
    };

    app.helper.showProgress();
    app.request.post({data: params}).then(function (error, data) {
        app.helper.hideProgress();
        $.each( data, function (key, item) {

            if ( item['entityType' + key] === 'Products') {
                //--if item is product
                inventory.addLineItemDetail(section.find('.addProduct'), item[key]);
                updateProductInfo(section, key, item);
            }
            else {
                //--if item is service
                inventory.addLineItemDetail(section.find('.addService'), item[key]);
                updateProductInfo(section, key, item);
            }

        });
    });
}
// Ended by Tung Nguyen
/**
 * Added by Kelvin Thang
 * Date: 2020-02-29
 * Function which will update product info into new line item
 * @params: key - key of product in array
 *          item - value of product
 *          section - The section to add product into
 */
function updateProductInfo(section, key, item) {
    var rowNum = section.find('.lineItemRow').length;
    var ListlineItemRow = section.find('.lineItemRow');

    newLineItem = jQuery(ListlineItemRow[rowNum - 1]);
    newLineItem.find('input.productName').val(app.htmlDecode(item['productName' + key]));
    newLineItem.find('input.productName').attr('disabled', 'disabled');
    newLineItem.find('.lineItemCommentBox').val(app.htmlDecode(item['comment' + key]));
    newLineItem.find('input.selectedModuleId').val(item['hdnProductId' + key]);
    newLineItem.find('input.qty').val(item['qty' + key]);
    newLineItem.find('input.listPrice').attr('list-info', '[]');

    inventory = Inventory_Edit_Js.getInstance();
    inventory.setListPriceValue(newLineItem, item['listPrice' + key]);
    inventory.setPurchaseCostValue(newLineItem, item['purchaseCost' + key]);
    inventory.setLineItemTotal(newLineItem, item['productTotal' + key]);
    inventory.setLineItemNetPrice(newLineItem, item['netPrice' + key]);
    inventory.registerQuantityChangeEvent();
}
