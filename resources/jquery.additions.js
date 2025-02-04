;(function($) {
	$.fn.disable = function() {
		this.attr('disabled', 'disabled');
	}
	$.fn.enable = function() {
		this.removeAttr('disabled');
	}
})(jQuery);

;(function($){
	$.fn.serializeFormData = function() {
		var form = $(this);
		var values = form.serializeArray();
		var data = {};				
		if (values) {
			$(values).each(function(k,v){
				if(v.name in data && (typeof data[v.name] != 'object')) {
					var element = form.find('[name="'+v.name+'"]');
					//Only for muti select element we need to send array of values
					if(element.is('select') && element.attr('multiple')!=undefined) {
						var prevValue = data[v.name];
						data[v.name] = new Array();
						data[v.name].push(prevValue)
					}
				}
				if(typeof data[v.name] == 'object' ) {
					data[v.name].push(v.value);
				}else{
					data[v.name]=v.value;
				}				
			});
		}
		// If data-type="autocomplete", pickup data-value="..." set
		var autocompletes = $('[data-type="autocomplete"]', $(this));
		$(autocompletes).each(function(i){
			var ac = $(autocompletes[i]);
			data[ac.attr('name')] = ac.data('value');
		});		
		return data;
	}
	
})(jQuery);

;(function($) {
	// Case-insensitive :icontains expression
	$.expr[':'].icontains = function(obj, index, meta, stack){
		return (obj.textContent || obj.innerText || jQuery(obj).text() || '').toLowerCase().indexOf(meta[3].toLowerCase()) >= 0;
	}
})(jQuery);

// Added by Phu Vo on 2021.12.14 to support serialize multiple level form
;(function ($) {
	$.fn.deepSerializeFormData = function () {
		var form = $(this);
		var obj = {};
		var formData = new FormData(form[0]);

		var coerce_types = {
			'true': !0,
			'false': !1,
			'null': null
		};

		/**
		 * Get the input value from the formData by key
		 * @return {mixed}
		 */
		var getValue = function (formData, key) {
			var val = formData.get(key);
			
			/* Commented out by Hieu Nguyen on 2022-06-22 to fix bug return wrong value for long number
			val = val && !isNaN(val) ? +val // number
				:
				val === 'undefined' ? undefined // undefined
				:
				coerce_types[val] !== undefined ? coerce_types[val] // true, false, null
				:
				val; // string
			*/

			return val;
		}

		for (var key of formData.keys()) {
			var val = getValue(formData, key);
			var cur = obj;
			var i = 0;
			var keys = key.split('][');
			var keys_last = keys.length - 1;

			if (/\[/.test(keys[0]) && /\]$/.test(keys[keys_last])) {
				keys[keys_last] = keys[keys_last].replace(/\]$/, '');
				keys = keys.shift().split('[').concat(keys);
				keys_last = keys.length - 1;
			}
			else {
				keys_last = 0;
			}

			if (keys_last) {
				for (; i <= keys_last; i++) {
					key = keys[i] === '' ? cur.length : keys[i];
					cur = cur[key] = i < keys_last ?
						cur[key] || (keys[i + 1] && isNaN(keys[i + 1]) ? {} : []) :
						val;
				}
			}
			else {
				if (Array.isArray(obj[key])) {
					obj[key].push(val);
				}
				else if (obj[key] !== undefined) {
					obj[key] = [obj[key], val];
				}
				else {
					obj[key] = val;
				}
			}
		}

		return obj;
	}
})(jQuery);