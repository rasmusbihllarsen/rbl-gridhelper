<?php
	function gridhelper_save_postdata($post_id)
	{
		update_post_meta(
			$post_id,
			'gridhelper',
			$_POST['gridhelper']
		);
	}
	add_action('save_post', 'gridhelper_save_postdata');