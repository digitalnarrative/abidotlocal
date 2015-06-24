$(document).ready(function(){

// Init a few variables we'll be using
var href;
var finalURL;
var frameHeight;
var frameWidth;
var parents;
var found;
var containsBase;
var mask;
var modal;

// Main function
$( "a" ).click(function(event) {

	// Check if the 'a' tag is in a has a parent div classed 'post'
	var parents = $(this).parents()
  		.map(function() {
   		return $(this).attr('class');
  	}) 
  	
  	// Decalre a variable is the 'a' is in a div classed 'post'
	var found = $.inArray('post', parents) > -1;
	
	
	// Intercept all clicks on 'a' tags
  	event.preventDefault();
  	
  	// Grab href
	var href = $(this).attr('href'); 	
	
	// Check if we should open a modal or continue the user to final location
	
		// First check to see if the URL contains the base of http://userpress.org/wiki/
	    if (href.indexOf('userpress.org/wiki/') !== -1 ){
	        var containsBase = true;       
	    }
		
		// Decide if the 'a' tag is in a div classed post AND contains the base URL.  If both are true, execute createModal function, if not, redirect user to end URL
		if (found == true && containsBase == true) {
			createModal(href);
			} else {
			window.open(href, '_self');
			}
		
});

// Open the modal window
function createModal(href){

	// Construct new href with ?view=modal	
	var finalHref = href + '?view=modal';
	
	// Load the modal
	$('body').append('<div style="display: block; opacity: 1; visibility: visible; top: 0px;" id="wikiModal"  class="currentModal reveal-modal currentModal medium open" data-reveal=""><div style="text-align:right; width: 100%;"><span class="closeModal">X</span></div><iframe src="' + finalHref + '"  width="100%" height="480" frameborder="0"></iframe></div>');



	
	$('.closeModal').css('cursor','pointer');

}
	
// Delete modal on close click
$(document).on( "click", ".closeModal", function() {
	$('.currentModal').remove();
});


});
