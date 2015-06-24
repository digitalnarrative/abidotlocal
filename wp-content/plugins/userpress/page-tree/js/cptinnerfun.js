jQuery(document).ready(function()
	{
		
		/* The output is ment to update the nestableMenu-output textarea
		 * So this could probably be rewritten a bit to only run the menu_updatesort function onchange
		*/
		
		var updateOutput = function(e)
		{
			var list   = e.length ? e : jQuery(e.target),
				output = list.data('output');
			if (window.JSON) {
				if (output !== undefined) {
				output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
				menu_updatesort(window.JSON.stringify(list.nestable('serialize')));
				//alert(window.JSON.stringify(list.nestable('serialize')));
				}
			} else {
				output.val('JSON browser support required for this demo.');
			}
			var newparent = jQuery('.dd-item.newitem').parents('li.dd-item').attr('data-id');
			if (newparent) {
				jQuery('input#parent_id').val(newparent);
				}
		};
		
		// activate Nestable for list menu
		jQuery('#nestableMenu').nestable({
			group: 1
		})
		.on('change', updateOutput);

		jQuery('#sortDBfeedback').on('change', function() {
			console.log(jQuery('#sortDBfeedback').text());
		});
		
		// output initial serialised data
		updateOutput(jQuery('#nestableMenu').data('output', jQuery('#nestableMenu-output')));

		jQuery('#nestableMenu').on('click', function(e)
		{
			var target = jQuery(e.target),
				action = target.data('action');
			if (action === 'expand-all') {
				jQuery('.dd').nestable('expandAll');
			}
			if (action === 'collapse-all') {
				jQuery('.dd').nestable('collapseAll');
			}
			
		});

		jQuery('#nestable3').nestable();
		
		jQuery('.dd').nestable('collapseAll');
			

		margtop = jQuery('body').css('margin-top');
		
		jQuery('html > head').append(jQuery('<style>.dd-dragel { margin-top:-'+margtop+'; }</style>'));
		
		jQuery('li.checked').parents('li.dd-item').each(function() {
			jQuery(this).children('ol').show();
			jQuery(this).removeClass('dd-collapsed');
			jQuery(this).children('[data-action="expand"]').hide();
			jQuery(this).children('[data-action="collapse"]').show();
		});
		
		jQuery('li.checked').each(function() {
			jQuery(this).children('ol').show();
			jQuery(this).removeClass('dd-collapsed');
			jQuery(this).children('[data-action="expand"]').hide();
			jQuery(this).children('[data-action="collapse"]').show();
		});
		jQuery("input#wiki_title").keyup(function(event) {
       		var stt= jQuery(this).val();
        	jQuery("#newitem.newitem > span").text(stt);
		});

	});