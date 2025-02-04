/*
	StringUtils
	Author: Hieu Nguyen
	Date: 15/04/2013
	Purpose: to manipulate string
*/

// Check if a string contains any unicode chacracters
String.prototype.isUnicode = function() { 
    for(var i = 0; i < this.length; i++){
        if(this.charCodeAt(i) >= 192){
            return true; 
        }
    }
    return false;
}

// parse unicode string to un-unicode string
String.prototype.unUnicode = function() {
	var result = this.toLowerCase();
	result = result.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a");
	result = result.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e");
	result = result.replace(/ì|í|ị|ỉ|ĩ/g,"i");
	result = result.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o");
	result = result.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u");
	result = result.replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y");
	result = result.replace(/đ/g,"d");
	return result;
}

// match un-unicode lookup string in unicode full string
String.prototype.unUnicodeMatch = function(lookupString) {
	var fullString = this.unUnicode();
	lookupString = lookupString.unUnicode();
	return fullString.indexOf(lookupString) >= 0;
}

// Format a string with ordered params or key params
String.prototype.format = function(params) {
    params = typeof params === 'object' ? params : Array.prototype.slice.call(arguments, 1);

    return this.replace(/\{\{|\}\}|\{(\w+)\}/g, function (m, n) {
        if (m == "{{") { return "{"; }
        if (m == "}}") { return "}"; }
        return params[n];
    });
};

// Detemine if a string is a valid json string or not
String.prototype.isJsonString = function() {
    try {
        JSON.parse(this);
        return true;
    }
    catch (e) {
        return false;
    }
}