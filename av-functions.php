<?php

// Don't access this directly, please
if ( ! defined( 'ABSPATH' ) ) exit;


/***********************************************************/
/******************** General Functions ********************/
/***********************************************************/

/**
 * Echoes the minimum age.
 *
 * @since 0.1
 * @echo int
 */
function av_minimum_age() {
	
	echo av_get_minimum_age();
}

/**
 * Returns the minimum age. You can filter this if you like.
 *
 * @since 0.1
 * @return int
 */
function av_get_minimum_age() {
	global $age_verify;
	
	return (int) apply_filters( 'av_minimum_age', $age_verify->minimum_age );
}

/**
 * Returns the visitor's age. Adds compatibility for PHP 5.2
 *
 * @since 0.1.5
 * @return int
 */
function av_get_visitor_age( $year, $month, $day ) {
	global $age_verify;
	
	$age = 0;
	
	$birthday = new DateTime( $year . '-' . $month . '-' . $day );
	
	$phpversion = phpversion();
	
	if ( $phpversion >= '5.3' ) :
		
		$current  = new DateTime( current_time( 'mysql' ) );
		$age      = $birthday->diff( $current );
		$age      = $age->format( '%y' );
		
	else :
		
		list( $year, $month, $day ) = explode( '-', $birthday->format( 'Y-m-d' ) );
		
	    $year_diff  = date_i18n( 'Y' ) - $year;
	    $month_diff = date_i18n( 'm' ) - $month;
	    $day_diff   = date_i18n( 'd' ) - $day;
	    
	    if ( $month_diff < 0 )
	    	$year_diff--;
	    elseif ( ( $month_diff == 0 ) && ( $day_diff < 0 ) )
	    	$year_diff--;
	    
	    $age = $year_diff;
	    
    endif;
	
	return (int) $age;
}

/**
 * Returns cookie duration. This lets us know how long to keep a
 * visitor's verified cookie. You can filter this if you like.
 *
 * @since 0.1
 * @return int
 */
function av_get_cookie_duration() {
	global $age_verify;
	
	return (int) apply_filters( 'av_cookie_duration', $age_verify->cookie_duration );
}

/**
 * This is the very important function that determines if a given visitor
 * needs to be verified before viewing the site. You can filter this if you like.
 *
 * @since 0.1
 * @return bool
 */
function av_needs_verification() {
	
	// Assume the visitor needs to be verified
	$return = true;
	
	if ( isset( $_GET['verified'] ) && $_GET['verified'] == 'yes' )
		$return = false;
	
	// If logged in users are exempt, and the visitor is logged in, let 'em through
	if ( get_option( '_av_always_verify', 'guests' ) == 'guests' && is_user_logged_in() )
		$return = false;
	
	// Or, if there is a valid cookie let 'em through
	if ( isset( $_COOKIE['av_old_enough'] ) )
		$return = false;
	
	return (bool) apply_filters( 'av_needs_verification', $return );
}


/***********************************************************/
/******************** Display Functions ********************/
/***********************************************************/

/**
 * Echoes the overlay heading
 *
 * @since 0.1
 * @echo string
 */
function av_the_heading() {
	
	echo av_get_the_heading();
}

/**
 * Returns the overlay heading. You can filter this if you like.
 *
 * @since 0.1
 * @return string
 */
function av_get_the_heading() {
	
	return sprintf( apply_filters( 'av_heading', get_option( '_av_heading', __( 'You must be %s years old to visit this site.', 'age_verify' ) ) ), av_get_minimum_age() );
}

/**
 * Echoes the overlay description, which lives below the heading and above the form.
 *
 * @since 0.1
 * @echo string
 */
function av_the_desc() {
	
	echo av_get_the_desc();
}

/**
 * Returns the overlay description, which lives below the heading and above the form.
 * You can filter this if you like.
 *
 * @since 0.1
 * @return string|false
 */
function av_get_the_desc() {
	
	$desc = apply_filters( 'av_description', get_option( '_av_description', __( 'Please verify your age', 'age_verify' ) ) );
	
	if ( ! empty( $desc ) )
		return $desc;
	else
		return false;
}

/**
 * Returns the form's input type, based on the settings.
 * You can filter this if you like.
 *
 * @since 0.1
 * @return string
 */
function av_get_input_type() {
	
	return apply_filters( 'av_input_type', get_option( '_av_input_type', 'dropdowns' ) );
}

/**
 * Returns the overlay box's background color
 * You can filter this if you like.
 *
 * @since 0.1
 * @return string
 */
function av_get_overlay_color() {
	
	if ( get_option( '_av_overlay_color' ) )
		$color = get_option( '_av_overlay_color' );
	else
		$color = 'fff';
	
	return apply_filters( 'av_overlay_color', $color );
}

/**
 * Returns the overlay's background color
 * You can filter this if you like.
 *
 * @since 0.1
 * @return string
 */
function av_get_background_color() {
	
	if ( current_theme_supports( 'custom-background' ) )
		$default = get_background_color();
	else
		$default = 'e6e6e6';
	
	if ( get_option( '_av_bgcolor' ) )
		$color = get_option( '_av_bgcolor' );
	else
		$color = $default;
	
	return apply_filters( 'av_background_color', $color );
}

/**
 * Echoes the actual form
 *
 * @since 0.1
 * @echo string
 */
function av_verify_form() {
	
	echo av_get_verify_form();
}

/**
 * Returns the all-important verification form.
 * You can filter this if you like.
 *
 * @since 0.1
 * @return string
 */
function av_get_verify_form() {
	
	$input_type = av_get_input_type();
	
	$submit_button_label = apply_filters( 'av_form_submit_label', __( 'Enter Site &raquo;', 'age_verify' ) );
	
	$form = '';
	
	$form .= '<form id="av_verify_form" action="' . home_url( '/' ) . '" method="post">';
	
	if ( isset( $_GET['verified'] ) && $_GET['verified'] == 'no' )
		$form .= sprintf( '<p class="error">' . apply_filters( 'av_error_text', __( 'Sorry, it doesn\'t look like you\'re old enough', 'age_verify' ) ) . '</p>', av_get_minimum_age() );
	
	do_action( 'av_form_before_inputs' );
	
	switch ( $input_type ) {
		
		// If set to date dropdowns
		case 'dropdowns' :
			
			$form .= '<p><select name="av_verify_m" id="av_verify_m">';
				
				foreach ( range( 1, 12 ) as $month ) :
					
					$month_name = date( 'F', mktime( 0, 0, 0, $month, 1 ) );
					
					$form .= '<option value="' . $month . '">' . $month_name . '</option>';
					
				endforeach;
				
			$form .= '</select> - <select name="av_verify_d" id="av_verify_d">';
				
				foreach ( range( 1, 31 ) as $day ) :
					
					$form .= '<option value="' . $day . '">' . zeroise( $day, 2 ) . '</option>';
					
				endforeach;
				
			$form .= '</select> - <select name="av_verify_y" id="av_verify_y">';
				
				foreach ( range( 1910, date( 'Y' ) ) as $year ) :
					
					$selected = ( $year == date( 'Y' ) ) ? 'selected="selected"' : '';
					
					$form .= '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
					
				endforeach;
				
			$form .= '</select></p>';
			
			$form .= '<p class="submit"><label for="av_verify_remember"><input type="checkbox" name="av_verify_remember" id="av_verify_remember" value="1" /> Remember me</label> ';
			
			break;
		
		// If set to date inputs
		case 'inputs' :
			
			$form .= '<p><input type="text" name="av_verify_m" id="av_verify_m" maxlength="2" value="" placeholder="MM" /> - <input type="text" name="av_verify_d" id="av_verify_d" maxlength="2" value="" placeholder="DD" /> - <input type="text" name="av_verify_y" id="av_verify_y" maxlength="4" value="" placeholder="YYYY" /></p>';
			
			$form .= '<p class="submit"><label for="av_verify_remember"><input type="checkbox" name="av_verify_remember" id="av_verify_remember" value="1" /> Remember me</label> ';
			
			break;
			
		// If just a simple checkbox
		case 'checkbox' :
			
			$form .= '<p class="submit"><label for="av_verify_confirm"><input type="checkbox" name="av_verify_confirm" id="av_verify_confirm" value="1" /> ';
			
			$form .= sprintf( apply_filters( 'av_confirm_text', __( 'I am at least %s years old', 'age_verify' ) ), av_get_minimum_age() ) . '</label> ';
			
			break;
			
	};
	
	do_action( 'av_form_after_inputs' );
	
	$form .= '<input type="submit" name="av_verify" id="av_verify" value="' . $submit_button_label . '" /></p>';
	
	$form .= '</form>';
	
	return apply_filters( 'av_verify_form', $form );
}


/***********************************************************/
/*************** User Registration Functions ***************/
/***********************************************************/

/**
 * Determines whether or not users need to verify their age before
 * registering for the site. You can filter this if you like.
 *
 * @since 0.1
 * @return bool
 */
function av_confirmation_required() {
	
	if ( get_option( '_av_membership', 1 ) == 1 )
		$return = true;
	else
		$return = false;
		
	return (bool) apply_filters( 'av_confirmation_required', $return );
}

/**
 * Adds a checkbox to the default WordPress registration form for
 * users to verify their ages. You can filter the text if you like.
 *
 * @since 0.1
 * @echo string
 */
function av_register_form() {
	
	$text = '<p class="age-verify"><label for="_av_confirm_age"><input type="checkbox" name="_av_confirm_age" id="_av_confirm_age" value="1" /> ';
	
	$text .= sprintf( apply_filters( 'av_registration_text', __( 'I am at least %s years old', 'age_verify' ) ), av_get_minimum_age() );
	
	$text .= '</label></p><br />';
	
	echo $text;
}

/**
 * Make sure the user checked the box when registering.
 * If not, print an error. You can filter the error's text if you like.
 *
 * @since 0.1
 * @return bool
 */
function av_register_check( $login, $email, $errors ) {
	
	if ( ! isset( $_POST['_av_confirm_age'] ) )
		$errors->add( 'empty_age_confirm', '<strong>ERROR</strong>: ' . apply_filters( 'av_registration_error', __( 'Please confirm your age', 'age_verify' ) ) );
}