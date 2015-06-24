(function($){

	// OnLoad
	$(function(){

		// Wrap sections
		$('form > table').each(
			function(){
				 $(this).prevUntil('h3').andSelf().prev('h3').andSelf().wrapAll('<div class="section"/>').wrapAll('<div class="section-inner"/>');
				 $(this).prevUntil('h3').prev('h3').andSelf().wrapAll('<div class="section-header"/>');
			}
		);

		
		$('[name=custom_field_revisions_regexp]').closest('tr').hide();
		$('[name=custom_field_revisions_include_keys]').closest('tr').hide();
		$('[name=custom_field_revisions_exclude_keys]').closest('tr').hide();

		switch ($('input[name=custom_field_revisions_method]:checked').val())
		{
			case 'include_keys':
				$('[name=custom_field_revisions_include_keys]').closest('tr').show();
			break;
			case 'exclude_keys':
				$('[name=custom_field_revisions_exclude_keys]').closest('tr').show();
			break;
			case 'regexp':
				$('[name=custom_field_revisions_regexp]').closest('tr').show();
			break;
		}

		$('input[name=custom_field_revisions_method]').change(
			function(value){


				$('[name=custom_field_revisions_include_keys]').closest('tr').animate({
					opacity: 'hide'
				}, 200);

				$('[name=custom_field_revisions_include_keys]').animate({
					height: 'hide'
				}, 100);

				$('[name=custom_field_revisions_exclude_keys]').closest('tr').animate({
					opacity: 'hide'
				}, 200);

				$('[name=custom_field_revisions_exclude_keys]').animate({
					height: 'hide'
				}, 100);

				$('[name=custom_field_revisions_regexp]').closest('tr').animate({
					opacity: 'hide'
				}, 200);

				$('[name=custom_field_revisions_regexp]').animate({
					height: 'hide'
				}, 100);

				switch ($(this).val())
				{
					case 'include_keys':
						
						$('[name=custom_field_revisions_include_keys]').closest('tr').animate({
							opacity: 'show'
						}, 400);
						$('[name=custom_field_revisions_include_keys]').animate({
							height: 'show'
						}, 200);

					break;
					case 'exclude_keys':
						
						$('[name=custom_field_revisions_exclude_keys]').closest('tr').animate({
							opacity: 'show'
						}, 400);
						$('[name=custom_field_revisions_exclude_keys]').animate({
							height: 'show'
						}, 200);

					break;
					case 'regexp':

						$('[name=custom_field_revisions_regexp]').closest('tr').animate({
							opacity: 'show'
						}, 400);

						$('[name=custom_field_revisions_regexp]').animate({
							height: 'show'
						}, 200);

					break;
					default:
					break;
				}
			}
		);
/*
		$('input [name=custom_field_revisions_method[method]]').live('change', function(){
			alert('asd');
		});
*/
	});

})(jQuery);
