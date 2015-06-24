jQuery(document).ready(function(){
	
	jQuery(document).on('mouseenter','div.bpsubbutton.subscribed', function () {
			jQuery( this ).text('Unsubscribe');
		});
	jQuery(document).on('mouseleave','div.bpsubbutton.subscribed', function () {
			jQuery( this ).text('Subscribed');
		});
	
	jQuery(".bpsbuttoncontainer").on("click","div.bpsubbutton.subscribed",
		function() {
			jQuery(this).fadeOut( "fast", function() {
				jQuery(this).next( "div.bpsauscontainer" ).fadeIn();
			});
		});
	jQuery(".bpsbuttoncontainer").on("click","div.bpsubbutton.delete",
		function() {
			jQuery(this).fadeOut( "fast", function() {
				jQuery(this).next( "div.bpsauscontainer" ).fadeIn();
			});
		});
	jQuery(".bpsbuttoncontainer").on("click","div.bpsunsubno",
		function() {
			jQuery(this).closest( "div.bpsauscontainer" ).fadeOut( "fast", function() {
				jQuery(this).parents("div.bpsbuttoncontainer").children("div.bpsubbutton").fadeIn();
			});
		});
		
	jQuery(".bpsbuttoncontainer").on("click","div.bpsunsubyes",
		function() {
			var post_id = jQuery(this).attr("data-post_id")
			var user_id = jQuery(this).attr("data-user_id")
			var blog_id = jQuery(this).attr("data-blog_id")
      		var nonce = jQuery(this).attr("data-nonce")
      		var substatus = jQuery(this).attr("data-sub-status")
      		if (jQuery(this).parents("div.bpsbuttoncontainer").children("div.bpsubbutton").hasClass('delete')) {
      			var mode = 'delete';
      		} else {
      			var mode = '';
      		}
      		
			var data = {
				action: 'bps_ajax_subscription_handler',
				bps_sub_status: substatus,
				user_id: user_id,
				post_id: post_id,
				blog_id: blog_id,
				nonce: nonce
			};
			jQuery.ajax({
            		type: 'POST',
            		url: ajaxurl,
            		data: data,
            		
            		success: function(response) {  
        				//alert('Got this from the server: ' + response);
            		}
            });

			if (mode == 'delete') {
				jQuery(this).parents("tr").fadeOut();
			} else {
				jQuery(this).closest( "div.bpsauscontainer" ).fadeOut( "fast", function() {
					jQuery(this).parents("div.bpsbuttoncontainer").children("div.bpsubbutton").fadeIn();
				});
				jQuery(this).parents("div.bpsbuttoncontainer").children("div.bpsubbutton").removeClass('subscribed').addClass('unsubscribed').text('Subscribe').attr('data-sub-status', 'sub');
			};
		});
		
	jQuery(".bpsbuttoncontainer").on("click","div.bpsbutton.unsubscribed",
		function() {
			var post_id = jQuery(this).attr("data-post_id")
			var user_id = jQuery(this).attr("data-user_id")
			var blog_id = jQuery(this).attr("data-blog_id")
      		var nonce = jQuery(this).attr("data-nonce")
      		var substatus = jQuery(this).attr("data-sub-status")
      		
			var data = {
				action: 'bps_ajax_subscription_handler',
				bps_sub_status: substatus,
				user_id: user_id,
				post_id: post_id,
				blog_id: blog_id,
				nonce: nonce
				
			};
			
			jQuery.ajax({
            		type: 'POST',
            		url: ajaxurl,
            		data: data,
            		
            		success: function(response) {  
        				//alert('Got this from the server: ' + response);
            		}
            });
			jQuery(this).parents("div.bpsbuttoncontainer").children("div.bpsubbutton").removeClass('unsubscribed').text('Success').toggleClass('success').delay(3000).queue(function() {
   				jQuery(this).text('Subscribed').toggleClass('success').addClass('subscribed').attr('data-sub-status', '').dequeue();
			});
		});
});

