jQuery(function(){
	if (jQuery('#userpress_wiki-tags').length != 0) {
    	jQuery('#userpress_wiki-tags').suggest( wpurl + "/wp-admin/admin-ajax.php?action=wiki_tag_search",{multiple:true, multipleSep: ","});
    }
    var ajaxaction = 'wiki_title_search';
    var showsuggestions = function(data) {
        var results = false;
        jQuery('#search_results').html('');
        var html = '';
        for(i in data) {
            if(data[i].ID.toString() != jQuery('input[name=post_ID]').val()) {
                results = true;
                html += '<li><a href="' + data[i].link + '" target="_blank">' + data[i].label + '</li>';
            }
        }
        html = '<ul><li><strong>The page you are creating might already exist..</strong></li>' + html + '</ul>';
        if(results) {
            jQuery('#search_results').append(html);
            jQuery('#search_results').show();
        }
        
    }
    jQuery(document).click(function (e) {
        var container = jQuery("#search_results");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });
    if (jQuery("input#wiki_title").length != 0) {
		jQuery("input#wiki_title").autocomplete({
			delay: 0,
			minLength: 5,
			source: function(req, response){  
				jQuery.getJSON(ajaxurl+'?callback=?&action='+ajaxaction, req, function(data){
					showsuggestions(data);
					//response(data);
				});  
			},
			select: function(event, ui) {
				event.preventDefault();
				window.location.href=ui.item.link;
				return false;
			},
		});
	}
});