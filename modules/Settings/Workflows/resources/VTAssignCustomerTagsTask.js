/*
	VTAssignCustomerTagsTask.js
	Author: Hieu Nguyen
	Date: 2021-11-24
	Purpose: handle logic on the UI for VTAssignCustomerTagsTask workflow task settings
*/

jQuery(function ($) {
	var formModelContent = $('.editTaskBody');
	var getTagsFromProductsServicesInput = formModelContent.find('[name="get_tags_from_products_services"]');
	var tagIdsInput = formModelContent.find('[name="tag_ids"]');

	// Handle toggle get tags from products and services in case the selected module is from Inventory family
	if (getTagsFromProductsServicesInput[0] != null) {
		// Render UI when modal load
		displayTagsInput();

		// Render UI when checkbox is toggled
		getTagsFromProductsServicesInput.on('change', function () {
			displayTagsInput();
		});
	}

	function displayTagsInput() {
		if (getTagsFromProductsServicesInput.is(':checked')) {
			tagIdsInput.select2('destroy');
			tagIdsInput.find('option').removeAttr('selected');
			tagIdsInput.select2().attr('disabled', true);
			formModelContent.find('#btn-new-tag').attr('disabled', true);
		}
		else {
			tagIdsInput.attr('disabled', false);
			formModelContent.find('#btn-new-tag').attr('disabled', false);
		}
	}

	// Handle button new tag
	formModelContent.find('#btn-new-tag').on('click', function () {
		var element = jQuery(this);
		element.popover('destroy');
	
		var editTagContainer = jQuery('#editTagContainer').clone();
		editTagContainer.removeClass('hide');
		editTagContainer.addClass('editTagContainer');

		element.popover({
			'content': editTagContainer,
			'html': true,
			'placement': 'top',
			'animation': true,
			'trigger': 'manual',
			'container': element.closest('.modal')
			
		});

		element.popover('show');
	});

	// Handle button save tag
	$(document).on('click', '.editTagContainer .saveTag', function (e) {
		var element = $(this);
		var editTagContainer = element.closest('.editTagContainer');
		var tagName = editTagContainer.find('[name="tag_name"]').val().trim();
		
		if (tagName == '') {
			var message = app.vtranslate('JS_PLEASE_ENTER_VALID_TAG_NAME');
			app.helper.showErrorNotification({ 'message': message });
			return;
		}
		
		var params = {
			'module': 'Vtiger',
			'action': 'TagCloud',
			'mode': 'create',
			'tag_name': tagName,
			'visibility': 'public',
		}
		
		app.request.post({ 'data': params })
		.then(function (err, data) {
			if (err || !data) {
				app.helper.showErrorNotification({ 'message': err.message });
				return;
			}

			var message = app.vtranslate('JS_TAG_SAVED_SUCCESSFULLY');
			app.helper.showSuccessNotification({ 'message': message });

			tagIdsInput.append('<option value="'+ data.tag_id +'" selected>'+ data.tag_name +'</option>');
			tagIdsInput.select2('destroy').select2();
			hidePopover(element);
		});
	});
	
	// Handle button cancel save tag
	$(document).on('click', '.editTagContainer .cancelSaveTag', function (e) {
		hidePopover($(this));
	});

	function hidePopover(triggeredElement) {
		var popoverId = triggeredElement.closest('.popover').attr('id');
		$('[aria-describedby="'+ popoverId +'"]').popover('destroy');
	}
	
	// Handle event enter on tag input
	$(document).on('keyup', '.editTagContainer [name="tag_name"]', function (e) {
		if (e.keyCode == 13 || e.which === 13) {
			$(e.target).closest('.editTagContainer').find('.saveTag').trigger('click');
		}
	});
});