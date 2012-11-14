<?php

// Don't access this directly, please
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define the settings page.
 *
 * @since 0.1
 */
function av_settings_page() { ?>

	<div class="wrap">

		<?php screen_icon(); ?>

		<h2><?php _e( 'Age Verify Settings', 'age_verify' ) ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'age-verify' ); ?>

			<?php do_settings_sections( 'age-verify' ); ?>

			<?php submit_button(); ?>
			
		</form>
	</div>

<?php }


/**********************************************************/
/******************** General Settings ********************/
/**********************************************************/

/**
 * Print the general settings section heading.
 *
 * @since 0.1
 * @access public
 */
function av_settings_callback_section_general() {
	
	// Something should go here
}

function av_settings_callback_minimum_age_field() { ?>
	
	<input name="_av_minimum_age" type="number" id="_av_minimum_age" step="1" min="10" class="small-text" value="<?php echo get_option( '_av_minimum_age', '21' ); ?>" /> <?php _e( 'years old or older to view this site', 'age_verify' ); ?>
	
<?php }

function av_settings_callback_always_verify_field() { ?>
	
	<fieldset>
		<legend class="screen-reader-text">
			<span><?php _e( 'Verify the age of', 'age_verify' ); ?></span>
		</legend>
		<label>
			<input type="radio" name="_av_always_verify" value="guests" <?php checked( 'guests', get_option( '_av_always_verify', 'guests' ) ); ?>/>
			 <?php _e( 'Guests only', 'age_verify' ); ?> <span class="description"><?php _e( 'Logged-in users will not need to verify their age.', 'age_verify' ); ?></span><br />
		</label>
		<label>
			<input type="radio" name="_av_always_verify" value="all" <?php checked( 'all', get_option( '_av_always_verify', 'guests' ) ); ?>/>
			 <?php _e( 'All visitors', 'age_verify' ); ?>
		</label>
	</fieldset>
	
<?php }

function av_settings_callback_cookie_duration_field() { ?>
	
	<input name="_av_cookie_duration" type="number" id="_av_cookie_duration" step="15" min="15" class="small-text" value="<?php echo get_option( '_av_cookie_duration', '720' ); ?>" /> <?php _e( 'minutes', 'age_verify' ); ?>
	
<?php }

function av_settings_callback_membership_field() { ?>
	
	<fieldset>
		<legend class="screen-reader-text">
			<span><?php _e( 'Membership', 'age_verify' ); ?></span>
		</legend>
		<label for="_av_membership">
			<input name="_av_membership" type="checkbox" id="_av_membership" value="1" <?php checked( 1, get_option( '_av_membership', 1 ) ); ?>/>
			 <?php _e( 'Require users to confirm their age before registering to this site', 'age_verify' ); ?>
		</label>
	</fieldset>
	
<?php }


/**********************************************************/
/******************** Display Settings ********************/
/**********************************************************/

/**
 * Print the display settings section heading.
 *
 * @since 0.1
 * @echo string
 */
function av_settings_callback_section_display() {
	
	echo '<p>' . __( 'These settings change the look of your overlay. You can use <code>%s</code> to display the minimum age number from the setting above.', 'age_verify' ) . '</p>';
}

function av_settings_callback_heading_field() { ?>
	
	<input name="_av_heading" type="text" id="_av_heading" value="<?php echo get_option( '_av_heading', __( 'You must be %s years old to visit this site.', 'age_verify' ) )?>" class="regular-text" />
	
<?php }

function av_settings_callback_description_field() { ?>
	
	<input name="_av_description" type="text" id="_av_description" value="<?php echo get_option( '_av_description', __( 'Please verify your age', 'age_verify' ) )?>" class="regular-text" />
	
<?php }

function av_settings_callback_input_type_field() { ?>
	
	<select name="_av_input_type" id="_av_input_type">
		<option value="dropdowns" <?php selected( 'dropdowns', get_option( '_av_input_type', 'dropdowns' ) ); ?>><?php _e( 'Date dropdowns', 'age_verify' ); ?></option>
		<option value="inputs" <?php selected( 'inputs', get_option( '_av_input_type', 'dropdowns' ) ); ?>><?php _e( 'Inputs', 'age_verify' ); ?></option>
		<option value="checkbox" <?php selected( 'checkbox', get_option( '_av_input_type', 'dropdowns' ) ); ?>><?php _e( 'Confirm checkbox', 'age_verify' ); ?></option>
	</select>
	
<?php }

function av_settings_callback_styling_field() { ?>
	
	<fieldset>
		<legend class="screen-reader-text">
			<span><?php _e( 'Styling', 'age_verify' ); ?></span>
		</legend>
		<label for="_av_styling">
			<input name="_av_styling" type="checkbox" id="_av_styling" value="1" <?php checked( 1, get_option( '_av_styling', 1 ) ); ?>/>
			 <?php _e( 'Use built-in CSS on the front-end (recommended)', 'age_verify' ); ?>
		</label>
	</fieldset>
	
<?php }

function av_settings_callback_overlay_color_field() { ?>
	
	<fieldset>
		
		<legend class="screen-reader-text">
			<span><?php _e( 'Overlay Color', 'age_verify' ); ?></span>
		</legend>
		
		<?php $default_color = ' data-default-color="#fff"'; ?>
			
		<input type="text" name="_av_overlay_color" id="_av_overlay_color" value="#<?php echo esc_attr( av_get_overlay_color() ); ?>"<?php echo $default_color ?> />
		
	</fieldset>
	
<?php }

function av_settings_callback_bgcolor_field() { ?>
	
	<fieldset>
		
		<legend class="screen-reader-text">
			<span><?php _e( 'Background Color' ); ?></span>
		</legend>
		
		<?php $default_color = '';
		
		if ( current_theme_supports( 'custom-background', 'default-color' ) )
			$default_color = ' data-default-color="#' . esc_attr( get_theme_support( 'custom-background', 'default-color' ) ) . '"'; ?>
			
		<input type="text" name="_av_bgcolor" id="_av_bgcolor" value="#<?php echo esc_attr( av_get_background_color() ); ?>"<?php echo $default_color ?> />
		
	</fieldset>
	
<?php }
