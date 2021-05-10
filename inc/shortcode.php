<?php
//[gridhelper]
function rbl_gridhelper_shortcode( $atts ){
	ob_start();
	gridhelper();
	$html = ob_get_clean();
	return $html;
}
add_shortcode( 'gridhelper', 'rbl_gridhelper_shortcode' );
