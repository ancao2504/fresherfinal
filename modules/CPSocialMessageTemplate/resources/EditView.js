/*
*	EditView.js
*	Author: Phuc Lu
*	Date: 2019.07.30
*   Purpose: handle edit action and UI
*/

jQuery(function($){    
    // Display block
    var content = $('#div_message_content_block').html();
    var contentBlock = $('#div_message_content_block').closest('.fieldBlockContainer');
    $(contentBlock).append('<div id="div_message_content_block" style="display:none">' + content + '</div>').find('table:first').remove();

    /**
     * Method to trigger change module
     * @author Phuc Lu on 2019.07.30
     */
    $('[name="cpsocialmessagetemplate_module"]').change(function() {
        $('[name="field_list"]').html('');
        var module = $(this).find('option:selected').val();

        $('[name="all_fields"] option').each(function() {
            if ($(this).attr('data-parent-module') == module) {
                $('[name="field_list"]').append('<option value="' + $(this).val() + '">' + $(this).html());
            }
        })

        $('[name="field_list"]').select2("destroy");
        $('[name="field_list"]').select2();
    })

    // Change type, so display layout for each tyoe
    $('[name="cpsocialmessagetemplate_content_type"]').change(function(e, resetValue = 1) {
        var type = $(this).find('option:selected').val();
        
        if (typeof type == 'undefined') {
            $('#div_message_content_block').hide();
        }
        else {
            if (resetValue) {
                $('[name="text_message"]').val('');
                $('[name="text_message"]').html('');   
                $('[name="message_content"]').val('');
                $('[name="message_content"]').html('');                    
                $('.div_button_container').remove();                  
                $('.div_item_container').remove();
                
                $('.div_cover img').attr('src', 'modules/CPSocialMessageTemplate/resources/DefaultCover.png');
                $('.div_cover_title').html(app.vtranslate('JS_COVER_DEFAULT_TITLE', 'CPSocialMessageTemplate'));
                $('.div_cover_description').html('');
                $('#cover_title').html('');
                $('#cover_image_url').html('');
                $('#cover_description').html('');
                $('#cover_click_url').html('');
            }
            
            if (type == '') {
                $('#div_message_content_block').hide();
            }

            if (type == 'text') {
                $('#div_message_content_block').show();
                $('#div_message_content_block').find('.fieldLabel.alignMiddle').closest('tr').show();
                $('#div_message_content_block').find('.fieldLabel.items.alignMiddle').closest('tr').hide();
                $('#div_message_content_block').find('.fieldLabel.buttons.alignMiddle').closest('tr').hide();          
                $('#div_message_content_block').find('.fieldLabel.cover.alignMiddle').closest('tr').hide(); 
            }

            if (type == 'list') {
                $('#div_message_content_block').show();    
                $('#div_message_content_block').find('.fieldLabel.alignMiddle').closest('tr').hide();                  
                $('#div_message_content_block').find('.fieldLabel.items.alignMiddle').closest('tr').show();           
                $('#div_message_content_block').find('.fieldLabel.cover.alignMiddle').closest('tr').show();  
            }
            
            if (type == 'buttons') {
                $('#div_message_content_block').show();    
                $('#div_message_content_block').find('.fieldLabel.alignMiddle').closest('tr').hide();                  
                $('#div_message_content_block').find('.fieldLabel.buttons.alignMiddle').closest('tr').show();           
                $('#div_message_content_block').find('.fieldLabel.cover.alignMiddle').closest('tr').show();  
            }
        }
    })

    // Trigger at the first time
    $('[name="cpsocialmessagetemplate_content_type"]').trigger('change', 0);

    // Add by Phuc on 2019.09.30 to trigger at edit view
    $('[name="cpsocialmessagetemplate_module"]').trigger('change');
})

$('form#EditView').submit(function(e) {
    // Validate for cover
    if ($('#cover_image_url').html() == '' && $('[name="cpsocialmessagetemplate_content_type"]').val() != '' && $('[name="cpsocialmessagetemplate_content_type"]').val() != 'text') {
        app.helper.showErrorNotification({message: app.vtranslate('JS_NOT_CHOOSE_COVER', 'CPSocialMessageTemplate')});
        
        setTimeout(function(){
            $('.btn.btn-success.saveButton').attr('disabled', false);
        }, 10);

        e.preventDefault();
    }

    // Update value for message_content
    var message_content = {};
    
    var cover = {
        'title' : $('#cover_title').html(),
        'image_url': $('#cover_image_url').html(),
        'description' : $('#cover_description').html(),
        'click_url' : $('#cover_click_url').html(),
    };
    
    if ($('[name="cpsocialmessagetemplate_content_type"]').val() == 'text') {  
        var textMessage = $('#text_message').val();

        message_content = {
            text_message : textMessage
        };
    }        

    if ($('[name="cpsocialmessagetemplate_content_type"]').val() == 'buttons') {
        let buttons = [];

        $('.div_button_container:visible').each(function() {
            buttons.push({
                'title' : $(this).find('.button_title').html(),
                'type' : $(this).find('.button_type').html(),
                'data' : $(this).find('.button_data').html(),
            })
        });

        message_content = {
            cover : cover,
            buttons : buttons
        };
    } 

    if ($('[name="cpsocialmessagetemplate_content_type"]').val() == 'list') {
        let items = [];

        $('.div_item_container:visible').each(function() {
            items.push({
                'title' : $(this).find('.item_title').html(),
                'type' : $(this).find('.item_type').html(),
                'icon_url' : $(this).find('.item_icon_url').html(),
                'data' : $(this).find('.item_data').html(),
            })
        });

        message_content = {
            cover : cover,
            items : items
        };
    }

    $('[name="message_content"]').val(Base64.encode(JSON.stringify(message_content)));
});

/**
 * Open model for cover
 * @author Phuc Lu on 2019.07.30
 */
function openCoverModal(element) { 
    var declareCoverModal = $('#div_cover_modal').clone(true, true);
    
    // Change div to form
    var formHtml = "<form class='form-horizontal formDeclareCoverForm' method='POST'>" + declareCoverModal.find('.formDeclareCoverForm').html() + '</form>';  
    declareCoverModal.find('.formDeclareCoverForm').remove();
    declareCoverModal.append(formHtml);

    // Reassign current value to modal
    declareCoverModal.find('[name="cover_title"]').val($('#cover_title').html());
    declareCoverModal.find('[name="cover_description"]').val($('#cover_description').html());
    declareCoverModal.find('[name="cover_image_url"]').val($('#cover_image_url').html());
    declareCoverModal.find('[name="cover_url"]').val($('#cover_click_url').html());

    var callBackFunction = function(data){       
        data.find('#div_cover_modal').removeClass('hide');        
        var form = data.find('.formDeclareCoverForm'); 

        var controller = Vtiger_Edit_Js.getInstance();
        controller.registerBasicEvents(form);
        vtUtils.applyFieldElementsView(form);
        
        // Form validation
        form.vtValidate();

        // Prevent submit action
        $(form).submit(function(e) {
            e.preventDefault();
    
            // Form validation
            if ($(form).find('.input-error').length > 0)
                return;

            var title = $(this).find('[name="cover_title"]').val();
            var description = $(this).find('[name="cover_description"]').val();
            var imageUrl = $(this).find('[name="cover_image_url"]').val();
            var coverUrl = $(this).find('[name="cover_url"]').val();

            $('.div_cover img').attr('src', imageUrl);
            $('.div_cover_title').html(title);
            $('.div_cover_description').html(description);

            $('#cover_title').html(title);
            $('#cover_description').html(description);
            $('#cover_image_url').html(imageUrl);
            $('#cover_click_url').html(coverUrl);

            app.helper.hideModal();
        })
    }

    var modalParams = {
        cb: callBackFunction
    }

    app.helper.showModal(declareCoverModal, modalParams);
}

/**
 * Open model for button
 * @author Phuc Lu on 2019.07.30
 */
function openButtonModal(element = null) {
    var declareButtonModal = $('#div_button_modal').clone(true, true);

    // Change div to form
    var index = '';
    var formHtml = "<form class='form-horizontal formDeclareButtonForm' method='POST'>" + declareButtonModal.find('.formDeclareButtonForm').html() + '</form>';  
    declareButtonModal.find('.formDeclareButtonForm').remove();
    declareButtonModal.append(formHtml);

    if (element !== null) {     
        // Reassign current value to modal
        let buttonDiv = $(element).parent().prev();
        declareButtonModal.find('[name="button_title"]').val(buttonDiv.find('.button_title').html());
        declareButtonModal.find('.slc_action').val(buttonDiv.find('.button_type').html());
        declareButtonModal.find('[name="button_url"]').val(buttonDiv.find('.button_data').html());

        // Edit header
        declareButtonModal.find('h4').html(app.vtranslate('JS_EDIT_BUTTON', 'CPSocialMessageTemplate'));
    }

    var callBackFunction = function(data){       
        data.find('#div_button_modal').removeClass('hide');        
        var form = data.find('.formDeclareButtonForm'); 

        var controller = Vtiger_Edit_Js.getInstance();
        controller.registerBasicEvents(form);
        vtUtils.applyFieldElementsView(form);

        // Validate select 2
        $('.slc_action:visible').select2();

        // Form validation
        form.vtValidate();

        // Prevent submit action
        $(form).submit(function(e) {
            e.preventDefault();
            
            // Form validation
            if ($(form).find('.input-error').length > 0)
                return;

            var title = $(this).find('[name="button_title"]').val();
            var action = $(this).find('.slc_action option:selected').val();
            var actionDetails = $(this).find('[name="button_url"]').val();

            if (element !== null) {
                updateButton(element, title, action, actionDetails);   
            }
            else {
                addNewButton(title, action, actionDetails); 
            } 

            app.helper.hideModal();
        })
    }

    var modalParams = {
        cb: callBackFunction
    }

    app.helper.showModal(declareButtonModal, modalParams);
}

/**
 * Open model for item
 * @author Phuc Lu on 2019.07.30
 */
function openItemModal(element = null) {

    var declareItemModal = $('#div_item_modal').clone(true, true);

    // Change div to form
    var formHtml = "<form class='form-horizontal formDeclareItemForm' method='POST'>" + declareItemModal.find('.formDeclareItemForm').html() + '</form>';  
    declareItemModal.find('.formDeclareItemForm').remove();
    declareItemModal.append(formHtml);

    if (element !== null) {
        let itemDiv = $(element).parent().prev();
        // Reassign current value to modal
        declareItemModal.find('[name="item_title"]').val(itemDiv.find('.item_title').html());
        declareItemModal.find('[name="icon_url"]').val(itemDiv.find('.item_icon_url').html());        
        declareItemModal.find('.slc_action').val(itemDiv.find('.item_type').html());
        declareItemModal.find('[name="item_url"]').val(itemDiv.find('.item_data').html());
        
        // Edit header
        declareItemModal.find('h4').html(app.vtranslate('JS_EDIT_ITEM', 'CPSocialMessageTemplate'));
    }

    var callBackFunction = function(data){       
        data.find('#div_item_modal').removeClass('hide');        
        var form = data.find('.formDeclareItemForm'); 

        var controller = Vtiger_Edit_Js.getInstance();
        controller.registerBasicEvents(form);
        vtUtils.applyFieldElementsView(form);

        // Init select 2
        $('.slc_action:visible').select2();

        // Form validation
        form.vtValidate();

        // Prevent submit action
        $(form).submit(function(e) {
            e.preventDefault();
            
            // Form validation
            if ($(form).find('.input-error').length > 0)
                return;

            var title = $(this).find('[name="item_title"]').val();
            var iconUrl = $(this).find('[name="icon_url"]').val();
            var action = $(this).find('.slc_action option:selected').val();
            var itemUrl = $(this).find('[name="item_url"]').val();

            if (element !== null) {
                updateItem(element, title, iconUrl, action, itemUrl);
            }     
            else {
                addNewItem(title, iconUrl, action, itemUrl);
            } 

            app.helper.hideModal();
        })
    }

    var modalParams = {
        cb: callBackFunction
    }

    app.helper.showModal(declareItemModal, modalParams);
}

/**
 * Add new button after submitting in modal
 * @author Phuc Lu on 2019.07.30
 */
function addNewButton(title, action, actionDetails) {
    var newButton = $('.div_button_template').clone();
    $(newButton).find('.div_action .action_link:first').attr('onclick', 'openButtonModal(this)');

    $(newButton).find('.div_button').find('.button_title').html(title);
    $(newButton).find('.div_button').find('.button_type').html(action);
    $(newButton).find('.div_button').find('.button_data').html(actionDetails);

    $(newButton).removeClass('hide');
    $(newButton).removeClass('div_button_template');
    $(newButton).addClass('div_button_container');

    $('.div_buttons_container').append(newButton);
}

/**
 * Update button after submitting in modal
 * @author Phuc Lu on 2019.07.30
 */
function updateButton(element, title, action, actionDetails) {
    let buttonDiv =  $(element).parent().prev();

    buttonDiv.find('.button_title').html(title);
    buttonDiv.find('.button_type').html(action);
    buttonDiv.find('.button_data').html(actionDetails);
}

/**
 * Add new item after submitting from modal
 * @author Phuc Lu on 2019.07.30
 */
function addNewItem(title, iconUrl, action, itemUrl) {
    var newItem = $('.div_item_template').clone();
    $(newItem).find('.div_action .action_link:first').attr('onclick', 'openItemModal(this)');

    $(newItem).find('.div_item_icon').css('background-image', 'url(' + iconUrl + ')');

    $(newItem).find('.div_item_content').find('.item_title').html(title);
    $(newItem).find('.div_item_content').find('.item_icon_url').html(iconUrl);
    $(newItem).find('.div_item_content').find('.item_type').html(action);
    $(newItem).find('.div_item_content').find('.item_data').html(itemUrl);

    $(newItem).removeClass('hide');
    $(newItem).removeClass('div_item_template');
    $(newItem).addClass('div_item_container');

    $('.div_items_container').append(newItem);
}

/**
 * Update item after submitting from modal
 * @author Phuc Lu on 2019.07.31
 */
function updateItem(element, title, iconUrl, action, itemUrl) {
    let itemDiv =  $(element).parent().prev();

    itemDiv.find('.div_item_icon').css('background-image', 'url(' + iconUrl + ')');
    itemDiv.find('.div_item_content').find('.item_title').html(title);
    itemDiv.find('.div_item_content').find('.item_icon_url').html(iconUrl);
    itemDiv.find('.div_item_content').find('.item_type').html(action);
    itemDiv.find('.div_item_content').find('.item_data').html(itemUrl);
}

/**
 * Remove a row for buttons and items
 * @author Phuc Lu on 2019.07.30
 * Update to remove for buttons and items function by Phuc on 2019.09.16
 */
function removeRow(element) {
    app.helper.showConfirmationBox({
        message: app.vtranslate('JS_COMFIRM_REMOVE_ROW', 'CPSocialMessageTemplate')
    }).then(function () {
        $(element).parent().parent().remove();
    });
}

/**
 * Insert variable for textfield
 * @author Phuc Lu on 2019.07.30
 */
function insertVariable() {
    var field = $('[name="field_list"] option:selected').val();

    if (field !== '' && typeof field != 'undefined'){
        var cursorPosition = $('#text_message').prop("selectionStart");
        var text =  $('#text_message').val();

        text = text.slice(0, cursorPosition) + field + text.slice(cursorPosition);
        $('#text_message').val(text);
    }
    else {
        vtUtils.showQtip($('.btn_insert'), app.vtranslate('JS_FIELD_EMPTY', 'CPSocialMessageTemplate') );
        setTimeout(function(){
            vtUtils.hideQtip($('.btn_insert'));
        }, 1000)
    }
}