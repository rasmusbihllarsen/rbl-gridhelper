jQuery(document).ready(function(){
	console.log('gridscripts');
	jQuery('.gridhelper__youtube--close-button, .gridhelper__youtube--close-overlay').on('click', function(ev, el){
		ev.preventDefault();
		var $this = jQuery(this);
		jQuery('body').removeClass('gridhelper__video-overlay');
		
		setTimeout(function(){
			jQuery('.gridhelper__youtube--inner iframe').attr('src', '');
		}, 100);
	});
	
	jQuery('.grid__item.video').on('click', function(ev, el){
		ev.preventDefault();
		var $this = jQuery(this),
			ytid = $this.data('gridhelper-yt');

		jQuery('.gridhelper__youtube--inner iframe').attr('src', 'https://www.youtube.com/embed/' + ytid + '?modestbranding=1&color=white&rel=0');

		jQuery('body').addClass('gridhelper__video-overlay');
	});
});