<?php
/*
Plugin Name: BuddyMedia
Plugin URI: https://github.com/WebDevStudios/WDS-BP-Media
Description: Media component for BuddyPress from WebDevStudios.
Version: 1.0.1
Tested up to: 4.7
Requires at least: 3.9
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: WebDevStudios
Author URI: https://webdevstudios.com
Text Domain: bp-media
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BP_Media' ) ) :

	/**
	 * Main BP_Media Class
	 */
	class BP_Media {

		/**
		 * The instance function.
		 *
		 * @access public
		 * @static
		 * @return object $instance
		 */
		public static function instance() {

			// Store the instance locally to avoid private static replication.
			static $instance = null;

			// Only run these methods if they haven't been run previously.
			if ( null === $instance ) {
				$instance = new BP_Media;
				$instance->constants();
				$instance->libs();
				$instance->includes();
				$instance->setup_actions();

				define( 'BP_MEDIA_DIR', dirname( __FILE__ ) );
			}

			// Always return the instance.
			return $instance;

			// The last transport is away! Rebel scum.
		}

		/**
		 * __construct function.
		 *
		 * @access private
		 */
		private function __construct() {
			/* Do nothing here */ }

		/**
		 * Magic method to prevent notices and errors from invalid method calls.
		 *
		 * @access public
		 */
		public function __call( $name = '', $args = array() ) {
			unset( $name, $args );
			return null; }

		/**
		 * The constants function.
		 *
		 * @access private
		 */
		private function constants() {

			// Path and URL.
			if ( ! defined( 'BP_MEDIA_PLUGIN_DIR' ) ) {
				define( 'BP_MEDIA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'BP_MEDIA_PLUGIN_URL' ) ) {
				define( 'BP_MEDIA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
		}

		/**
		 * The includes function.
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {

			require( dirname( __FILE__ ) . '/includes/class-bp-media-admin-settings.php' );
			require( dirname( __FILE__ ) . '/includes/bp-media-function.php' );
			require( dirname( __FILE__ ) . '/includes/class-bp-media-cpt.php' );
			require( dirname( __FILE__ ) . '/includes/bp-media-loader.php' );
			require( dirname( __FILE__ ) . '/includes/bp-media-ajax.php' );
			require( dirname( __FILE__ ) . '/includes/bp-media-activity.php' );
			require( dirname( __FILE__ ) . '/includes/bp-media-library-filter.php' );
			require( dirname( __FILE__ ) . '/includes/class-bp-media-groups.php' );
			require( dirname( __FILE__ ) . '/includes/class-bp-media-reports-list-table.php' );
			require( dirname( __FILE__ ) . '/includes/class-bp-media-reporting.php' );

			$this->admin = new BP_Media_Settings();
		}

		/**
		 * The libs function.
		 *
		 * @author  Kailan W.
		 *
		 * @since 1.0.2
		 *
		 * @access private
		 * @return void
		 */
		private function libs() {

			if ( file_exists( __DIR__ . '/vendor/webdevstudios/cmb2/init.php' ) ) {
				require_once  __DIR__ . '/vendor/webdevstudios/cmb2/init.php';
			}
		}

		/**
		 * The setup_actions function.
		 *
		 * @access private
		 */
		private function setup_actions() {
			add_action( 'plugins_loaded', array( $this, 'bp_media_bp_check' ), 999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * The enqueue_scripts function.
		 *
		 * @access public
		 */
		public function enqueue_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_script( 'plupload-all' );

			wp_register_script( 'bp-media-js', plugins_url( 'includes/assets/js/bp-media' . $suffix . '.js' , __FILE__ ), array(), filemtime( plugin_dir_path( __FILE__ ) . 'includes/assets/js/bp-media' . $suffix . '.js' ) );

			$reasons = bp_media_get_option( 'bp_media_reporting_reasons' );

			// Localize the script with new data.
			$translation_array = array(
				'bp_media_ajax_create_album_error'  => __( 'Error creating album', 'bp-media' ),
				'bp_media_ajax_delete_album_error'  => __( 'Error deleteing album', 'bp-media' ),
				'bp_media_ajax_edit_album_error'    => __( 'Error editing album', 'bp-media' ),
				'bp_media_ajax_create_album_error'  => __( 'Error creating album', 'bp-media' ),
				'bp_media_ajax_delete_album_error'  => __( 'Error deleteing album', 'bp-media' ),
				'bp_media_ajax_edit_album_error'    => __( 'Error editing album', 'bp-media' ),
				'bp_media_ajax_reporting_error'     => __( 'Error reporting this item', 'bp-media' ),
				'bp_media_reporting_reasons'        => json_encode( $reasons ),
				'bp_media_reporting_header'         => __( 'Help Us Understand What\'s Happening','bp-media' ),
				'bp_media_reporting_body'           => __( 'Why don\'t you want to see this?</div>','bp-media' ),
	 			'submit_text'                       => __( 'Report this media', 'bp-media' ),
				'cancel_text'                       => __( 'Cancel', 'bp-media' ),
				'report_success_message'            => __( 'Your report has been sent for consideration', 'bp-media' ),
			);
			wp_localize_script( 'bp-media-js', 'bp_media', $translation_array );

			wp_enqueue_script( 'bp-media-js' );

			wp_enqueue_style( 'bp-media-css', plugins_url( 'includes/assets/css/bp-media' . $suffix . '.css' , __FILE__ ), array(), filemtime( plugin_dir_path( __FILE__ ) . 'includes/assets/css/bp-media' . $suffix . '.css' ) );

		}

		/**
		 * The bp_media_bp_check function.
		 *
		 * @access public
		 */
		public function bp_media_bp_check() {
			if ( ! class_exists( 'BuddyPress' ) ) {
				add_action( 'admin_notices', array( $this, 'bp_media_install_buddypress_notice' ) );
			}
		}

		/**
		 * The bp_media_install_buddypress_notice function.
		 *
		 * @access public
		 */
		public function bp_media_install_buddypress_notice() {

			// compile default message
			$default_message = sprintf(
				__( '<strong>BP Media</strong> requires the BuddyPress plugin to work. Please <a href="http://buddypress.org/download">install BuddyPress</a> first, or <a href="%s">deactivate BP Media</a>.', 'buddmedia' ),
				admin_url( 'plugins.php' )
			);

			// default details to null
			$details = null;

			// add details if any exist
			if ( ! empty( $this->activation_errors ) && is_array( $this->activation_errors ) ) {
				$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
			}

			// output errors
			?>
			<div id="message" class="error fade">
				<p><?php echo $default_message; ?></p>
				<?php echo $details; ?>
			</div>
			<?php
		}

		/**
		 * Include a file from the includes directory
		 *
		 * @since  1.0.2
		 * @param  string $filename Name of the file to be included.
		 * @return bool   Result of include call.
		 */
		public static function include_file( $filename ) {
			$file = self::dir( $filename . '.php' );
			if ( file_exists( $file ) ) {
				return include_once( $file );
			}
			return false;
		}

		/**
		 * This plugin's directory.
		 *
		 * @since  1.0.2
		 * @param  string $path (optional) appended path.
		 * @return string       Directory and path
		 */
		public static function dir( $path = '' ) {
			static $dir;
			$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
			return $dir . $path;
		}

		/**
		 * This plugin's url.
		 *
		 * @since  1.0.2
		 * @param  string $path (optional) appended path.
		 * @return string       URL and path
		 */
		public static function url( $path = '' ) {
			static $url;
			$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
			return $url . $path;
		}

	}
endif; // End of line...


/**
 * The bpmedia function.
 *
 * Fire bpmedia instance method
 *
 * @return object
 */
function buddymedia() {
	return BP_Media::instance();
}
add_action( 'bp_include', 'buddymedia', 999 );

/**
 * The enqueue_scripts function.
 */
function bp_media_enqueue_admin_scripts() {
	wp_enqueue_script( 'bp-media-admin-js', plugins_url( 'admin/assets/js/bp-media-admin.js' , __FILE__ ), array(), filemtime( plugin_dir_path( __FILE__ ) . 'admin/assets/js/bp-media-admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'bp_media_enqueue_admin_scripts' );
