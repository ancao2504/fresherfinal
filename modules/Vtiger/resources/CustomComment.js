/*
	CustomComment.js
	Author: Vu Mai
	Date: 2022-09-09
	Purpose: handle logic UI for Custom Comment
*/

class CustomComment {

	constructor () {
		this.attachments = [];
		this.containerDiv;
		this.customerId;
	}

	// Handle event init custom comment
	init (containerDiv, customerId) {
		let self = this;
		this.containerDiv = containerDiv;
		this.customerId = customerId;

		// Hide custom comment
		containerDiv.hide();

		// Render parent comments list UI
		this.renderParentComments();

		// Register event input for add comment textarea
		containerDiv.find('#add-comment-textarea').on('input', function () {
			mentionHandler.attach($(this));

			$(this).closest('.add-comment-container').find('[name="comment_content"]').val(mentionHandler.getDbFormat($(this)));
		});

		// Register event add attachments
		containerDiv.find('[name="attachements"]').on('change', function () {
			self.bindCommentAttachments(event);
		});

		// Register event save comment
		containerDiv.find('.save-comment').on('click', function() {
			self.saveComment(this);
		});

		// Register event get children comment
		containerDiv.on('click', '.child-count' , function() {
			self.renderChildComments(this);
		});
	}

	// Return parent comments list UI fo custom comment
	renderParentComments () {
		let params = {
			module: 'Vtiger',
			view: 'CustomCommentAjax',
			mode: 'getParentComments',
			customer_id: this.customerId,
			offset: 0,
			max_results: 5
		};
	
		app.request.post({ data: params })
		.then((err, res) => {
			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}
	
			// Refresh parent comment list
			this.containerDiv.find('.comment-list').html('');
			this.containerDiv.find('.comment-list').append(res);

			let commentCount = this.containerDiv.find('.comment-item').length;

			if (commentCount > 0) {
				this.containerDiv.find('.block-header').removeClass('hide');
				this.containerDiv.find('.view-all').removeClass('hide');

				// Update comment count on list title
				this.containerDiv.find('.comment-count').text(commentCount);
			}

			this.containerDiv.show();
			vtUtils.enableTooltips();
		});
	}

	// Return children comments list UI fo custom comment
	renderChildComments (element) {
		let parentId = $(element).attr('data-id');
		let childCommentContainer = $(element).closest('.comment-item').find('.child-comment-list');

		// Render child comment if not render yet
		if (!childCommentContainer.children().length > 0) {
			let params = {
				module: 'Vtiger',
				view: 'CustomCommentAjax',
				mode: 'getChildComments',
				parent_id: parentId,
			};
		
			app.request.post({ data: params })
			.then((err, res) => {
				if (err) {
					app.helper.showErrorNotification({ message: err.message });
					return;
				}
		
				// Refresh child comment list and show it
				childCommentContainer.html('');
				childCommentContainer.append(res);
				childCommentContainer.show();

				vtUtils.enableTooltips();
			});
		}

		// Toggle child comment container
		if (childCommentContainer.is(':visible')) {
			childCommentContainer.hide()
		}
		else {
			childCommentContainer.show()
		}
	}

	// Hanldle event add attachments
	bindCommentAttachments (event) {
		if (event.target.files.length == 0) return;

		let self = this;
		let files = event.target.files;
		let ext = '';
		let baseName = '';
		let allowedFileExts = _VALIDATION_CONFIG.allowed_upload_file_exts;

		for (let i = 0; i < files.length; i++) {
			let file = files[i];
			let nameParts = file.name.split('.');
			ext = nameParts.pop();
			baseName = nameParts.join('');
			
			files[i].ext = ext;
			files[i].base_name = baseName;

			// Validate file ext
			if ($.inArray(ext, allowedFileExts) == -1) {
				let replaceParams = {
					base_name: baseName,
					ext: ext
				};

				app.helper.showErrorNotification({ message: app.vtranslate('JS_CUSTOM_COMMENT_FILE_EXT_NOT_ALLOWED_ERROR_MSG', replaceParams) });
				return;
			}

			this.attachments = this.attachments.concat(file);
			event.target.value = '';

			let fileIndex = this.attachments.findIndex(single => single == file);

			let attachment = $(event.target).closest('.custom-comment').find('.comment-attachment-template');
			attachment.find('.file-name').html(baseName + '.');
			attachment.find('.file-extension').html(ext);
			attachment.find('.remove-attachment-button').attr('fileIndex', fileIndex);

			this.containerDiv.find('.comment-attachments-container')
			.append(attachment.html())
			.ready(function () {
				$(this).find('.remove-attachment-button').on('click', function () {
					self.removeCommentAttachment(this, file);
				})
			});
		}
	}

	// Hanldle event remove attachments
	removeCommentAttachment (targetBtn) {
		$(targetBtn).closest('.comment-attachments').remove();

		let files = this.attachments;
		let fileIndex = $(targetBtn).attr('fileIndex');

		if (fileIndex > -1) {
			files.splice(fileIndex, 1);
			this.attachments = files;
		}
	}

	// Do save comment
	saveComment (targetBtn) {
		let commentContent =  this.containerDiv.find('[name="comment_content"]').val();

		if (!commentContent) {
			app.helper.showErrorNotification({ message: app.vtranslate('JS_CUSTOM_COMMENT_POST_COMMENT_CONTENT_REQUIRED_ERROR_MSG') });
			return;
		}

		// Disabled button save
		$(targetBtn).attr('disabled', true);

		let params = {
			module: 'ModComments',
			action: 'SaveAjax',
			is_private: 0,
			commentcontent: commentContent,
			related_to: this.customerId,
		}

		let formData = new FormData();

		for (let prop in params) {
			formData.append(prop, params[prop]);
		}

		this.attachments.forEach(file => {
			formData.append('filename[]', file);
		});

		let requestParams = {};

		if (formData instanceof FormData) {
			requestParams.contentType = false;
			requestParams.processData = false;
		}
		
		requestParams.data = formData;

		app.request.post(requestParams)
		.then((err, res) => {
			if (err) {
				app.helper.showErrorNotification({ message: err.message });
				return;
			}
			
			app.helper.showSuccessNotification({ message: app.vtranslate('JS_CUSTOM_COMMENT_POST_COMMENT_SUCCESSFULLY') });

			this.renderParentComments(this.containerDiv);

			// Clear form and attachments
			this.clearFormAndAttachments();

			// Enable button save
			$(targetBtn).attr('disabled', false);
		});
	}

	clearFormAndAttachments () {
		// Clear form
		this.containerDiv.find('#add-comment-textarea').html('');
		this.containerDiv.find('[name="comment_content"]').val('');

		// Clear attachments
		this.attachments = [];
		this.containerDiv.find('.comment-attachments-container').html('');
	}
}