<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * HELPER COMMENT START
 *
 * This is the main singleton class that is responsible for registering
 * the core functions, including the files and setting up all features.
 *
 * To add a new class, here's what you need to do:
 * 1. Add your new class within the following folder: core/includes/classes
 * 2. Create a new variable you want to assign the class to (as e.g. public $helpers)
 * 3. Assign the class within the instance() function ( as e.g. self::$instance->helpers = new Plum_Island_Media_Telemetry_Helpers();)
 * 4. Register the class you added to core/includes/classes within the includes() function
 *
 * HELPER COMMENT END
 */

if ( ! class_exists( 'Plum_Island_Media_Telemetry' ) ) :

  /**
   * Main Plum_Island_Media_Telemetry Class.
   *
   * @package    PIMTELEMETRY
   * @subpackage  Classes/Plum_Island_Media_Telemetry
   * @since    1.0.0
   * @author    Ollie Jones
   */
  final class Plum_Island_Media_Telemetry {

    /**
     * The real instance
     *
     * @access  private
     * @since  1.0.0
     * @var    object|Plum_Island_Media_Telemetry
     */
    private static $instance;

    /**
     * PIMTELEMETRY helpers object.
     *
     * @access  public
     * @since  1.0.0
     * @var    object|Plum_Island_Media_Telemetry_Helpers
     */
    public $helpers;

    /**
     * PIMTELEMETRY settings object.
     *
     * @access  public
     * @since  1.0.0
     * @var    object|Plum_Island_Media_Telemetry_Settings
     */
    public $settings;

    public $rest;

    /**
     * Throw error on object clone.
     *
     * Cloning instances of the class is forbidden.
     *
     * @access  public
     * @return  void
     * @since  1.0.0
     */
    public function __clone() {
      _doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'plum-island-media-telemetry' ), '1.0.0' );
    }

    /**
     * Disable unserializing of the class.
     *
     * @access  public
     * @return  void
     * @since  1.0.0
     */
    public function __wakeup() {
      _doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'plum-island-media-telemetry' ), '1.0.0' );
    }

    /**
     * Main Plum_Island_Media_Telemetry Instance.
     *
     * Insures that only one instance of Plum_Island_Media_Telemetry exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @access    public
     * @return    object|Plum_Island_Media_Telemetry  The one true Plum_Island_Media_Telemetry
     * @since    1.0.0
     * @static
     */
    public static function instance() {
      if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Plum_Island_Media_Telemetry ) ) {
        self::$instance = new Plum_Island_Media_Telemetry;
        self::$instance->base_hooks();
        self::$instance->includes();
        self::$instance->helpers  = new Plum_Island_Media_Telemetry_Helpers();
        self::$instance->settings = new Plum_Island_Media_Telemetry_Settings();
        self::$instance->rest = new Plum_Island_Media_Telemetry_Controller();
        self::$instance->rest->init();

        //Fire the plugin logic
        new Plum_Island_Media_Telemetry_Run();

        /**
         * Fire a custom action to allow dependencies
         * after the successful plugin setup
         */
        do_action( 'PIMTELEMETRY/plugin_loaded' );
      }

      return self::$instance;
    }

    /**
     * Include required files.
     *
     * @access  private
     * @return  void
     * @since   1.0.0
     */
    private function includes() {
      require_once PIMTELEMETRY_PLUGIN_DIR . 'core/includes/classes/class-plum-island-media-telemetry-helpers.php';
      require_once PIMTELEMETRY_PLUGIN_DIR . 'core/includes/classes/class-plum-island-media-telemetry-settings.php';
      require_once PIMTELEMETRY_PLUGIN_DIR . 'core/includes/classes/class-plum-island-media-telemetry-controller.php';

      require_once PIMTELEMETRY_PLUGIN_DIR . 'core/includes/classes/class-plum-island-media-telemetry-run.php';
    }

    /**
     * Add base hooks for the core functionality
     *
     * @access  private
     * @return  void
     * @since   1.0.0
     */
    private function base_hooks() {
      add_action( 'plugins_loaded', [ self::$instance, 'load_textdomain' ] );
    }

    /**
     * Loads the plugin language files.
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function load_textdomain() {
      load_plugin_textdomain( 'plum-island-media-telemetry', false, dirname( plugin_basename( PIMTELEMETRY_PLUGIN_FILE ) ) . '/languages/' );
    }

  }

endif; // End if class_exists check.