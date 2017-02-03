<?php
/*
 * Plugin Name: Visual Composer Lightbox Enhancement
 * Version: 1.0
 * Plugin URI: https://github.com/etalented/visual-composer-lightbox
 * Description: An enhancement for Visual Composer to replace prettyPhoto lightbox support to support for any lightbox.
 * Author: Etalented
 * Author URI: http://etalented.co.uk
 * Requires at least: 4.7
 * Tested up to: 4.7
 *
 * Text Domain: visual-composer-lightbox
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Etalented
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-visual-composer-lightbox.php' );
require_once( 'includes/class-visual-composer-lightbox-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-visual-composer-lightbox-admin-api.php' );

/**
 * Returns the main instance of Visual_Composer_Lightbox to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Visual_Composer_Lightbox
 */
function Visual_Composer_Lightbox () {
	$instance = Visual_Composer_Lightbox::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Visual_Composer_Lightbox_Settings::instance( $instance );
	}

	return $instance;
}

Visual_Composer_Lightbox();