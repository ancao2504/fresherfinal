{* Added by Hieu Nguyen on 2018-08-29 to override the original ListViewPreProcesss.tpl *}

{strip}
        </div>
    </div>

    <script type="text/javascript">
        var extraFieldsInfo = {};

        var uimeta = (function() {
            var fieldInfo = {$FIELDS_INFO};

            return {
                field: {
                    get: function(name, property) {
                        if (name && property === undefined) {
                            return fieldInfo[name] ? fieldInfo[name] : extraFieldsInfo[name];
                        }

                        if (name && property) {
                            if(fieldInfo[name] && fieldInfo[name][property]) {
                                return fieldInfo[name][property];
                            } 
                            else if(extraFieldsInfo[name] && extraFieldsInfo[name][property]) {
                                return extraFieldsInfo[name][property];
                            } 
                        }
                    },
                    isMandatory: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].mandatory;
                        }

                        if (extraFieldsInfo[name]) {
                            return extraFieldsInfo[name].mandatory;
                        }
                        
                        return false;
                    },
                    getType: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].type;
                        }

                        if (extraFieldsInfo[name]) {
                            return extraFieldsInfo[name].type;
                        }

                        return false;
                    }
                },
            };
        })();
    </script>
{/strip}