var gridster;
var xhr;
jQuery(document).ready(function () {
	setTimeout(function () {
		var width = Math.abs(jQuery("#gridster ul").width() / 4);
		var height = Math.abs((width / 390) * 250);

		gridster = jQuery("#gridster ul").gridster({
			widget_margins: [0, 0],
			widget_base_dimensions: [width, height],
			helper: 'preview-holder',
			max_cols: 4,
			min_cols: 1,
			resize: {
				enabled: true,
				max_size: [2, 2],
				min_size: [1, 1],
				stop: function () {
					grid_serialize();
				}
			},
			draggable: {
				stop: function () {
					grid_serialize();
				}
			}
		}).data('gridster');

		if (gridster) {
			grid_serialize();
		}
	}, 100);
});

jQuery(window).resize(function () {
	gridster = jQuery("#gridster ul").gridster().data('gridster');
	if (gridster) {
		var width = Math.abs(jQuery("#gridster ul").width() / 2);
		var height = Math.abs((width / 390) * 250);
	}
});

var mobilegridwidget = '<li class="griditem"><span class="close"></span><span class="edit"></span><input type="hidden" name="mobilegrid-data-value[]" value="" /></li>';
var gridwidget = '<li class="griditem"><span class="close"></span><span class="edit"></span><input type="hidden" name="grid-data-value[]" value="" /></li>';

function grid_serialize() {
	var s = gridster.serialize(),
	var js = JSON.stringify(s);

	jQuery('input[name="gridster"]').val(js);
}

function add_wide() {
	gridster.add_widget.apply(gridster, [gridwidget, 2, 1]);
	grid_serialize();
}

function add_high() {
	gridster.add_widget.apply(gridster, [gridwidget, 1, 2]);
	grid_serialize();
}

function add_small() {
	gridster.add_widget.apply(gridster, [gridwidget, 1, 1]);
	grid_serialize();
}

function add_small_mobile() {
	jQuery('#mobilegrid ul').append(mobilegridwidget);
}

function add_big() {
	gridster.add_widget.apply(gridster, [gridwidget, 2, 2]);
	grid_serialize();
}

function add_grid_parameter(id, title) {
	jQuery('#grid-parametres').append('<span class="result"><span>' + title + '</span><input type="hidden" name="gridid[]" value="' + id + '" /></span>');
}

jQuery(document).ready(function () {
	jQuery(document).on('click', '#gridster ul li span.close', function () {
		gridster.remove_widget(jQuery('#gridster li').eq(jQuery(this).parent().index() - 1));
		grid_serialize();
	});

	jQuery(document).on('click', '#mobilegrid ul li span.close', function () {
		jQuery(this).parent().remove();
	});

	jQuery(document).on('click', '#gridster ul li span.edit,#mobilegrid ul li span.edit', function () {
		jQuery('#grid-parametres').html('');
		var parenttile = jQuery(this).parents('li.griditem');
		parenttile.addClass('active');
		var jsonval = parenttile.find('input').val();
		if (jsonval != '') {
			var obj = JSON.parse(jsonval);
			jQuery.each(obj, function (k, v) {
				add_grid_parameter(k, v);
			});
		}
		jQuery('#grid-overlay, #grid-edit').addClass('active');
	});
	jQuery(document).on('focus', '#grid-edit input.autocomplete', function () {
		jQuery(this).addClass('active').keyup();
	});

	jQuery(document).on('keyup', '#grid-edit input.autocomplete', function () {
		if (jQuery(this).val().length >= 3) {
			xhr = jQuery.ajax({
				type: "POST",
				url: ajaxurl,
				data: "action=gridautocomplete&gs=" + jQuery(this).val(),
				success: function (msg) {
					jQuery('#grid-edit input.autocomplete.active').siblings('ul').remove();
					jQuery('#grid-edit input.autocomplete.active').after(msg);
				}
			});
		}
	});

	jQuery(document).on('click', '#grid-edit ul.autocomplete-result li', function () {
		add_grid_parameter(jQuery(this).attr('data-id'), jQuery(this).html());
	});

	jQuery(document).on('click', '#grid-edit #grid-parametres span.result', function () {
		jQuery(this).remove();
		jQuery(this).parents('.action-overlay').find('input.autocomplete').show().focus();
	});

	jQuery(document).on('click', '#grid-edit-ok', function (e) {
		e.preventDefault();
		var dataobj = {};
		var $text = '';
		var vui = 1;

		jQuery('#grid-parametres > span').each(function () {
			var key = jQuery(this).find('input').val();
			var val = jQuery(this).find('span').html();
			dataobj[key] = val;

			if (vui > 1) {
				$text += ', ';
			}

			$text += val;
			vui += 1;
		});

		var $val = '';
		if (dataobj) {
			$val = JSON.stringify(dataobj);
		}

		jQuery('li.griditem.active input').val($val);
		jQuery('li.griditem.active .title').html($text);

		// TODO: Hvis korrekt billede/tekst i tile
		if (dataobj) {
			jQuery('#gridster li.griditem.active, #mobilegrid li.griditem.active').addClass('filled');
		}

		jQuery('#grid-overlay, #grid-edit, #gridster li.griditem.active, #mobilegrid li.griditem.active').removeClass('active');
	});
	jQuery('#mobilegrid ul').sortable();
});
