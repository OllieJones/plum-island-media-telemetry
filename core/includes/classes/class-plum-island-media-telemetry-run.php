<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * HELPER COMMENT START
 *
 * This class is used to bring your plugin to life.
 * All the other registered classed bring features which are
 * controlled and managed by this class.
 *
 * Within the add_hooks() function, you can register all of
 * your WordPress related actions and filters as followed:
 *
 * add_action( 'my_action_hook_to_call', array( $this, 'the_action_hook_callback', 10, 1 ) );
 * or
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * or
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 *
 * Once added, you can create the callback function, within this class, as followed:
 *
 * public function the_action_hook_callback( $some_variable ){}
 * or
 * public function the_filter_hook_callback( $some_variable ){}
 * or
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 *
 *
 * HELPER COMMENT END
 */

/**
 * Class Plum_Island_Media_Telemetry_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package    PIMTELEMETRY
 * @subpackage  Classes/Plum_Island_Media_Telemetry_Run
 * @author    Ollie Jones
 * @since    1.0.0
 */
class Plum_Island_Media_Telemetry_Run {
  /**
   * Our Plum_Island_Media_Telemetry_Run constructor
   * to run the plugin logic.
   *
   * @since 1.0.0
   */
  function __construct() {
    $this->add_hooks();
  }

  /**
   * ######################
   * ###
   * #### WORDPRESS HOOKS
   * ###
   * ######################
   */

  /**
   * Registers all WordPress and plugin related hooks
   *
   * @access  private
   * @return  void
   * @since  1.0.0
   */
  private function add_hooks() {

    add_shortcode( 'renderjson', [ $this, 'jsonShortcode' ] );

    add_filter( 'generate_post_date_output', [$this, 'post_date_and_time'],15,2);
  }

  /** Generate
   *
   * @param string $date the already-generated date (ignored)
   *
   * @return string the date string we generate. It must end with a space for correct formatting.
   */
  public function post_date_and_time( string $date): string {

    $isodate = esc_attr(get_the_date('c'));
    $textdate = esc_html(get_the_date() . ' ' . get_the_time());
    $fmt = '<span class="posted-on"><time class="entry-date published" datetime="%1$s" itemprop="datePublished">%2$s</time></span> ';
    return sprintf ($fmt, $isodate, $textdate);
  }
  /**
   * /**
   * The [json] shortcode.
   *
   * Renders JSON between [json]<pre>    and </pre>[/json]
   *
   * @param array $atts Shortcode attributes. Default empty.
   * @param string $content Shortcode content. Default null.
   * @param string $tag Shortcode tag (name). Default empty.
   *
   * @return string Shortcode output.
   */
  public function jsonShortcode( $atts = [], $content = null, $tag = '' ) {

    wp_enqueue_style( 'renderjson',
      PIMTELEMETRY_PLUGIN_URL . 'core/includes/assets/css/renderjson.css',
      [],
      PIMTELEMETRY_VERSION );
    wp_enqueue_script( 'renderjson',
      PIMTELEMETRY_PLUGIN_URL . 'core/includes/assets/js/renderjson.js',
      [],
      PIMTELEMETRY_VERSION,
      false );

    // place to render.
    $o = '<div class="json render"><div id="json_render"></div></div>';

    $o .= '
      <script>
      debugger
        window.addEventListener("DOMContentLoaded", ev => {
          let uuJson = ';

    $o .= "'";
    $o .= $content;
    $o .= "'" . PHP_EOL;
    $o .= 'document.getElementById(\'json_render\')
           .appendChild(
              renderjson
              .set_show_to_level(1)
              .set_sort_objects(false)
              .set_icons(\'►\', \'▼\')
              .set_max_string_length(50)
            ( JSON.parse(atob(uuJson)) )
         )
   });
      </script>';

    // can't apply content filters here
    //$o .= apply_filters( 'the_content', $content );

// return output
    return $o;
  }
}
