<?php
/*
 * Metaboxes for frontpage.
 */

add_action('add_meta_boxes', 'gridhelper_frontpage_meta_boxes');
function gridhelper_frontpage_meta_boxes() {
	global $post, $gridhelper_showmeta;
	$template_file = get_post_meta($post->ID,'_wp_page_template',true);

	$gridhelper_showmeta[] = 'page';

	if($template_file != 'page-sektion.php'){
		remove_post_type_support('page','editor');
	}

	add_meta_box(
		'gridhelper',
		__('Gridhelper', 'rbl-gridhelper'),
		'gridhelper',
		$gridhelper_showmeta,
		'normal',
		'default'
	);

	/*add_meta_box(
		'mobilegrid',
		'Mobil Grid',
		'mobilegrid',
		'page',
		'side',
		'core'
	);*/
}

add_action('add_meta_boxes', 'gridhelper_item_meta_boxes');
function gridhelper_item_meta_boxes() {
	add_meta_box(
		'gridhelper_settings',
		__('Gridhelper Settings', 'rbl-gridhelper'),
		'gridhelper_settings',
		'grid_items',
		'normal',
		'high'
	);
}

function gridhelper_settings(){
	$post = get_post( get_the_ID() );
	$gridhelper = $post->gridhelper;
	?>
	<style>
		.gridhelper-settings {
			width: 100%;
		}
		
		.gridhelper-settings tr.gray {
			background-color: #efefef;
		}
		
		.gridhelper-settings th {
			width:25%;
			font-weight: 700;
			padding-right: 35px;
			text-align: left;
		}

		.gridhelper-settings small {
			display: block;
			font-weight: 400;
		}
	</style>

	<table class="gridhelper-settings" cellspacing="0" cellpadding="10" border="0">
		<tr>
			<th>
				<?php _e('Show/hide elements', 'rbl-gridhelper'); ?>
			</th>
			<td>
				<label for="gridhelper[show_title]">
					<input type="checkbox" value="1" name="gridhelper[show_title]" id="gridhelper[show_title]" <?php echo (isset($gridhelper['show_title']) && $gridhelper['show_title'] == 1) ? 'checked' : ''; ?>>
					<span class="title"><?php _e('Show title', 'rbl-gridhelper'); ?></span>
				</label>
			</td>
			<td>
				<label for="gridhelper[show_content]">
					<input type="checkbox" value="1" name="gridhelper[show_content]" id="gridhelper[show_content]" <?php echo (isset($gridhelper['show_content']) && $gridhelper['show_content'] == 1) ? 'checked' : ''; ?>>
					<span class="title"><?php _e('Show content', 'rbl-gridhelper'); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<label for="gridhelper[show_pretitle]">
					<input type="checkbox" value="1" name="gridhelper[show_pretitle]" id="gridhelper[show_pretitle]" <?php echo (isset($gridhelper['show_pretitle']) && $gridhelper['show_pretitle'] == 1) ? 'checked' : ''; ?>>
					<span class="title"><?php _e('Show Pre-title', 'rbl-gridhelper'); ?></span>
				</label>
			</td>
			<td>
				<label for="gridhelper[show_video]">
					<input type="checkbox" value="1" name="gridhelper[show_video]" id="gridhelper[show_video]" <?php echo (isset($gridhelper['show_video']) && $gridhelper['show_video'] == 1) ? 'checked' : ''; ?>>
					<span class="title"><?php _e('Show video', 'rbl-gridhelper'); ?></span>
					<small><?php _e('This will override all text settings.', 'rbl-gridhelper'); ?></small>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Background-image', 'rbl-gridhelper'); ?>
			</th>
			<td colspan="2">
				<label for="gridhelper[featured_bg]">
					<input type="checkbox" value="1" name="gridhelper[featured_bg]" id="gridhelper[featured_bg]" <?php echo (isset($gridhelper['featured_bg']) && $gridhelper['featured_bg'] == 1) ? 'checked' : ''; ?>>
					<span class="title"><?php _e('Use featured image as background', 'rbl-gridhelper'); ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Inverted item', 'rbl-gridhelper'); ?>
			</th>
			<td colspan="2">
				<label for="gridhelper[inverted]">
					<input type="checkbox" value="1" name="gridhelper[inverted]" id="gridhelper[inverted]" <?php echo (isset($gridhelper['inverted']) && $gridhelper['inverted'] == 1) ? 'checked' : ''; ?>>
					<span class="title"><?php _e('Make this grid-item different style', 'rbl-gridhelper'); ?></span>
					<small><?php _e('This will add the class .inverted to the item-wrapper.', 'rbl-gridhelper'); ?></small>
				</label>
			</td>
		</tr>
		<tr class="gray">
			<th>
				<?php _e('Pre-title', 'rbl-gridhelper'); ?>
				<small><?php _e('Will be displayed above the title.', 'rbl-gridhelper'); ?></small>
			</th>
			<td colspan="2">
				<input type="text" class="widefat" value="<?php echo (isset($gridhelper['pretitle'])) ? $gridhelper['pretitle'] : ''; ?>" name="gridhelper[pretitle]" placeholder="<?php _e('Pre-title', 'rbl-gridhelper'); ?>">
			</td>
		</tr>
		<tr class="gray">
			<th>
				<?php _e('Alternative title', 'rbl-gridhelper'); ?>
				<small><?php _e('Will override the title of the post.', 'rbl-gridhelper'); ?></small>
			</th>
			<td colspan="2">
				<textarea class="widefat" name="gridhelper[title]" cols="30" rows="3" placeholder="<?php _e('Alternative title', 'rbl-gridhelper'); ?>"><?php echo (isset($gridhelper['title'])) ? $gridhelper['title'] : ''; ?></textarea>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Placement of text', 'rbl-gridhelper'); ?>
			</th>
			<td><?php _e('Horizontal', 'rbl-gridhelper'); ?></td>
			<td>
				<select name="gridhelper[placement_horizontal]">
					<option value="left" <?php echo (isset($gridhelper['placement_horizontal']) && $gridhelper['placement_horizontal'] == 'left') ? 'selected' : ''; ?>><?php _e('Left', 'rbl-gridhelper'); ?></option>
					<option value="middle" <?php echo (isset($gridhelper['placement_horizontal']) && $gridhelper['placement_horizontal'] == 'middle') ? 'selected' : ''; ?>><?php _e('Centered', 'rbl-gridhelper'); ?></option>
					<option value="right" <?php echo (isset($gridhelper['placement_horizontal']) && $gridhelper['placement_horizontal'] == 'right') ? 'selected' : ''; ?>><?php _e('Right', 'rbl-gridhelper'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th></th>
			<td><?php _e('Vertical', 'rbl-gridhelper'); ?></td>
			<td>
				<select name="gridhelper[placement_vertical]">
					<option value="top" <?php echo (isset($gridhelper['placement_vertical']) && $gridhelper['placement_vertical'] == 'top') ? 'selected' : ''; ?>><?php _e('Top', 'rbl-gridhelper'); ?></option>
					<option value="middle" <?php echo (isset($gridhelper['placement_vertical']) && $gridhelper['placement_vertical'] == 'middle') ? 'selected' : ''; ?>><?php _e('Middle', 'rbl-gridhelper'); ?></option>
					<option value="bottom" <?php echo (isset($gridhelper['placement_vertical']) && $gridhelper['placement_vertical'] == 'bottom') ? 'selected' : ''; ?>><?php _e('Bottom', 'rbl-gridhelper'); ?></option>
				</select>
			</td>
		</tr>
		<tr class="gray">
			<th>
				<?php _e('Link', 'rbl-gridhelper'); ?>
				<small><?php _e('Enter an URL in this field, to add a link to the grid-item.', 'rbl-gridhelper'); ?></small>
			</th>
			<td colspan="2">
				<input type="text" class="widefat" value="<?php echo (isset($gridhelper['link_url'])) ? $gridhelper['link_url'] : ''; ?>" name="gridhelper[link_url]" placeholder="<?php _e('Grid-link URL', 'rbl-gridhelper'); ?>">
			</td>
		</tr>
		<tr class="gray">
			<th></th>
			<td colspan="2">
				<select name="gridhelper[link_target]">
					<option value="_self" <?php echo (isset($gridhelper['link_target']) && $gridhelper['link_target'] == '_self') ? 'selected' : ''; ?>><?php _e('Same window', 'rbl-gridhelper'); ?></option>
					<option value="_blank" <?php echo (isset($gridhelper['link_target']) && $gridhelper['link_target'] == '_blank') ? 'selected' : ''; ?>><?php _e('New window', 'rbl-gridhelper'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Overlay color', 'rbl-gridhelper'); ?>
			</th>
			<td colspan="2">
				<label for="gridhelper[overlay_color]">
					<input type="text" name="gridhelper[overlay_color]" value="<?php echo (isset($gridhelper['overlay_color'])) ? $gridhelper['overlay_color'] : ''; ?>">
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e('Overlay opacity', 'rbl-gridhelper'); ?>
			</th>
			<td colspan="2">
				<label for="gridhelper[overlay_opacity]" style="display:block;">
					<?php echo (isset($gridhelper['overlay_opacity'])) ? $gridhelper['overlay_opacity'] : '0'; ?>%
				</label>
				<input type="range" name="gridhelper[overlay_opacity]" value="<?php echo (isset($gridhelper['overlay_opacity'])) ? $gridhelper['overlay_opacity'] : '0'; ?>">
			</td>
		</tr>
		<tr class="gray">
			<th>
				<?php _e('Video', 'rbl-gridhelper'); ?>
				<small><?php _e('Insert a YouTube-URL to the chosen video.', 'rbl-gridhelper'); ?></small>
			</th>
			<td colspan="2">
				<label for="gridhelper[yt_video]">
					<input class="widefat" type="text" name="gridhelper[yt_video]" value="<?php echo (isset($gridhelper['yt_video'])) ? $gridhelper['yt_video'] : ''; ?>">
				</label>
			</td>
		</tr>
	</table>

	<script>
		jQuery(document).ready(function(){
			jQuery('input[name="gridhelper[overlay_color]"]').wpColorPicker();

			jQuery('input[name="gridhelper[overlay_opacity]"]').on('input', function () {
				var $this = jQuery(this);
				$this.prev('label').html($this.val() + '%');
			});
		});
	</script>
	<?php
}
