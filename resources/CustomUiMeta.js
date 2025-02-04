/*
    CustomUiMeta
    Author: Hieu Nguyen
    Date: 2020-09-08
    Purpose: provide an empty uimeta that can be set fields meta later for custom views that are missing this object like entrypoint
*/

var uimeta = (function () {
    var fieldInfo = {};

    return {
        field: {
            get: function (name, property) {
                if (name && property === undefined) {
                    return fieldInfo[name];
                }

                if (name && property) {
                    return fieldInfo[name][property];
                }
            },
            set: function(fieldData) {
                Object.keys(fieldData).map(function (fieldName, index) {
                    fieldInfo[fieldName] = fieldData[fieldName];
                });
            },
            isMandatory: function (name) {
                if (fieldInfo[name]) {
                    return fieldInfo[name].mandatory;
                }

                return false;
            },
            getType: function (name) {
                if (fieldInfo[name]) {
                    return fieldInfo[name].type;
                }

                return false;
            }
        },
    };
})();