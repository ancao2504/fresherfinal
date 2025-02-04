/*
	VTUnlinkCustomerTagsTask.js
	Author: Hieu Nguyen
	Date: 2021-12-07
	Purpose: handle logic on the UI for VTUnlinkCustomerTagsTask workflow task settings
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
});