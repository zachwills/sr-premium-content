<?php
/**
 * Plugin Name: SR Premium Content
 * Plugin URI: #
 * Description: Custom plugin to hide premium content behind if a user doesn't have 
 * Version: 0.1
 * Author: Zach Wills
 * Author URI: http://zachwills.net
 * License: GPL2
 */

/* WordPress recommended security precaution */
defined('ABSPATH') or die('No script kiddies please!');

require 'lib/class-sr-premium-content.php';
$hubspot_personalize = new SR_Premium_Content;
