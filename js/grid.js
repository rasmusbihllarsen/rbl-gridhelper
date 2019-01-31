/*(function($) {
    fn.generate_stylesheet = function(opts) {
        var styles = '';
        var max_size_x = this.options.max_size_x;
        var max_rows = 0;
        var max_cols = 0;
        var i;
        var rules;

        opts || (opts = {});
        opts.cols || (opts.cols = this.cols);
        opts.rows || (opts.rows = this.rows);
        opts.namespace || (opts.namespace = this.options.namespace);
        opts.widget_base_dimensions || (opts.widget_base_dimensions = this.options.widget_base_dimensions);
        opts.widget_margins || (opts.widget_margins = this.options.widget_margins);
        opts.min_widget_width = (opts.widget_margins[0] * 2) +
            opts.widget_base_dimensions[0];
        opts.min_widget_height = (opts.widget_margins[1] * 2) +
            opts.widget_base_dimensions[1];


        for (i = opts.cols; i >= 0; i--) {
            styles += (opts.namespace + ' [data-col="'+ (i + 1) + '"] { left:' +
                ((i * opts.widget_base_dimensions[0]) +
                (i * opts.widget_margins[0]) +
                ((i + 1) * opts.widget_margins[0])) + 'px;} ');
        }

        for (i = opts.rows; i >= 0; i--) {
            styles += (opts.namespace + ' [data-row="' + (i + 1) + '"] { top:' +
                ((i * opts.widget_base_dimensions[1]) +
                (i * opts.widget_margins[1]) +
                ((i + 1) * opts.widget_margins[1]) ) + 'px;} ');
        }

        for (var y = 1; y <= opts.rows; y++) {
            styles += (opts.namespace + ' [data-sizey="' + y + '"] { height:' +
                (y * opts.widget_base_dimensions[1] +
                (y - 1) * (opts.widget_margins[1] * 2)) + 'px;}');
        }

        for (var x = 1; x <= max_size_x; x++) {
            styles += (opts.namespace + ' [data-sizex="' + x + '"] { width:' +
                (x * opts.widget_base_dimensions[0] +
                (x - 1) * (opts.widget_margins[0] * 2)) + 'px;}');
        }

        return this.add_style_tag(styles);
    };

    fn.add_style_tag = function(css) {
        var d = document;
        var tag = d.createElement('style');

        tag.setAttribute('generated-from', 'gridster');

        d.getElementsByTagName('head')[0].appendChild(tag);
        tag.setAttribute('type', 'text/css');

        if (tag.styleSheet) {
            tag.styleSheet.cssText = css;
        } else {
            tag.appendChild(document.createTextNode(css));
        }
        return this;
    };

    fn.resize_widget_dimensions = function(options) {
        if (options.widget_margins) {
            this.options.widget_margins = options.widget_margins;
        }

        if (options.widget_base_dimensions) {
             this.options.widget_base_dimensions = options.widget_base_dimensions;
        }

        this.min_widget_width  = (this.options.widget_margins[0] * 2) + this.options.widget_base_dimensions[0];
        this.min_widget_height = (this.options.widget_margins[1] * 2) + this.options.widget_base_dimensions[1];

        var serializedGrid = this.serialize();
        this.$widgets.each(jQuery.proxy(function(i, widget) {
            var $widget = jQuery(widget);
            this.resize_widget($widget);
        }, this));

        this.generate_grid_and_stylesheet();
        this.get_widgets_from_DOM();
        this.set_dom_grid_height();

        return false;
    };
})(jQuery);*/
var gridster;
var xhr;
jQuery(function () {
	var width = Math.abs(jQuery("#gridster ul").width() / 3);
	var height = Math.abs((width / 390) * 250);
	gridster = jQuery("#gridster ul").gridster({
		widget_margins: [0, 0],
		widget_base_dimensions: [width, height],
		helper: 'preview-holder',
		max_cols: 3,
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
});
jQuery(window).resize(function () {
	gridster = jQuery("#gridster ul").gridster().data('gridster');
	if (gridster) {
		var width = Math.abs(jQuery("#gridster ul").width() / 3);
		var height = Math.abs((width / 390) * 250);
		//console.log(width+' : '+height);
		/*
			gridster.resize_widget_dimensions({
				widget_base_dimensions: [width,height],
				widget_margins:[0,0]
			});
		*/
	}
});
var mobilegridwidget = '<li class="griditem"><span class="close"></span><span class="edit"></span><input type="hidden" name="mobilegrid-data-value[]" value="" /></li>';
var gridwidget = '<li class="griditem"><span class="close"></span><span class="edit"></span><input type="hidden" name="grid-data-value[]" value="" /></li>';

function grid_serialize() {
	var s = gridster.serialize();
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
		gridster.remove_widget(jQuery('#gridster li').eq(jQuery(this).parent().index()));
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
		jQuery('#grid-overlay,#grid-edit').addClass('active');
	});
	jQuery(document).on('focus', '#grid-edit input.autocomplete', function () {
		jQuery(this).addClass('active').keyup();
	});
	/*
		jQuery(document).on('blur','#grid-edit input.autocomplete',function(){
			setTimeout(function(){
				jQuery('#grid-edit input.autocomplete').removeClass('active');
				jQuery('#grid-edit input.autocomplete').siblings('ul').remove();
			}, 100);
		});
	*/
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
	/*
	jQuery(document).on('keydown','#gridster ul li .autocomplete',function(){
		if(xhr !== undefined){
			xhr.abort();
		}
	});
	*/
	jQuery(document).on('click', '#grid-edit ul.autocomplete-result li', function () {
		//alert(jQuery(this).attr('data-id'));
		add_grid_parameter(jQuery(this).attr('data-id'), jQuery(this).html());
		/*
			jQuery(this).parents('.action-overlay').find('input[name="gridid[]"]').val(jQuery(this).attr('data-id'));
			jQuery(this).parents('.action-overlay').find('.result span').html(jQuery(this).html());
			jQuery(this).parents('.action-overlay').find('.result').show();
			jQuery(this).parents('.action-overlay').find('input.autocomplete').blur().hide();
		*/
	});
	jQuery(document).on('click', '#grid-edit #grid-parametres span.result', function () {
		jQuery(this).remove();
		jQuery(this).parents('.action-overlay').find('input.autocomplete').show().focus();
	});
	jQuery(document).on('click', '#grid-edit-ok', function (e) {
		e.preventDefault();
		var dataobj = {};
		jQuery('#grid-parametres > span').each(function () {
			var key = jQuery(this).find('input').val();
			var val = jQuery(this).find('span').html();
			dataobj[key] = val;
		});
		jQuery('li.griditem.active input').val(JSON.stringify(dataobj));
		// TODO: Hvis korrekt billede/tekst i tile
		jQuery('#grid-overlay,#grid-edit,#gridster li.griditem.active,#mobilegrid li.griditem.active').removeClass('active');
	});
	jQuery('#mobilegrid ul').sortable();
});
