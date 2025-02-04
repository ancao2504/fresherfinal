/*
	SurveyPopup.js
	Author: Hieu Nguyen
	Date: 2022-11-14
	Purpose: to display customer survey popup based on License's start date and expire date
*/

jQuery(function ($) {
	let startDate = $('[name="license_start_date"]').val();
	let remainingDays = $('[name="license_remaining_days"]').val();

	// Show bigining popup (x days from start date)
	if (startDate) {
		let beginingSurveyTriggerDays = [30, 90, 180];
		let daysFromStartDate = moment().startOf('day').diff(moment(startDate), 'days');

		if ($.inArray(daysFromStartDate, beginingSurveyTriggerDays) >= 0) {
			let beginingSurveyStatus = loadSurveyStatus('begining');
			let showBeginingSurvey = false;

			if (!beginingSurveyStatus.pause_until) {
				showBeginingSurvey = true;
			}
			else {
				if (moment() >= moment(beginingSurveyStatus.pause_until)) {
					showBeginingSurvey = true;
				}
			}

			if (showBeginingSurvey) {
				openSurveyPopup('begining', daysFromStartDate);
			}
		}
	}

	// Show ending popup (x days until expire date)
	if (remainingDays) {
		let endingSurveyTriggerDays = [30, 15, 7];
		remainingDays = parseInt(remainingDays);	// To compare with integers

		if ($.inArray(remainingDays, endingSurveyTriggerDays) >= 0) {
			let endingSurveyStatus = loadSurveyStatus('ending');
			let showEndingSurvey = false;

			if (!endingSurveyStatus.pause_until) {
				showEndingSurvey = true;

				// Do not show popup again when user already clicked the open form button
				if (endingSurveyStatus.form_opened_at) {
					showEndingSurvey = false;
				}
			}
			else {
				if (moment() >= moment(endingSurveyStatus.pause_until)) {
					showEndingSurvey = true;
				}
			}

			if (showEndingSurvey) {
				openSurveyPopup('ending', remainingDays);
			}
		}
	}

	function openSurveyPopup (type, triggerDay) {
		let modal = $('.modal-dialog.survey-popup').clone();
			
		let modalParams = {
			backdrop: 'static',
			keyboard: false,
			cb: function (container) {
				let title = container.find('.title');
				
				if (type == 'begining') {
					title.find('[data-for="beginingSurvey"]').find('.trigger-day').text(triggerDay);	
					title.find('[data-for="beginingSurvey"]').removeClass('hide');

					if (triggerDay == 180) {
						container.find('.promotion').removeClass('hide');
					}
					else {
						container.find('.promotion').remove();
					}
				}
				else {
					title.find('[data-for="endingSurvey"]').removeClass('hide');
				}

				container.find('.survey-popup').removeClass('hide');

				// Handle button close
				container.find('.btn-close').on('click', function () {
					// Skip to next 6 hours in the same day
					let status = {
						pause_until: moment().add(6, 'hours').format('YYYY-M-D HH:mm:ss')
					};

					saveSurveyStatus(type, status);
				});

				// Handle button open form
				container.find('.btn-open-form').on('click', function () {
					// Track clicked time
					let status = {
						form_opened_at: moment().format('YYYY-M-D HH:mm:ss')
					};

					saveSurveyStatus(type, status);

					// Hide popup
					container.find('.btn-close').trigger('click');
				});
			}
		};

		app.helper.showModal(modal, modalParams);
	}

	function loadSurveyStatus (type) {
		let jsonString = '';

		if (type == 'begining') {
			jsonString = localStorage.getItem('beginingSurveyStatus');
		}
		else {
			jsonString = localStorage.getItem('endingSurveyStatus');
		}

		if (jsonString) {
			return JSON.parse(jsonString) || {};
		}

		return {};
	}

	function saveSurveyStatus (type, status) {
		if (type == 'begining') {
			localStorage.setItem('beginingSurveyStatus', JSON.stringify(status));
		}
		else {
			localStorage.setItem('endingSurveyStatus', JSON.stringify(status));
		}
	}
});