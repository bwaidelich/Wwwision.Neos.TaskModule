window.addEventListener('DOMContentLoaded', (event) => {
	timeago.render(document.querySelectorAll('time'));
	jQuery(function() {
		jQuery('.fold-toggle').click(function() {
			jQuery(this).toggleClass('fas fa-chevron-down fas fa-chevron-up');
			jQuery('tr.' + jQuery(this).data('toggle')).toggle();
		});
	});
});
