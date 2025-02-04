/*
	CustomTag.js
	Author: Vu Mai
	Date: 2022-09-07
	Purpose: handle logic UI for Custom Tag
*/

class CustomTag {

	container = null;
	customerId = null;
	customerType = null;

	// Handle event init custom tag
	init (container) {
		let self = this;
		this.container = container;
		this.customerId = container.attr('data-customer-id');
		this.customerType = container.attr('data-customer-type');

		// Trigger event render tag list UI
		this.loadTagList(container);

		// Register event click of button show tagging modal
		container.find('.btn-add-tags').on('click', function () {
			self.showTaggingModal(this);
		});
	}

	// Render tag list UI for custom tag
	loadTagList (element) {
		let params = {
			module: 'Vtiger',
			view: 'CustomTagViewAjax',
			mode: 'getTagList',
			customer_id: this.customerId,
			customer_type: this.customerType
		};
	
		app.request.post({ data: params })
		.then((err, res) => {
			if (err) {
				console.log('[CustomTag] Load tags error:', err);
				return;
			}
	
			// Refresh tag linked list
			element.find('.tag-list-container').html('');
			element.find('.tag-list-container').append(res);

			// Enable tooltip
			vtUtils.enableTooltips();
		});
	}

	// Show taggig modal and save tag linked list
	showTaggingModal (targetBtn) {
		let self = this;
		app.helper.hideProgress();
	
		let params = {
			module: 'Vtiger',
			view: 'CustomTagViewAjax',
			mode: 'getTaggingModal',
			customer_id: this.customerId,
			customer_type: this.customerType
		};
	
		app.request.post({ data: params })
		.then((err, res) => {
			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}
	
			// Display modal
			app.helper.showModal(res, {
				preShowCb: function (modal) {
					const modalForm = modal.find('form[name="tagging_form"]');
	
					// Init Dom Controller
					Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modalForm);
	
					// Init field selected tag
					let tagInput = modal.find('[name="tags"]');
					tagInput.select2({
						placeholder: tagInput.attr('placeholder'),
						minimumInputLength: 0,
						closeOnSelect: false,
						tags: [],
						tokenSeparators: [','],
						ajax: {
							url: 'index.php?module=Vtiger&action=CustomTagAjax&mode=getAssignableTags',
							dataType: 'json',
							data: function (term, page) {
								term = term.trim();
								let data = { keyword: term }
	
								return data;
							},
							results: function (data) {
								return { results: data.result };
							},
							transport: function (params) {
								return jQuery.ajax(params);
							}
						},
						formatSelection: function (object, container) {
							if (object.id) {
								let template = `<span title="${object.text}">${object.text}</span>`;
			
								// Process item type
								container
									.closest('.select2-search-choice')
									.attr('data-type', object.type)
									.addClass('tag')
		
								return template;
							}
		
							return object.text;
						},
						formatResult: function (object, container) {
							if (object.id) {
								let template = `<span title="${object.text}">${object.text}</span>`;
			
								// Process item type
								container
									.attr('data-type', object.type)
									.addClass('tag-option')
		
								return template;
							}
		
							return object.text;
						}
					});

					// Get tags linked with record and update list tags linked to field selected tag
					let selectedTags = jQuery.parseJSON(tagInput.attr('data-selected-tags'));
					tagInput.select2('data', selectedTags);
					let preserveTags = tagInput.select2('data').map(single => single.id) || [];

					// Register create new tag button
					let selectInput = tagInput.select2('container').find('.select2-input');
						
					selectInput.on('keydown', e => {
						if (e.keyCode == 13) {
							// TODO Find another way to check is result empty
							let resultCount = $('.select2-results:visible').find('.select2-no-results').length;
							let newTag = $(e.target).val();
							let maxLength = 25;
							
							if (resultCount > 0) {
								if (newTag.length > maxLength) {
									Error.notify(app.vtranslate('JS_CUSTOM_TAG_CREATE_TAG_LIMIT_ERROR_MSG', { limit: maxLength }));
									return;
								}
	
								// Create new Tag
								let params = {
									module: this.customerType,
									action: 'TagCloud',
									mode: 'saveTags',
									tagsList: { new: [newTag] },
									newTagType: 'private',
								}
	
								app.request.post({ data: params })
								.then((err, res) => {
									if (err) {
										app.helper.showErrorNotification({ message: err.message });
										return;
									}
	
									let currentTags = tagInput.select2('data') || [];
									let newTags = res.new || {};
									newTags = self.formatTagsList(newTags);
	
									currentTags = currentTags.concat(...newTags);
									tagInput.select2('data', currentTags).trigger('change');
									tagInput.data('select2').blur();
									tagInput.data('select2').search.trigger('keydown');
								});
							}
	
							e.preventDefault();
							return false;
						}
					});
	
					// Register add tag button
					modal.find('.add-tag-btn').on('click', event => {
						event.preventDefault();
	
						let callBack = res => {
							let currentTags = tagInput.select2('data') || [];
							let newTags = res.new || {};
							newTags = self.formatTagsList(newTags);
	
							currentTags = currentTags.concat(...newTags);
							tagInput.select2('data', currentTags).trigger('change');
						};
	
						self.openCreateTagModal(callBack);
					});

					modalForm.vtValidate({
						submitHandler: function () {
							let currentTags = tagInput.select2('data').map(single => single.id) || [];
							let linkedTags = currentTags.filter(single => !preserveTags.includes(single));
							let removedTags = preserveTags.filter(single => !currentTags.includes(single));
							
							// Do saving tag linked to record
							self.saveTag(linkedTags, removedTags);
	
							// Refresh tag linked list
							self.loadTagList($(targetBtn).closest('.custom-tag'));
	
							modalForm.find('.cancelLink').trigger('click');
							return false;
						}
					});
				}
			});
		});
	}

	// Show create new tag modal
	openCreateTagModal (callBack = null) {
		let self = this;
	
		// Load modal template
		const modal = $('#create-tag-modal').clone();
	
		// Remove modal style display none
		modal.show();
	
		// Init DOM Elements
		modal.find('.select2-container').remove();
	
		// Init Dom Controller
		Vtiger_Edit_Js.getInstanceByModuleName('Vtiger').registerBasicEvents(modal.find('form'));
	
		// Register submit event
		modal.find('form').vtValidate({
			submitHandler: form => {
				form = $(form);
				let newTag = form.find(':input[name="tag_name"]').val();
				let type = form.find(':input[name="visibility"]').is(':checked') ? 'public' : 'private';
					
				let params = {
					module: this.customerType,
					action: 'TagCloud',
					mode: 'saveTags',
					tagsList: { new: [newTag] },
					newTagType: type,
				}
	
				app.request.post({ data: params })
				.then((err, res) => {
					if (err) {
						app.helper.showErrorNotification({ message: err.message });
						return;
					}
	
					if (typeof callBack == 'function') {
						callBack(res);
					}
	
					modal.find('.close').trigger('click');
					app.helper.showSuccessNotification({ message: app.vtranslate('JS_CUSTOM_TAG_CREATE_TAG_SUCCESSFUL_MSG') });
				});
	
				return false;
			}
		});
		
		// Show modal
		app.helper.showPopup(modal);
	}

	// Save list tag linked
	saveTag (linkedTags, removedTags) { 
		let params = {
			module: this.customerType,
			action: 'TagCloud',
			mode: 'saveTags',
			selected_ids: [this.customerId],
			tagsList: {
				existing: linkedTags,
				deleted: removedTags
			}
		}
	
		app.request.post({ data: params })
		.then((err, res) => {
			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}
	
			app.helper.showSuccessNotification({ message: app.vtranslate('JS_CUSTOM_TAG_UPDATE_TAG_SUCCESSFUL_MSG') });
		});
	}

	// Format tag for saving new tag
	formatTagsList (tags) {
		tags = Object.keys(tags).map(id => {
			return {
				id,
				text: tags[id]['name'],
				name: tags[id]['name'],
				type: tags[id]['type'],
			}
		});
	
		return tags;
	}
}