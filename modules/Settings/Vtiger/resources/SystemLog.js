/*
	File: SystemLog.js
	Author: Hieu Nguyen
	Date: 2022-11-17
	Purpose: handle logic on the UI for System Log
*/

CustomView_BaseController_Js('Settings_Vtiger_SystemLog_Js', {}, {
	registerEvents: function () {
		this._super();
		this.registerEventFormInit();
	},

	getForm: function () {
		return $('form#system-log');
	},

	getSelectedFile: function (form) {
		return form.find('[name="log_file"]').val();
	},

	registerEventFormInit: function () {
		let self = this;
		let form = self.getForm();
		this.lastFile = self.getSelectedFile(form);

		// Handle file select
		form.find('[name="log_file"]').on('change', function () {
			let selectedFile = $(this).val();

			if (!selectedFile) {
				form.find('#btn-reload').addClass('hide');
			}
			else {
				form.find('#btn-reload').removeClass('hide');
				self.showLogContent(form, selectedFile);
			}
		});

		// Handle button reload
		form.find('#btn-reload').on('click', function () {
			let selectedFile = self.getSelectedFile(form);
			
			if (selectedFile) {
				self.showLogContent(form, selectedFile);
			}
		});
	},

	showLogContent: function (form, selectedFile) {
		let self = this;
		let logContentInput = form.find('[name="log_content"]');
		app.helper.showProgress();

		let params = {
			module: 'Vtiger',
			parent: 'Settings',
			view: 'SystemLog',
			mode: 'getLogContent',
			selected_file: selectedFile,
		}

		// Call ajax to get log content
		app.request.get({ data: params })
		.then((err, res) => {
			app.helper.hideProgress();

			if (err) {					
				app.helper.showErrorNotification({ message: err.message });
				return;
			}

			// Display log content
			logContentInput.val(res.log_content);

			// Reset scroll top
			if (selectedFile != self.lastFile) {
				logContentInput[0].scrollTop = logContentInput[0].scrollHeight;
			}

			// Track selected file for next run
			self.lastFile = selectedFile;
		});
	},
});