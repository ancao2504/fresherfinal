/**
 * Name: CustomColorPicker.js
 * Author: Phu Vo
 * Date: 2021.10.29
 * Dependencies: jQuery, select2
 */

($ => {
	let colors = [
		'#E9E9E9',
		'#FFFACD',
		'#FFE501',
		'#FFA600',
		'#CD853F',
		'#F17030',
		'#FF6347',
		'#DE425B',
		'#CD5C5C',
		'#F47C7C',
		'#FF69B4',
		'#DA70D6',
		'#9370DB',
		'#9E579D',
		'#3D84A8',
		'#008ECF',
		'#08B8B4',
		'#35BF8E',
		'#9ACD32',
		'#BDB76B',
	];

	let colorOptions = colors.map(color => {
		if (color.id) return { id: color.id, text: color.text };
		return { id: color, text: color };
	});

	let getTextColor = hex => {
		let contrast = app.helper.getColorContrast(hex);
		let textColor = (contrast === 'dark') ? 'white' : 'black';

		return textColor;
	}

	$.fn.customColorPicker = function (...params) {
		let colorInput = $(this);
		colorInput.parent().addClass('position-relative');

		// Modified by Vu Mai on 2022-01-30 to restyle and append icon checked in color is selected
		let colorPicker = colorInput.select2({
			minimumResultsForSearch: Infinity, // Hide search bar
			data: colorOptions,
			containerCssClass : "color-picker",
			dropdownCssClass: "color-picker-dropdown",
			formatResult: function (object, container) {
				container.addClass('color-item');
				container.attr('data-value', object.id);
				
				container.css({
					backgroundColor: object.id,
					color: getTextColor(object.id),
				});

				return '';
			},
			formatSelection: function(object, container) {
				let chosen = container.closest('.select2-chosen');
				
				chosen.css({
					backgroundColor: object.id,
					color: getTextColor(object.id),
				});

				return '';
			},
			escapeMarkup: function(m) { return m; },
		});

		$(this).on('select2-open', function() {
			selectedOption = $(this).val();
	
			$('.color-item').removeClass('active');
			$(`.color-item[data-value=${selectedOption}]`).addClass('active');
			$(`.color-item[data-value=${selectedOption}]`).html('<i class="fa-light fa-circle-check"></i>');
		})

		return colorPicker;
	}

	$.fn.customColorPicker.colors = colors;

	$(() => {
		$('.customColorPicker').customColorPicker();
	});
})(jQuery);
