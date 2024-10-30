<?php

if ( ! class_exists( 'Conformis_Settings' ) ) {

	final class Conformis_Settings {
		protected static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		protected function __construct() {
			add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_header_scripts' ), 10, 1 );
			add_action( 'admin_menu', array( $this, 'settings_page_init' ), 1 );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
		}

		function add_header_scripts( $hook_suffix ) {
			if ( "settings_page_conformis" === $hook_suffix ) {
				wp_register_script( 'conformis_admin_script', plugins_url( '../js/admin.js', __FILE__ ), array( 'wp-editor' ), '0.1.0' );
				wp_enqueue_script( 'conformis_admin_script' );
				wp_enqueue_editor();
			}
		}

		function settings_page_init() {
			add_options_page(
				'Conformis',
				__( 'Conformis Cookie Banner', 'conformis' ),
				'manage_options',
				'conformis',
				array( $this, 'settings_page_html' )
			);
		}

		function settings_page_html() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			settings_errors( 'conformis_messages' );
			?>
            <div class="wrap">
                <h1><?php esc_html_e( get_admin_page_title() ); ?></h1>
                <form action="options.php" method="post">
					<?php
					settings_fields( 'conformis' );
					do_settings_sections( 'conformis' );
					submit_button( __( 'Save Settings', 'conformis' ) );
					?>
                </form>
            </div>
			<?php
		}

		function settings_init() {
			register_setting( 'conformis', 'conformis_options' );

			add_settings_section(
				'conformis_general',
				'',
				array( $this, 'render_section' ),
				'conformis'
			);

			add_settings_field(
				'conformis_banner_position',
				__( 'Banner Position', 'conformis' ),
				array( $this, 'dropdown_cb' ),
				'conformis',
				'conformis_general',
				[
					'label_for' => 'conformis_banner_position',
					'class'     => 'conformis_row',
					'options'   => array(
						array( 'key' => 'top', 'value' => __( 'Top', 'conformis' ) ),
						array( 'key' => 'center', 'value' => __( 'Center', 'conformis' ) ),
						array( 'key' => 'bottom', 'value' => __( 'Bottom', 'conformis' ), 'default' => true ),
					)
				]
			);

			add_settings_field(
				'conformis_message',
				__( 'Banner Message', 'conformis' ),
				array( $this, 'richtext_cb' ),
				'conformis',
				'conformis_general',
				[
					'label_for' => 'conformis_message',
					'class'     => 'conformis_row',
				]
			);

			add_settings_field(
				'conformis_button-text',
				__( 'Confirm Button Text', 'conformis' ),
				array( $this, 'textfield_cb' ),
				'conformis',
				'conformis_general',
				[
					'label_for' => 'conformis_button-text',
					'class'     => 'conformis_row',
				]
			);
		}

		function render_section( $args ) {
		}

		function dropdown_cb( $args ) {
			function is_selected( $option, $key, $default = false ) {
				$options = get_option( 'conformis_options' );

				if ( isset( $options[ $option ] ) ) {
					return $key === $options[ $option ];
				}

				return $default;
			}

			?>
            <select id="<?php esc_attr_e( $args['label_for'] ); ?>"
                    name="conformis_options[<?php esc_attr_e( $args['label_for'] ); ?>]">
				<?php foreach ( $args['options'] as $option ) { ?>
                    <option
                            value="<?php esc_attr_e( $option['key'] ); ?>"
						<?php selected( is_selected( $args['label_for'], $option['key'], $option['default'] ) ) ?>
                    ><?php esc_html_e( $option['value'] ); ?></option>
				<?php } ?>
            </select>
			<?php
		}

		function textfield_cb( $args ) {
			$options = get_option( 'conformis_options' );
			?>
            <input type="hidden" name="conformis_options[<?php esc_attr_e( $args['label_for'] ); ?>]"
                   value="<?php echo isset( $options[ $args['label_for'] ] ) ? esc_html( $options[ $args['label_for'] ] ) : ( '' ); ?>"/>
            <input type="text" id="<?php esc_attr_e( $args['label_for'] ); ?>"/>
			<?php
		}

		function richtext_cb( $args ) {
			$options = get_option( 'conformis_options' ); ?>
            <input type="hidden" name="conformis_options[<?php esc_attr_e( $args['label_for'] ); ?>]"
                   value="<?php echo isset( $options[ $args['label_for'] ] ) ? esc_html( $options[ $args['label_for'] ] ) : ( '' ); ?>"/>
			<?php
			global $is_IE;
			$toolbar_btns = array(
				'forecolor',
				'bold',
				'italic',
				'underline',
				'blockquote',
				'strikethrough',
				'bullist',
				'numlist',
				'alignleft',
				'aligncenter',
				'alignright',
				'undo',
				'redo',
				'link'
			);
			wp_editor(
				$options[ $args['label_for'] ],
				$args['label_for'],
				array(
					'_content_editor_dfw' => false,
					'drag_drop_upload'    => true,
					'tabfocus_elements'   => 'content-html,save-post',
					'editor_height'       => 300,
					'tinymce'             => array(
						'resize'                  => true,
						'wp_autoresize_on'        => false,
						'add_unload_trigger'      => false,
						'wp_keep_scroll_position' => ! $is_IE,
						'toolbar1'                => implode( ",", $toolbar_btns ),
					),
				)
			);
		}

		function add_settings_link( $plugin_actions, $plugin_file ) {
			global $CONFORMIS_PLUGIN_BASENAME;

			if ( $CONFORMIS_PLUGIN_BASENAME === $plugin_file ) {
				$settings_link = sprintf(
					'<a href="%s">%s</a>',
					menu_page_url( 'conformis', false ),
					esc_html( __( 'Settings', 'conformis' ) )
				);
				array_unshift( $plugin_actions, $settings_link );
			}

			return $plugin_actions;
		}
	}
}