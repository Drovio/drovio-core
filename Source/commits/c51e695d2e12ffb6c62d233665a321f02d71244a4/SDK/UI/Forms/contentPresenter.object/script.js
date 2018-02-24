/* 
 * Redback JS Document
 *
 * Title: RedBack JS Library - Form Validator
 * Description: Checks for potential input errors on forms
 * Author: RedBack Developing Team 
 * Version: --
 * DateCreated: 06/02/2012
 * DateRevised: --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

var editor = false;

jq(document).one("ready.extra",function(){
	
	// Event Listener On Edit Button (if any)
	jq(document).on("click", '.contentPresenter > .control[role=edit]', function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();
		
		// Get Owner Content Presenter
		var jq_this_cntp = jq(this).closest('.contentPresenter');
		// Get Next Height
		_height = jq_this_cntp.children('.editor').outerHeight(true) + jq_this_cntp.children('.title').outerHeight(true);
		// Animate Container
		jq_this_cntp.animate({
			height: _height
		}, 'fast', function(){
			jq_this_cntp.children('.presenter').fadeOut('fast').css("position", "absolute");
			jq_this_cntp.children('.editor').fadeIn('fast', function(){
				jq(this).css("position", "inherit");
				jq_this_cntp.removeAttr('style');
			});
		});
		
		jq(this).css('display','none');
		jq_this_cntp.children('.control[role=cancel]').css('display','block');
	});
	// Event Listener On Cancel Button (if any)
	jq(document).on("click", '.contentPresenter > .control[role=cancel]', function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();
		
		// Get Owner Content Presenter
		var jq_this_cntp = jq(this).closest('.contentPresenter');
		// Get Next Height
		_height = jq_this_cntp.children('.presenter').outerHeight(true) + jq_this_cntp.children('.title').outerHeight(true);
		// Animate Container
		jq_this_cntp.animate({
			height: _height
		}, 'fast', function(){
			jq_this_cntp.children('.editor').fadeOut('fast');
			jq_this_cntp.children('.presenter').css("position", "absolute").fadeIn('fast', function(){
				jq(this).css("position", "inherit");
				jq_this_cntp.removeAttr('style');
				
				reset_form(jq_this_cntp);
			});
		});
		
		jq(this).css('display','none');
		jq_this_cntp.children('.control[role=edit]').css('display','block');
	});
	
	function reset_form(jq_this_cntp) {
		// Clear Report
		jq_this_cntp.children('.editor .form_report').contents().remove();

		// Reset Input Values
		jq_this_cntp.find('form').each(function() {
			this.reset();
		});
	}
});

