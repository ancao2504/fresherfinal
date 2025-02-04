/**
 * Name: SMSNotifierHelper.js
 * Description: A helper class to handle send SMS form logic
 * Author: Phu Vo
 * Date: 2020.02.12
 */

if (typeof window.SMSNotifierHelper === 'undefined') {
    (function () {
        window.SMSNotifierHelper = new class {
            initSMSValidator() {
                this.form = $('#massSave');

                this.initSMSLimitValidator();
                this.initSMSTemplateSelector();
            }

            initSMSLimitValidator() {
                let limit = this.form.find('#message').attr('maxlength');

                this.form.find('#character-limit').text(limit);
                this.form.find('#character-remain').text(limit - this.form.find('#message').val().length);

                this.form.find('#message').on('keyup change', e => {
                    let str = this.form.find('#message').val();

                    this.form.find('#character-remain').text(limit - str.length); // Update character remain
                    this.form.find('#character-counter-container').toggleClass('text-danger', str.length >= limit); // toggle error flag
                });
            }

            initSMSTemplateSelector() {
                // Modified by Phu Vo on 2019.09.20 to fix could not fill message from template
                this.form.find('#sms-template').on('change', e => {
                    this.form.find('#message').val($(e.target).val()).trigger('change');
                });
                // End Phu Vo
            }
        }
    })();
}
