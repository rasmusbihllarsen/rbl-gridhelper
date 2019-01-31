<?php
/**
 * Plugin Name: Gridhelper
 * Plugin URI:  http://rasmusbihllarsen.com
 * Description: Adds a super awesome grid feature to pages
 * Author:      Rasmus Bihl Larsen
 * Author URI:  http://rasmusbihllarsen.com
 * Version:     1.0
 * License:     DONOTSTEAL
 */
 


add_action('admin_enqueue_scripts',function($hook){
	if('post.php' != $hook && 'edit-tags.php' != $hook && 'term.php' != $hook){
		return;
	}
	wp_enqueue_script('gridsterjs',plugin_dir_url(__FILE__).'js/jquery.gridster.with-extras.min.js',array('jquery'));
	wp_enqueue_script('gridjs',plugin_dir_url(__FILE__).'js/grid.js',array('jquery','gridsterjs'));
	wp_enqueue_style('gridstercss',plugin_dir_url(__FILE__).'css/jquery.gridster.min.css');
	wp_enqueue_style('gridcss',plugin_dir_url(__FILE__).'css/grid.css');
});

/*
function mh_grid(){
	global $post;
	?>
		<input type="button" class="button" onclick="add_small();" value="Tilføj blok" />
		<div id="gridster">
			<ul>
				<?php
					$gridid = get_post_meta($post->ID,'gridid',true);
					$gridster = get_post_meta($post->ID,'gridster',true);
					$griddata = get_post_meta($post->ID,'griddata',true);
					$gridster = json_decode($gridster);
					for($i = 0; $i < count($gridster); $i++){
						$title = '';
						echo '
							<li class="griditem" data-row="'.$gridster[$i]->row.'" data-col="'.$gridster[$i]->col.'" data-sizex="'.$gridster[$i]->size_x.'" data-sizey="'.$gridster[$i]->size_y.'">
								<span class="close"></span>
								<span class="edit"></span>
								<input type="hidden" name="grid-data-value[]" value=\''.$griddata[$i].'\' />
							</li>
						';
					}
				?>
			</ul>
		</div>
		<input type="hidden" name="gridster" />
		<div id="grid-edit">
			<div id="grid-parametres">
				
			</div>
			<button id="grid-edit-ok" style="float:right;">Ok</button>
			<input type="text" class="autocomplete" />
		</div>
		<div id="grid-overlay"></div>
	<?php
}
*/
add_action('add_meta_boxes',function(){
	global $post;
	$template_file = get_post_meta($post->ID,'_wp_page_template',true);
  if($template_file == 'page-forside.php' || $post->post_name == 'forside' || $template_file == 'page-sektion.php'){
		if($template_file != 'page-sektion.php'){
			remove_post_type_support('page','editor');
		}
		add_meta_box(
			'grid',
			'Grid',
			'grid',
			'page',
			'normal',
			'high'
		);
		add_meta_box(
			'mobilegrid',
			'Mobil Grid',
			'mobilegrid',
			'page',
			'side',
			'core'
		);
	}
},1);
add_action('wp_ajax_gridautocomplete',function(){
	$search = $_POST['gs'];
	$post_types = array(
		'post' => 'Artikel',
		'interview' => 'Interview',
		'brevkasse' => 'Brevkasse',
		'page' => 'Side'
	);
	$matches = preg_grep("/".$search."/i", $post_types);
	$args = array(
		'search' => $search
	);
	$terms = get_terms('category',$args);
	$query1 = new WP_Query(array(
		'posts_per_page'	=> 10,
		'post_type'	=> array_keys($post_types),
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
				$type = 'Artikel';
				if($p->post_type == 'brevkasse'){
					$type = 'Brevkasse';
				}elseif(get_post_meta($p->ID,'is_interview',true)){
					$type = 'Interview';
				}elseif($p->post_type == 'page'){
					$type = 'Side';
				}
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
	update_post_meta($post_id,'gridster',$_POST['gridster']);
	update_post_meta($post_id,'gridid',$_POST['gridid']);
	update_post_meta($post_id,'griddata',$_POST['grid-data-value']);
	update_post_meta($post_id,'mobilegriddata',$_POST['mobilegrid-data-value']);
}
add_action('save_post','save_grid');

add_action('edit_term',function($term_id){
	if(isset($_POST['gridster'])){
		update_option('gridster'.$term_id,$_POST['gridster']);
		update_option('gridid'.$term_id,$_POST['gridid']);
		update_option('griddata'.$term_id,$_POST['grid-data-value']);
		update_option('mobilegriddata'.$term_id,$_POST['mobilegrid-data-value']);
	}
},3);
//add_action('delete_term');

function grid($post_id=0,$mobile=false){
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
				<input type="button" class="button" onclick="add_small_mobile();" value="Tilføj blok" />
				<div id="mobilegrid">
					<ul>
			<?php
		}else{
			?>
				<input type="button" class="button" onclick="add_small();" value="Tilføj blok" />
				<div id="gridster">
					<ul>
			<?php
		}
	}
	if(is_string($post_id) && substr($post_id,0,1) == 'c'){
		$cat_ID = substr($post_id,1);
		if($mobile){
			$griddata = get_option('mobilegriddata'.$cat_ID);
		}else{
			$grid = json_decode(stripcslashes(get_option('gridster'.$cat_ID)));
			$griddata = get_option('griddata'.$cat_ID);
		}
	}elseif($post_id == 0){
		global $post;
		if(is_category()){
			$category = get_the_category(); 
			$cat_ID = $category[0]->cat_ID;
			if($mobile){
				$griddata = get_option('mobilegriddata'.$cat_ID);
			}else{
				$grid = json_decode(stripcslashes(get_option('gridster'.$cat_ID)));
				$griddata = get_option('griddata'.$cat_ID);
			}
		}elseif($post && isset($post->ID)){
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
	}else{
		if($mobile){
			$griddata = get_post_meta($post_id,'mobilegriddata',true);
		}else{
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
	$h = $max*250;
	$r = $h/$p;
	$tiles = '';
	if(!$mobile){
		$tiles = '<style type="text/css"> .grid:after { padding-top: '.$r.'%; } </style>';
	}
	for($i = 0; $i < count($griddata); $i++){
		
		
		$size = 'mobile';
		$imgsize = 'grid_small';
		if(!$mobile){
			$tile = $grid[$i];
					if($tile->size_x == 1 && $tile->size_y == 1){ $size = 'col33';			$imgsize = 'grid_small'; }
			elseif($tile->size_x == 2 && $tile->size_y == 2){ $size = 'col66 tall';	$imgsize = 'grid_big'; }
			elseif($tile->size_x == 2 && $tile->size_y == 1){ $size = 'col66';			$imgsize = 'grid_wide'; }
			elseif($tile->size_x == 1 && $tile->size_y == 2){ $size = 'col33 tall';	$imgsize = 'grid_high'; }
		}
		if($max > 0){
			$pro = 100/$max;
		}else{
			$pro = 0;
		}
		if($mobile){
			$top = 0;
		}else{
			$top = (($tile->row*$pro)-$pro);
		}
		$p = false;
		if(isset($tile_post['nr'.$i])){
			$p = $tile_post['nr'.$i];
		}else{
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
			}elseif(in_array('interview',$post_types) && !in_array('post',$post_types)){
				$post_types[] = 'post';
				$meta_query = array(array(
					'key'			=> 'is_interview',
					'value'		=> '1',
					'compare'	=> '='
				));
			}
			
			$args = array(
				'posts_per_page'	=> 1,
				'post_type'				=> $post_types
			);
			if(!$mobile){ $args['post__not_in'] = $used_tiles; }
			if(count($categories) != 0){
				$args['tax_query'] = array(array(
					'taxonomy'	=> 'category',
					'field'			=> 'term_id',
					'terms'			=> $categories
				));
			}elseif(is_category()){
				global $wp_query;
				$term = $wp_query->get_queried_object();
				$args['tax_query'] = array(array(
					'taxonomy'	=> 'category',
					'field'			=> 'term_id',
					'terms'			=> $term->term_id
				));
			}
			if($meta_query){
				$args['meta_query'] = $meta_query;
			}
			$ps = get_posts($args);
			if($ps){
				$p = $ps[0];
				if(!$mobile){ $used_tiles[] = $p->ID; }
			}else{
				$args = array(
					'posts_per_page'	=> 1,
					'post_type'				=> 'post'
				);
				if(!$mobile){ $args['post__not_in'] = $used_tiles; }
				if(is_tax()){
					$term = $wp_query->get_queried_object();
					$args['tax_query'] = array(array(
						'taxonomy'	=> 'category',
						'field'			=> 'term_id',
						'terms'			=> $term->term_id
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
			$tiles .= '
				<li class="griditem"';
			if(!$mobile){
				$name = 'grid-data-value';
				$tiles .= ' data-row="'.$grid[$i]->row.'" data-col="'.$grid[$i]->col.'" data-sizex="'.$grid[$i]->size_x.'" data-sizey="'.$grid[$i]->size_y.'"';
			}
			$tiles .= '>
					<span class="close"></span>
					<span class="edit"></span>
					<input type="hidden" name="'.$name.'[]" value=\''.stripcslashes($griddata[$i]).'\' />
				</li>
			';
		}elseif($p){
			$tiles .= '<a href="'.get_permalink($p->ID).'" class="'.$size;
			if(!$mobile){
				$tiles .= ' col'.$tile->col;
			}
			$tiles .= '" style="top: '.$top.'%;">';
			$image = false;
			if(has_post_thumbnail($p->ID)){
				$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($p->ID),$imgsize);
				$url = $thumb[0];
				$tiles .= '<div class="grid-image" style="background-image:url('.$url.')"></div>';
				$image = true;
			}elseif($p->post_type == 'brevkasse'){
				$url = get_template_directory_uri().'/library/images/brevkasse-'.$imgsize.'.jpg';
				$tiles .= '<div class="grid-image" style="background-image:url(\''.$url.'\')"></div>';
				$image = true;
			}else{
				$terms = wp_get_post_terms($p->ID,'category');
				foreach($terms AS $ter){
					$imgid = get_tax_meta($ter->term_id,'featured_image');
					if($imgid){
						$url = wp_get_attachment_image_src($imgid['id'],$imgsize)[0];
						$tiles .= '<div class="grid-image" style="background-image: url(\''.$url.'\');"></div>';
						$image = true;
						break;
					}
				}
			}
			if(!$image){
				$tiles .= '<div class="grid-noimage"></div>';
			}
				$tiles .= '<div class="grid-inner">';
					$themetext = get_post_meta($p->ID,'article-category',true);
					if(!$themetext || $themetext == ''){
						if($p->post_type == 'brevkasse'){
							$themetext = 'Brevkassen';
						}else{
							$terms = wp_get_post_terms($p->ID,'category',array('fields' => 'names'));
							$themetext = 'Tema: ';
							if($terms){
								$themetext .= implode(', ',$terms);
							}else{
								$themetext .= 'Generelt';
							}
						}
					}
					$tiles .= '<div class="box-header">'.$themetext.'</div>';
					$tiles .= '<p class="h2">'.apply_filters('the_title',$p->post_title,$p->ID).'</p>';
					$tiles .= '<span class="icon-arrow"></span>';
				$tiles .= '</div>';
			$tiles .= '</a>';
		}
	}
	
	// Mark locked
	
	echo $tiles;
	
	if(is_admin()){
		?>
				</ul>
			</div>
		<?php
			if(!$mobile){
				?>
					<input type="hidden" name="gridster" />
					<div id="grid-edit">
						<b class="grid-edit-heading">Hvad skal der vises i denne boks?</b>
						<div id="grid-parametres">
							
						</div>
						<input type="text" class="autocomplete" placeholder="Søg&hellip;" />
						<button id="grid-edit-ok">Ok</button>
					</div>
					<div id="grid-overlay"></div>
				<?php
			}
		?>
		
		<?php
	}
}
function mobilegrid($post_id=0){
	grid($post_id,true);
}

add_action('init',function(){
	add_image_size('grid_small',390,260,true);
	add_image_size('grid_big',780,520,true);
	add_image_size('grid_wide',780,260,true);
	add_image_size('grid_high',390,520,true);
});

add_action('edit_category_form',function(){
	echo '<div>';
		echo '<h3>Grid</h3>';
		grid('c'.$_GET['tag_ID']);
	echo '</div>';
	
	echo '<div>';
		echo '<h3>Mobil Grid</h3>';
		mobilegrid('c'.$_GET['tag_ID']);
	echo '</div>';
});



//Echo grid:
// grid()


//Echo mobile-grid:
// mobilegrid()

?>
