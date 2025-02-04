/**
 * Author: Kelvin Thang
 * Date: 2018-07-13
 * @param element
 * @param isIgnoreDecimalCustom
 */
function formatNumber(element, isIgnoreDecimalCustom) {
    if (element.type != 'number') {
        setMaxLengthNumber(element);
    }

    app.formatNumberToUser(jQuery(element), element.selectionStart, isIgnoreDecimalCustom);
};

/**
 * Author: Kelvin Thang
 * Date: 2020-02-10
 * Set Max Length Input For Number Fields
 * @param element
 */
function setMaxLengthNumber(element) {
    var maxlength = 13;

    if (jQuery(element).attr('data-field-type') == 'currency' || jQuery(element).attr('data-rule-currency')) {
        maxlength = 22;

        if (app.getNumberOfDecimals()) {
            maxlength += app.getNumberOfDecimals() + 1;
        }
    }

    jQuery(element).attr('maxlength', maxlength);
};