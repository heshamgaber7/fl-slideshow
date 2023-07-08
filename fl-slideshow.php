<?php
/**
 * Plugin Name: FlatLayers Basic Slideshow
 * Plugin URI: http://flatlayers.com
 * Description: A plugin to create multiple images slideshow
 * Author: Hesham Gaber
 * Author URI: http://flatlayers.com
 * Text Domain: fl-slideshow
 * Domain Path: /languages/
 * Version: 1.0
 *
 * @package FlatLayers Basic Slideshow
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include Slideshow Class.
require 'class-fl-slideshow.php';

// Instantiate Slideshow class.
$fl_slideshow = new FL_Slideshow();
