 /*
*   EditView,php
*   Author: Phuc Lu
*   Date: 2019.11.27
*   Purpose: customize the layout
*/

jQuery(function ($) {

	// Check for duplicate templatename
	$('#EditView').submit(function (e, checkDuplicated = 0) {

		if (!checkDuplicated) {
			e.preventDefault();

			var params = {
				module: 'EmailTemplates',
				action: 'CheckDuplicateAjax',
				record_id: $('[name="record"]').val(),
				check_field: 'templatename',
				check_value: $('[name="templatename"]').val()
			};

			app.request.post({ data: params }).then(function (error, data) {
				app.helper.hideProgress();

				if (error || data == 'true') {
					vtUtils.showQtip($('[name="templatename"]'), app.vtranslate('JS_VALIDATE_DUPLICATE_VALUE'),
						{
							position: {
								my: 'bottom left',
								at: 'top left'
							},
							style: {
								classes: 'qtip-red qtip-shadow'
							},
							events : {
								render: function (event, api) {
									var tooltip = api.elements.tooltip;
									setTimeout(function () {
										tooltip.hide();
									}, 5000);
									tooltip.on('click', function (event, api) {
										tooltip.hide();
									});
								}
							}
						}
					);

					$('[name="templatename"]').addClass('input-error');			
					$('.btn.btn-success.saveButton').attr('disabled', false);
				}
				else {
					$('#EditView').trigger('submit', 1);
				}

				return true;
			})
		}
	});
});