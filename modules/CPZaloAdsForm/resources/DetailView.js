/**
 * Name: Editview.js
 * Author: Phu Vo
 * Date: 2021.11.09
 */

 ;(function ($) {
	 
	// Page ready events
	$(function () {
		window.CPZaloAdsForm_Detail_View = new class {
			constructor () {
				let mappingFieldBlocks = $('.block[data-block="LBL_MAPPING_FIELDS"]');
				let mappingFieldContainer = mappingFieldBlocks.find('.fieldValue.mapping_fields');
		
				mappingFieldBlocks.find('.blockData').replaceWith(mappingFieldContainer);
				
				setTimeout(() => {
					mappingFieldBlocks.show();
				}, 100);
			}

			pullFormData () {
				let params = {
					module: app.getModuleName(),
					action: 'ZaloAdsFormAjax',
					mode: 'pullFormData',
					record_id: app.getRecordId(),
				}

				app.helper.showProgress();

				app.request.post({ data: params }).then((err, res) => {
					app.helper.hideProgress();

					if (err) {
						app.helper.showErrorNotification({ message: err.message });
						return;
					}

					if (!res) {
						app.helper.showErrorNotification({ message: app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR') });
						return;
					}

					app.helper.showSuccessNotification({ message: app.vtranslate('CPZaloAdsForm.JS_GET_DATA_SUCCESS_MSG')});

                    let currentTabModule = $('.related-tabs .tab-item.active').data('module');
                    let modulesToReload = ['CPTarget', 'Leads', 'Contacts'];
                    
                    if (modulesToReload.includes(currentTabModule)) {
                    	$('.related-tabs .tab-item.active').trigger('click');
                    }
                    else {
					    app.controller().updateRelatedRecordsCount();
                    }
					
					if (res.customer_count) {
						$('.fieldValue.customer_count').find('.value').text(res.customer_count);
					}
				});
			}
		}
	});
})(jQuery);