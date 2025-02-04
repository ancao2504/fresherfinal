/*
    PasswordValidator
    Author: Hieu Nguyen
    Date: 2020-01-08
    Purpose: provide a util class to validate the password based on the configured rules
*/

var PasswordValidator = {
    rules: _VALIDATION_CONFIG.password,

    isValid: function (password) {
        var pattern = '^(?=.{'+ this.rules.length +',})';

        if (this.rules.lower_case_characters) {
            pattern += '(?=.*[a-z])';
        }

        if (this.rules.upper_case_characters) {
            pattern += '(?=.*[A-Z])';
        }

        if (this.rules.digit_characters) {
            pattern += '(?=.*[0-9])';
        }

        if (this.rules.special_characters) {
            pattern += '(?=.*[!@#\$%\^&\*])';
        }

        return new RegExp(pattern).test(password);
    },

    validate: function (password) {
        var isValid = this.isValid(password);

        if (!isValid) {
            var message = app.vtranslate('JS_PASSWORD_VALIDATE_ERROR_MSG', 'Users');
            message += '<br>' + app.vtranslate('JS_PASSWORD_VALIDATE_RULE_MIN_LENGTH', 'Users');
            
            if (this.rules.lower_case_characters) {
                message += '<br>' + app.vtranslate('JS_PASSWORD_VALIDATE_RULE_LOWER_CASE_CHARACTER', 'Users');
            }

            if (this.rules.upper_case_characters) {
                message += '<br>' + app.vtranslate('JS_PASSWORD_VALIDATE_RULE_UPPER_CASE_CHARACTER', 'Users');
            }

            if (this.rules.digit_characters) {
                message += '<br>' + app.vtranslate('JS_PASSWORD_VALIDATE_RULE_DIGIT_CHARACTER', 'Users');
            }

            if (this.rules.special_characters) {
                message += '<br>' + app.vtranslate('JS_PASSWORD_VALIDATE_RULE_SPECIAL_CHARACTER', 'Users');
            }

            app.helper.showErrorNotification({ message: message }, { delay: 10000 });
        }

        return isValid;
    }
}