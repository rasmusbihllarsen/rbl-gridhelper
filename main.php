<?php
/**
 * Plugin Name: Gridhelper
 * Plugin URI:  http://rasmusbihllarsen.com
 * Description: Adds a super awesome grid feature to pages
 * Author:      Rasmus Bihl Larsen
 * Author URI:  http://rasmusbihllarsen.com
 * Version:     1.1
 * License:     DONOTSTEAL
 * Text Domain: rbl-gridhelper
 */

require('inc/post_type-grid.php');
require('inc/metaboxes.php');
require('inc/savedata.php');
require('inc/shortcode.php');

add_action('admin_enqueue_scripts', function($hook){
	if('post-new.php' != $hook && 'post.php' != $hook && 'edit-tags.php' != $hook && 'term.php' != $hook){
		return;
	}

	wp_enqueue_script('gridsterjs', plugin_dir_url(__FILE__).'js/jquery.gridster.min.js', array('jquery'));
	wp_enqueue_script('gridjs', plugin_dir_url(__FILE__).'js/grid.js', array('jquery','gridsterjs'));

	wp_enqueue_style('gridstercss', plugin_dir_url(__FILE__).'css/jquery.gridster.min.css');
	wp_enqueue_style('gridcss', plugin_dir_url(__FILE__).'css/grid.css');
});

add_action('wp_enqueue_scripts', function($hook){
	wp_enqueue_style('gridcss', plugin_dir_url(__FILE__).'css/grid-styles.css');
	wp_enqueue_script('gridjs', plugin_dir_url(__FILE__).'js/grid-scripts.js', array('jquery'));
});

add_action('wp_ajax_gridautocomplete', function() {
	$search = $_POST['gs'];

	global $gridhelper_posttypes;
	$post_types = $gridhelper_posttypes;
	$post_types['grid_items'] = __('Grid item', 'rbl-gridhelper');

	$matches = preg_grep("/".$search."/i", $post_types);
	$args = array(
		'search' => $search
	);

	$terms = get_terms('category',$args);
	$query1 = new WP_Query(array(
		'posts_per_page'	=> 10,
		'post_type'			=> array_keys($post_types),
		's'					=> $search
	));
	if($terms || $query1->posts || count($matches) > 0){
		echo '<ul class="autocomplete-result">';
			$counter = 0;
			foreach($matches AS $key => $value){
				$counter++;
				if($counter > 10){ break; }
				echo '<li data-id="p'.$key.'"><b>Type:</b> '.$value.'</li>';
			}
			foreach($terms AS $t){
				$counter++;
				if($counter > 10){ break; }
				echo '<li data-id="c'.$t->term_id.'"><b>Kategori:</b> '.$t->name.'</li>';
			}
			foreach($query1->posts AS $p){
				$counter++;

				if($counter > 10){ break; }

				$type = $post_types[$p->post_type];

				echo '<li data-id="i'.$p->ID.'"><b>'.$type.':</b> '.$p->post_title.'</li>';
			}
		echo '</ul>';
	}
	die();
});

function save_grid($post_id){
	if(!isset($_POST['gridster'])){
		return;
	}
	update_post_meta($post_id, 'gridster', $_POST['gridster']);
	update_post_meta($post_id, 'gridid', $_POST['gridid']);
	update_post_meta($post_id, 'griddata', $_POST['grid-data-value']);
	update_post_meta($post_id, 'mobilegriddata', $_POST['mobilegrid-data-value']);
}
add_action('save_post','save_grid');

add_action('edit_term',function($term_id){
	if(isset($_POST['gridster'])){
		update_option('gridster'.$term_id, $_POST['gridster']);
		update_option('gridid'.$term_id, $_POST['gridid']);
		update_option('griddata'.$term_id, $_POST['grid-data-value']);
		update_option('mobilegriddata'.$term_id, $_POST['mobilegrid-data-value']);
	}
},3);
//add_action('delete_term');

function gridhelper($post_id = 0, $mobile = false, $class = ''){
	$grid = false;

	if(is_array($mobile) && isset($mobile['id'])){
		$mobile = false;
	}

	if(is_object($post_id) && isset($post_id->ID)){
		$post_id = $post_id->ID;
	}

	if($post_id == 0 && is_category()){
		$post_id = 'c'.get_queried_object_id();
	}

	if(is_admin()){
		if($mobile){
			?>
				<input type="button" class="button" onclick="add_small_mobile();" value="<?php _e('Add block', 'rbl-gridhelper'); ?>" />
				<div id="mobilegrid">
					<ul>
			<?php
		} else {
			?>
				<input type="button" class="button" onclick="add_small();" value="<?php _e('Add block', 'rbl-gridhelper'); ?>" />
				<div id="gridster">
					<ul>
			<?php
		}
	}

	if(is_string($post_id) && substr($post_id, 0, 1) == 'c') {
		$cat_ID = substr($post_id, 1);

		if($mobile) {
			$griddata = get_option('mobilegriddata'.$cat_ID);
		} else {
			$grid = json_decode(stripcslashes(get_option('gridster'.$cat_ID)));
			$griddata = get_option('griddata'.$cat_ID);
		}
	} elseif($post_id == 0) {
		global $post;

		if(is_category()) {
			$category = get_the_category(); 
			$cat_ID = $category[0]->cat_ID;
			if($mobile){
				$griddata = get_option('mobilegriddata'.$cat_ID);
			} else {
				$grid = json_decode(stripcslashes(get_option('gridster'.$cat_ID)));
				$griddata = get_option('griddata'.$cat_ID);
			}
		} elseif($post && isset($post->ID)) {
			$post_id = $post->ID;

			if($mobile){
				$griddata = get_post_meta($post_id,'mobilegriddata',true);
			}else{
				$grid = json_decode(get_post_meta($post_id,'gridster',true));
				$griddata = get_post_meta($post_id,'griddata',true);
			}
		}else{
			return;
		}
	} else {
		if($mobile) {
			$griddata = get_post_meta($post_id,'mobilegriddata',true);
		} else {
			$grid = json_decode(get_post_meta($post_id,'gridster',true));
			$griddata = get_post_meta($post_id,'griddata',true);
		}
	}

	if(!$griddata){
		$griddata = array();
	}
	
	global $used_tiles;
	if(!$mobile){ $used_tiles = array(); }
	$tile_post = array();
	
	$max = 0;

	for($i = 0; $i < count($griddata); $i++){
		if($grid){
			$tile = $grid[$i];
			$t = ($tile->size_y-1) + $tile->row;
			if($t > $max){
				$max = $t;
			}
		}

		$obj = json_decode(stripcslashes($griddata[$i]));
		$keys = array_keys((array)$obj);
		
		foreach($keys AS $k){
			if(substr($k,0,1) == 'i'){
				$p = get_post(substr($k,1));
				if($p && $p->post_status == 'publish'){
					$tile_post['nr'.$i] = $p;
					if(!$mobile){ $used_tiles[] = $p->ID; }
					continue;
				}
			}
		}
	}

	$p = 11.7;
	$h = $max * 250;
	$r = $max * 25;
	$tiles = '';

	if(is_admin()){
		$tiles = '<style type="text/css"> #gridster:after { content:""; display:block; padding-top: 1px; } </style>';
	} else if(!$mobile){
		$tiles = '<style type="text/css"> .grid:after { padding-top: '.$r.'%; } </style>';
		$tiles .= '<div class="grid">';
	}

	$has_yt_videos = false;
	for($i = 0; $i < count($griddata); $i++){
		$size = 'mobile';
		$imgsize = 'grid_small';

		if(!$mobile){
			$tile = $grid[$i];
			if		($tile->size_x == 1 && $tile->size_y == 1){ $size = 'col25';		$imgsize = 'grid_small'; }
			elseif	($tile->size_x == 2 && $tile->size_y == 2){ $size = 'col50 tall';	$imgsize = 'grid_big';   }
			elseif	($tile->size_x == 2 && $tile->size_y == 1){ $size = 'col50';		$imgsize = 'grid_wide';  }
			elseif	($tile->size_x == 1 && $tile->size_y == 2){ $size = 'col25 tall';	$imgsize = 'grid_high';  }
		}

		if($max > 0){
			$pro = 100/$max;
		} else {
			$pro = 0;
		}

		if($mobile){
			$top = 0;
		} else {
			$top = (($tile->row*$pro)-$pro);
		}

		$p = false;

		if(isset($tile_post['nr'.$i])){
			$p = $tile_post['nr'.$i];
		} else {
			$obj = json_decode($griddata[$i]);
			$keys = array_keys((array)$obj);

			$categories = array();
			$post_types = array();

			foreach($keys AS $k){
				if(substr($k,0,1) == 'c'){ // Category
					$categories[] = substr($k,1);
				}elseif(substr($k,0,1) == 'p'){ // Post type
					$post_types[] = substr($k,1);
				}
			}

			$meta_query = false;

			if(count($post_types) == 0){
				$post_types[] = 'post';
			}

			$args = array(
				'posts_per_page'	=> 1,
				'post_type'			=> $post_types
			);

			if(!$mobile){
				$args['post__not_in'] = $used_tiles;
			}

			if(count($categories) != 0){
				$args['tax_query'] = array(array(
					'taxonomy'	=> 'category',
					'field'		=> 'term_id',
					'terms'		=> $categories
				));
			} elseif(is_category()) {
				global $wp_query;
				$term = $wp_query->get_queried_object();

				$args['tax_query'] = array(array(
					'taxonomy'	=> 'category',
					'field'		=> 'term_id',
					'terms'		=> $term->term_id
				));
			}

			if($meta_query){
				$args['meta_query'] = $meta_query;
			}

			$ps = get_posts($args);

			if($ps){
				$p = $ps[0];
				if(!$mobile){ $used_tiles[] = $p->ID; }
			} else {
				$args = array(
					'posts_per_page'	=> 1,
					'post_type'			=> 'post'
				);

				if(!$mobile){ $args['post__not_in'] = $used_tiles; }

				if(is_tax()){
					$term = $wp_query->get_queried_object();
					$args['tax_query'] = array(array(
						'taxonomy'	=> 'category',
						'field'		=> 'term_id',
						'terms'		=> $term->term_id
					));
				}

				$ps = get_posts($args);

				if($ps){
					$p = $ps[0];
				}
			}
		}

		if(is_admin()){
			$name = 'mobilegrid-data-value';
			$value = stripcslashes($griddata[$i]);
			$value_uns = (array) json_decode(strip_tags($value));
			
			$value_unstring = '';
			$vus = 1;
			foreach($value_uns as $v){
				if($vus > 1){
					$value_unstring .= '<br>';
				}
				
				$value_unstring .= $v;
				$vus++;
			}

			$li_class = ( !empty($value) ) ? 'griditem filled' : 'griditem';

			$tiles .= '
				<li class="'.$li_class.'"';
			if(!$mobile){
				$name = 'grid-data-value';
				$tiles .= ' data-row="'.$grid[$i]->row.'" data-col="'.$grid[$i]->col.'" data-sizex="'.$grid[$i]->size_x.'" data-sizey="'.$grid[$i]->size_y.'"';
			}

			$tiles .= '>
					<span class="close"></span>
					<span class="edit"></span>
					<span class="title">'.$value_unstring.'</span>
					<input type="hidden" name="'.$name.'[]" value=\''.$value.'\' />
				</li>
			';
		} elseif($p) {
			$classes = '';
			$gridhelper = $p->gridhelper;

			if( isset($gridhelper['placement_vertical']) ){
				switch($gridhelper['placement_vertical']){
					case 'top':
						$classes .= ' vtop';
						break;
					case 'middle':
						$classes .= ' vmiddle';
						break;
					case 'bottom':
						$classes .= ' vbottom';
						break;
				}
			}

			if( isset($gridhelper['placement_horizontal']) ){
				switch($gridhelper['placement_horizontal']){
					case 'left':
						$classes .= ' hleft';
						break;
					case 'middle':
						$classes .= ' hcenter';
						break;
					case 'right':
						$classes .= ' hright';
						break;
				}
			}
			
			if( isset($gridhelper['inverted']) ){
				$classes .= ' inverted';
			}

			$yt_id = 'none';
			if( isset($gridhelper['show_video']) && !empty($gridhelper['show_video']) ) {
				$yt_link = $gridhelper['yt_video'];
				$yt_parts = explode('?', $yt_link);

				foreach($yt_parts as $yt_part){
					if(substr($yt_part, 0, 2) == 'v='){
						$yt_id = str_replace('v=', '', $yt_part);
						$classes .= ' video';
						$has_yt_videos = true;
					}
				}
			}

			$tile_type = 'div';
			$tile_type_end = 'div';
			if( isset($gridhelper['link_url']) && !empty($gridhelper['link_url']) ){
				$tile_link = ($p->post_type == 'grid_items') ? $gridhelper['link_url'] : get_permalink($p->ID);
				
				$tile_type = 'a href="'.$tile_link.'"';
				$tile_type_end = 'a';
				
				if(isset($gridhelper['link_target'])){
					$tile_type .= ' target="'.$gridhelper['link_target'].'"';
				}
			}

			if($p->post_type != 'grid_items'){
				$tile_link = get_permalink($p->ID);
				
				$tile_type = 'a href="'.$tile_link.'"';
				$tile_type_end = 'a';
			}
			
			$classes .= ' post_type--'.$p->post_type;
			
			$tiles .= '<'.$tile_type.' class="grid__item '.$classes.' '.$size;
			
			if(!$mobile){
				$tiles .= ' col'.$tile->col;
			}

			$tile_styles = 'top: '.$top.'%;';

			if(has_post_thumbnail($p->ID) && isset($gridhelper['featured_bg']) || $yt_id != 'none'){
				$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($p->ID), $imgsize);

				if($yt_id != 'none'){
					$img_url = 'https://img.youtube.com/vi/'.$yt_id.'/hqdefault.jpg';
				}

				if(!empty($thumb) && isset($gridhelper['featured_bg'])){
					$img_url = $thumb[0];
				}

				$tile_styles .= 'background-image:url('.$img_url.');';
			} else if( has_post_thumbnail($p->ID) && $p->post_type != 'grid_items'){
				$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($p->ID), $imgsize);

				if(!empty($thumb)){
					$img_url = $thumb[0];
				}

				$tile_styles .= 'background-image:url('.$img_url.');';
			}

			$tiles .= '"'; //end classes

			if($yt_id != 'none'){
				$tiles .= ' data-gridhelper-yt="'.$yt_id.'"';
			}

			$tiles .= ' style="'.$tile_styles.'">';
				if( isset($gridhelper['overlay_color']) && !empty($gridhelper['overlay_color']) && isset($gridhelper['overlay_opacity']) && !empty($gridhelper['overlay_opacity']) ){
					$opacity = (int)$gridhelper['overlay_opacity'] / 100;
					$tiles .= '<div class="grid__item--overlay" style="background-color: '.$gridhelper['overlay_color'].'; opacity: '.$opacity.';"></div>';
				}

				ob_start();
				do_action( 'gridhelper_before_inner', $p );
				$tiles .= ob_get_clean();

				$tiles .= '<div class="grid__inner">';
					if($p->post_type == 'grid_items'){
						if($yt_id != 'none'){
							$tiles .= '<div class="video-icon"></div>';
						}

						if(isset($gridhelper['show_pretitle']) && $gridhelper['pretitle']){
							$tiles .= '<span class="gh_preheadline">'.$gridhelper['pretitle'].'</span>';
						}

						if(isset($gridhelper['show_title'])){
							$title = (isset($gridhelper['title']) && !empty($gridhelper['title'])) ? nl2br($gridhelper['title']) : $p->post_title;
							$tiles .= '<h2>'.apply_filters('the_title', $title, $p->ID).'</h2>';
						}
				
						if(isset($gridhelper['show_content'])){
							$tiles .= apply_filters('the_content', $p->post_content, $p->ID);
						}
					} else {
						ob_start();
						do_action( 'gridhelper_custom_content', $p );
						$tiles .= ob_get_clean();
					}

				$tiles .= '</div>';
			$tiles .= '</'.$tile_type_end.'>';
		}
	}

	if(!$mobile){
		$tiles .= '</div>';
	}

	echo $tiles;

	if($has_yt_videos){
?>
	<div class="gridhelper__youtube--overlay">
		<div class="gridhelper__youtube--close-overlay"></div>
		<div class="gridhelper__youtube--inner">
			<div class="gridhelper__youtube--close-button"></div>
			<iframe src="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
<?php
	}
	
	if(is_admin()){
		?>
				</ul>
			</div>
		<?php if(!$mobile){ ?>
			<input type="hidden" name="gridster" />
			<div id="grid-edit">
				<b class="grid-edit-heading"><?php _e('Select what to show in this box', 'rbl-gridhelper'); ?></b>
				<div id="grid-parametres">

				</div>
				<input type="text" class="autocomplete" placeholder="Søg&hellip;" />
				<button id="grid-edit-ok"><?php _e('OK', 'rbl-gridhelper'); ?></button>
			</div>
			<div id="grid-overlay"></div>
		<?php } ?>
		
		<?php
	}
}

function mobile_grid($post_id = 0){
	gridhelper($post_id, true);
}

add_action('init', function(){
	add_image_size('grid_small', 390, 260, true);
	add_image_size('grid_big', 780, 520, true);
	add_image_size('grid_wide', 780, 260, true);
	add_image_size('grid_high', 390, 520, true);
});

add_action('edit_category_form',function(){
	echo '<div>';
		echo '<h3>'.__('Grid', 'rbl-gridhelper').'</h3>';
		grid('c'.$_GET['tag_ID']);
	echo '</div>';
	
	echo '<div>';
		echo '<h3>'.__('Mobile grid', 'rbl-gridhelper').'</h3>';
		mobile_grid('c'.$_GET['tag_ID']);
	echo '</div>';
});

?>
