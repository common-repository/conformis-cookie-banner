<?php
/**
 * @package Conformis
 *
 * @wordpress-plugin
 * Plugin Name: Conformis Cookie Banner
 * Description: A simple customisable GDPR Banner
 * Version: 0.1.0
 * Author: Solice GmbH
 * Author URI: https://www.conformis.io/
 * License: GPLv2 or later
 * Text Domain: conformis
 * Domain Path: /languages
 */
define( 'CONFORMIS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( ! class_exists( 'Conformis' ) ) {

	final class Conformis {
		protected static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		protected function __construct() {
			$this->init();
		}

		function init() {
			load_plugin_textdomain( 'conformis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			add_action( 'wp_head', array( $this, 'add_scripts' ), 0 );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );
			add_action( 'wp_footer', array( $this, 'show_banner' ), 5 );
		}

		function add_styles() {
			wp_register_style( 'conformis_style', plugins_url( 'css/main.css', __FILE__ ), false, '0.1.0' );
			wp_enqueue_style( 'conformis_style' );
		}

		function add_scripts() {
			printf(
				'<script src="%s?ver=0.1.0"></script>',
				plugins_url( 'js/main.js', __FILE__ )
			);
		}

		function show_banner() {
			$options = get_option( 'conformis_options' );
			printf(
				'<div class="conformis-plugin__wrapper position-%s closed"><div class="conformis-plugin__content"><div class="conformis-plugin__message">%s</div><button class="conformis-plugin__btn--confirm">%s</button></div></div>',
				$options['conformis_banner_position'],
				html_entity_decode( $options['conformis_message'], ENT_COMPAT | ENT_HTML5 ),
				$options['conformis_button-text']
			);
		}
	}

}

add_action( 'init', array( 'Conformis', 'instance' ), 0 );
global $CONFORMIS_PLUGIN_BASENAME;
$CONFORMIS_PLUGIN_BASENAME = plugin_basename( __FILE__ );

if ( is_admin() ) {
	require_once( CONFORMIS__PLUGIN_DIR . 'views/settings.php' );
	add_action( 'init', array( 'Conformis_Settings', 'instance' ) );
}