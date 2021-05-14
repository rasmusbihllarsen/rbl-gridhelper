<?php
//[gridhelper]
function rbl_gridhelper_shortcode( $atts ){
	$a = shortcode_atts( array(
		'post_id'	=> 0,
		'mobile'	=> false,
		'class'	=> '',
	), $atts );

	ob_start();
	gridhelper($a['post_id'], $a['mobile'], $a['class']);
	$html = ob_get_clean();
	return $html;
}
add_shortcode( 'gridhelper', 'rbl_gridhelper_shortcode' );
