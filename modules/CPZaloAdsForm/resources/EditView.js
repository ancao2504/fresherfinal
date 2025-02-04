/**
 * Name: Editview.js
 * Author: Phu Vo
 * Date: 2021.11.09
 */

;(function ($) {

	// Page ready events
	$(function () {
		jQuery.validator.addMethod(
			'mapping-required',
			function (value, element, params) {
				let target = $(element);
				let module = target.data('module');
				let mappingContainers = $(`.mapping-field[data-module="${module}"]`);
				let mappingFields = mappingContainers.find('select');
				let currentValues = [];
				let isValid = true;

				if (typeof params != 'object') return;

				mappingFields.each((index, element) => {
					let value = $(element).val();
					if (value) currentValues.push(value);
				});

				Object.keys(params).forEach((key) => {
					if (!currentValues.includes(key)) {
						isValid = false;
					}
				});

				return isValid;
			},
			function (params, element) {
				let fieldList = '';

				Object.keys(params).forEach((key, index) => {
					if (index > 0) fieldList += ', ';
					fieldList += params[key];
				});

				return app.vtranslate('CPZaloAdsForm.JS_MAPPING_FIELD_REQUIREMENT_ERROR_MSG', { field_list: fieldList });
			}
		);
		window.ZaloAdsFormEditView = new class {
			constructor () {
				let self = this;

				this.module = 'CPZaloAdsForm';
				this.form = $('#EditView');
				this.recordId = this.form.find(':input[name="record"]').val();
				this.mappingFieldBlocks = $('.fieldBlockContainer[data-block="LBL_MAPPING_FIELDS"]');
				this.mappingFieldContainer = this.mappingFieldBlocks.find('.fieldValue.mapping_fields');
				this.oaInput = this.form.find(':input[name="cpzaloadsform_advertise_oa"]');
				this.formIdInput = this.form.find(':input[name="form_id"]');
		
				this.oaInput.on('change', function () {
					self.initLoadFormFieldMapping();
				});
				
				this.formIdInput.on('change', function () {
					self.initLoadFormFieldMapping();
				});
		
				this.mappingFieldBlocks.children('table.table-borderless').replaceWith(this.mappingFieldContainer);
				self.registerReloadFormMappingField();
		
				if (this.recordId) {
					this.oaInput.prop('disabled', true);
					this.formIdInput.prop('disabled', true);
				}

				this.registerClassifyTags();
				this.registerValidateFormIdInput();
				this.registerPurposeInput();
				
				setTimeout(() => {
					this.mappingFieldBlocks.show();
				}, 100);
			}

			registerPurposeInput () {
				let self = this;
				let markAsRequired = $('<span class="redColor"> *</span>');

				this.form.find(':input[name="cpzaloadsform_purpose"]').on('change', function () {
					if ($(this).val() == 'Event Registration') {
						self.form.find('.fieldLabel.related_event').show();
						self.form.find('.fieldLabel.related_event').append(markAsRequired);
						self.form.find('.fieldValue.related_event').show();
						self.form.find(':input[name="related_event_display"]').attr('data-rule-required', true);
					}
					else {
						self.form.find('.fieldLabel.related_event').hide();
						self.form.find('.fieldLabel.related_event').find('.redColor').remove();
						self.form.find('.fieldValue.related_event').hide();
						self.form.find(':input[name="related_event_display"]').attr('data-rule-required', false);
					}
				}).trigger('change');
			}

            formatTagsList (tags) {
                tags = Object.keys(tags).map(id => {
                    return {
                        id,
                        text: tags[id]['name'],
                        name: tags[id]['name'],
                        type: tags[id]['type'],
						temp: true,
                    }
                });

                return tags;
            }

			registerClassifyTags () {
				let self = this;
				let tagInput = this.form.find(':input.classify-tags-input');

				tagInput.select2({
					placeholder: tagInput.attr('placeholder'),
					minimumInputLength: 0,
					closeOnSelect: false,
					tags: [],
					tokenSeparators: [','],
					ajax: {
						url: `index.php?module=CPZaloAdsForm&action=ZaloAdsFormAjax&mode=getAssignableTags`,
						dataType: 'json',
						data: function (term, page) {
							term = term.trim();

							let data = {
								keyword: term
							}

							return data;
						},
						results: function (data) {
							return {results: data.result};
						},
						transport: function (params) {
							return jQuery.ajax(params);
						}
					},
					formatSelection: function (object, container) {
						if (object.id) {
							let template =  `<span title="${object.text}">${object.text}</span>`;
		
							// Process item type
							container
								.closest('.select2-search-choice')
								.attr('data-type', object.type)
								.attr('data-temp', object.temp)
								.addClass('tag')
	
							return template;
						}
	
						return object.text;
					},
					formatResult: function (object, container) {
						if (object.id) {
							let template =  `<span title="${object.text}">${object.text}</span>`;
		
							// Process item type
							container
								.attr('data-type', object.type)
								.attr('data-temp', object.temp)
								.addClass('tag-option')
	
							return template;
						}
	
						return object.text;
					}
				});

				// Init selected values
				let selectedTags = tagInput.data('selectedTags');
				
				if (selectedTags) {

                    tagInput.select2('data', selectedTags).trigger('change');
				}

				// Register create new tag button
				let selectInput = tagInput.select2('container').find('.select2-input');
                    
				selectInput.on('keydown', e => {
					if (e.keyCode == 13) {
						// TODO Find another way to check is result empty
						let isResultEmpty = $('.select2-results:visible').find('.select2-no-results').length >= 1;
						let newTag = $(e.target).val();
						let maxLength = 25;
						
						if (isResultEmpty) {
							if (newTag.length > maxLength) {
								let message = app.vtranslate('CPZaloAdsForm.JS_CREATE_TAG_LIMIT_ERROR_MSG', { limit: maxLength });
								app.helper.showErrorNotification({ message: message });
								return;
							}

							// Create temp Tag
							let currentTags = tagInput.select2('data') || [];
							let newTags = {
								[newTag]: {
									name: newTag,
									type: 'public',
								}
							};

							newTags = self.formatTagsList(newTags);
							currentTags = currentTags.concat(...newTags);
							tagInput.select2('data', currentTags).trigger('change');
							tagInput.data('select2').blur();
							tagInput.data('select2').search.trigger('keydown');

							// Create hidden input to save tag later
							let input = $(`<input type="hidden" name="temp_tags[]" value="${newTag}">`);
							self.form.append(input);

							// Create new Tag
							// let requestParams = {
							// 	module: self.module,
							// 	action: 'TagCloud',
							// 	mode: 'saveTags',
							// 	tagsList: { new: [newTag] },
							// 	newTagType: 'public',
							// }

							// app.helper.showProgress();

							// app.request.post({ data: requestParams }).then((err, res) => {
							// 	app.helper.hideProgress();

							// 	if (err) {
							// 		app.helper.showErrorNotification({ message: err.message });
							// 		return;
							// 	}
			
							// 	if (!res) {
							// 		app.helper.showErrorNotification({ message: app.vtranslate('JS_THERE_WAS_SOMETHING_ERROR') });
							// 		return;
							// 	}
								
							// 	let currentTags = tagInput.select2('data') || [];
							// 	let newTags = res.new || {};
								
							// 	newTags = self.formatTagsList(newTags);

							// 	currentTags = currentTags.concat(...newTags);
							// 	tagInput.select2('data', currentTags).trigger('change');
							// 	tagInput.data('select2').blur();
							// 	tagInput.data('select2').search.trigger('keydown');
							// });
						}

						e.preventDefault();

						return false;
					}
				});
			}
		
			initLoadFormFieldMapping (alert = false) {
				let self = this;

				if (!this.oaInput.val().trim() || !this.formIdInput.val().trim()) {
					if (alert) {
						app.helper.showErrorNotification({ message: app.vtranslate('CPZaloAdsForm.JS_GET_DATA_REQUIREMENT_ERROR_MSG') });
					}

					return;
				}

				if (!this.formIdInput.valid()) return;
	
				let params = {
					module: this.module,
					view: 'ZaloAdsFormAjax',
					mode: 'loadFormMappingFields',
					record_id: this.recordId ?? '',
					oa_id: this.oaInput.val(),
					form_id: this.formIdInput.val(),
				};
	
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
	
					self.mappingFieldBlocks.find('.fieldValue.mapping_fields').html(res);
					vtUtils.applyFieldElementsView(self.mappingFieldBlocks.find('.fieldValue.mapping_fields'));
					self.registerReloadFormMappingField();
				});
			}
		
			registerReloadFormMappingField () {
				let self = this;

				this.mappingFieldBlocks.find('.mapping-fields-refresh').on('click', function () {
					self.initLoadFormFieldMapping();
				});
			}

			registerValidateFormIdInput () {
				let form = this.form;
				let checkDuplicateAction = 'index.php?module=CPZaloAdsForm&action=ZaloAdsFormAjax&mode=checkFormIdDuplicate'

				form.find('[name="form_id"]').attr('data-rule-remote-check-duplicate', checkDuplicateAction);
			}
		}
	});
})(jQuery);