<?php

/**
 * Plugin Name: Age Verify
 * Description: A simple way to ask visitors for their age before viewing your site.
 * Author:      Chase Wiseman
 * Author URI:  http://chasewiseman.com
 * Version:     0.1.5
 * Text Domain: age_verify
 * Domain Path: /av-languages/
 *
 * @package   AgeVerify
 * @version   0.1.5
 * @author    Chase Wiseman <contact@chasewiseman.com>
 * @copyright Copyright (c) 2012, Chase Wiseman
 * @link      http://chasewiseman.com/plugins/age-verify/
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Don't access this directly, please
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The main Age Verify class.
 *
 * @since 0.1
 */
class Age_Verify {
	
	/**
	 * Sets up everything.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function __construct() {
		
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}
	
	/**
	 * Sets up the globals.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function setup_globals() {
		
		// Directories and URLs
		$this->file            = __FILE__;
		$this->basename        = plugin_basename( $this->file );
		$this->plugin_dir      = plugin_dir_path( $this->file );
		$this->plugin_url      = plugin_dir_url ( $this->file );
		$this->lang_dir        = trailingslashit( $this->plugin_dir . 'av-languages' );
		
		$this->admin_url      = $this->plugin_url . 'av-admin';
		$this->admin_dir      = $this->plugin_dir . 'av-admin';
		
		// Min age and cookie duration
		$this->minimum_age     = get_option( '_av_minimum_age',     '21' );
		$this->cookie_duration = get_option( '_av_cookie_duration', '720' );
	}
	
	/**
	 * Require the necessary files
	 *
	 * @since 0.1
	 * @access private
	 */
	private function includes() {
		
		// This file defines all of the common functions
		require( $this->plugin_dir . 'av-functions.php' );
		
		// If in the admin, this file sets up the admin functions
		if ( is_admin() ) :
			
			require( $this->admin_dir . '/admin.php' );
			
			require( $this->admin_dir . '/admin-settings.php' );
			
		endif;
	}
	
	/**
	 * Sets up the actions and filters.
	 *
	 * @since 0.1
	 * @access private
	 */
	private function setup_actions() {
		
		// Load the text domain for i18n
		add_action( 'init', array( $this, 'load_textdomain' ) );
		
		// If checked in the settings, load the default and custom styles
		if ( get_option( '_av_styling', 1 ) == 1 ) :
			
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			
			add_action( 'wp_head', array( $this, 'custom_styles' ) );
			
		endif;
		
		// Maybe display the overlay
		add_action( 'wp_footer', array( $this, 'verify_overlay' ) );
		
		// Verify the visitor's input
		add_action( 'template_redirect', array( $this, 'verify' ) );
		
		// If checked in the settings, add to the registration form
		if ( av_confirmation_required() ) :
			
			add_action( 'register_form', 'av_register_form' );
			
			add_action( 'register_post', 'av_register_check', 10, 3 );
			
		endif;
	}
	
	/**
	 * Load the text domain.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function load_textdomain() {
		
		load_plugin_textdomain( 'age_verify', false, $this->lang_dir );
	}
	
	/**
	 * Enqueue the styles.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function enqueue_styles() {
		
		wp_enqueue_style( 'av-styles', $this->plugin_url . '/av-assets/av-styles.css' );
	}
	
	/**
	 * Print the custom colors, as defined in the admin.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function custom_styles() { ?>
		
		<style type="text/css">
			
			#av-overlay-wrap { 
				background: #<?php echo esc_attr( av_get_background_color() ); ?>;
			}
			
			#av-overlay {
				background: #<?php echo esc_attr( av_get_overlay_color() ); ?>;
			}
			
		</style>
		
		<?php do_action( 'av_custom_styles' );
	}
	
	/**
	 * Print the actual overlay if the visitor needs verification.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function verify_overlay() {
		
		if ( ! av_needs_verification() )
			return; ?>
		
		<div id="av-overlay-wrap">
			
			<div id="av-overlay">
				
				<h1><?php av_the_heading(); ?></h1>
				
				<?php if ( av_get_the_desc() )
					echo '<p>' . av_get_the_desc() . '</p>'; ?>
				
				<?php do_action( 'av_before_form' ); ?>
				
				<?php av_verify_form(); ?>
					
				<?php do_action( 'av_after_form' ); ?>
				
				<?php if ( floatval( phpversion() ) ); ?>
				
			</div>
			
		</div>
	<?php }
	
	/**
	 * Verify the visitor if the form was submitted.
	 * There are various filters and actions to change this method's behavior.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function verify() {
		
		if ( ! isset( $_POST['av_verify'] ) )
			return;
		
		$redirect_url = wp_get_referer();
		
		$is_verified  = false;
		
		$input_type   = av_get_input_type();
		
		switch ( $input_type ) {
			
			
			case 'checkbox' :
				
				if ( (int) $_POST['av_verify_confirm'] == 1 )
					$is_verified = true;
				
				break;
			
			default :
				
				if ( checkdate( (int) $_POST['av_verify_m'], (int) $_POST['av_verify_d'], (int) $_POST['av_verify_y'] ) ) :
					
					$age = av_get_visitor_age( $_POST['av_verify_y'], $_POST['av_verify_m'], $_POST['av_verify_d'] );
					
				    if ( $age >= av_get_minimum_age() )
						$is_verified = true;
						
				endif;
				
				break;
		}
		
		$is_verified = apply_filters( 'av_passed_verify', $is_verified );
		
		if ( $is_verified == true ) :
			
			do_action( 'av_was_verified' );
			
			if ( isset( $_POST['av_verify_remember'] ) )
				$cookie_duration = av_get_cookie_duration() * 60;
			else
				$cookie_duration = 7200;
			
			setcookie( 'av_old_enough', 1, time() + $cookie_duration, COOKIEPATH, COOKIE_DOMAIN, false );
			
			wp_redirect( add_query_arg( 'verified', 'yes', $redirect_url ) );
			exit;
			
		else :
			
			do_action( 'av_was_not_verified' );
			
			wp_redirect( add_query_arg( 'verified', 'no', $redirect_url ) );
			exit;
			
		endif;
	}
}

$age_verify = new Age_Verify();