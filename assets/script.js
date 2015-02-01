/**
 * Get Tokens
 * function for extracting tokens
 */
function get_tokens( str ) {
	var results = [],
			re = /{{([^}]+)}}/g,
			text;

	while( text = re.exec( str ) ) {
		results.push( text[1] );
	}

	return results;
}

/**
 * Set cookie
 */
function set_cookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : ";     expires="+exdate.toUTCString());

	document.cookie=c_name + "=" + c_value;
}

/**
 * Plugin JS
 */
jQuery( document ).ready(function( $ ) {
	var overlay_html = wp.overlay_html;
	var overlay_html_tokens = get_tokens( wp.overlay_html );

	for( i = 0; i < overlay_html_tokens.length; i++ ) {
		overlay_html = overlay_html.replace( '{{' + overlay_html_tokens[i] + '}}', wp.post[overlay_html_tokens[i]] );
	}

	/* dont show the overlay if form_complete is found */
	if( overlay_html.search( 'form_complete' ) == -1 ) {
		set_cookie( 'hs_view_premium_content', '1', 10000 );
	} else {
		$("body").prepend( overlay_html );
		
		var form_html = $( ".hbspt-form" ).clone();
		$( ".hubspot-form-holder" ).hide().append( form_html ).fadeIn('fast');
	}
});