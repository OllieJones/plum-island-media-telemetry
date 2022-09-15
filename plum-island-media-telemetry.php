<?php
/**
 * Plum Island Media Telemetry
 *
 * @package       PIMTELEMETRY
 * @author        Ollie Jones
 * @license       gplv2
 * @version       1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:   Plum Island Media Telemetry
 * Plugin URI:    https://github.com/OllieJones/plum-island-media-telemetry
 * Description:   Accepts telemetry requests from other plugins
 * Version:       1.0.1
 * Author:        Ollie Jones
 * Author URI:    https://github.com/OllieJones
 * Text Domain:   plum-island-media-telemetry
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Plum Island Media Telemetry. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This file contains the main information about the plugin.
 * It is used to register all components necessary to run the plugin.
 * 
 * The comment above contains all information about the plugin 
 * that are used by WordPress to differenciate the plugin and register it properly.
 * It also contains further PHPDocs parameter for a better documentation
 * 
 * The function PIMTELEMETRY() is the main function that you will be able to 
 * use throughout your plugin to extend the logic. Further information
 * about that is available within the sub classes.
 * 
 * HELPER COMMENT END
 */

// Plugin name
define( 'PIMTELEMETRY_NAME',			'Plum Island Media Telemetry' );

// Plugin version
define( 'PIMTELEMETRY_VERSION',		'1.0.1' );

// Plugin Root File
define( 'PIMTELEMETRY_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'PIMTELEMETRY_PLUGIN_BASE',	plugin_basename( PIMTELEMETRY_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'PIMTELEMETRY_PLUGIN_DIR',	plugin_dir_path( PIMTELEMETRY_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'PIMTELEMETRY_PLUGIN_URL',	plugin_dir_url( PIMTELEMETRY_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once PIMTELEMETRY_PLUGIN_DIR . 'core/class-plum-island-media-telemetry.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Ollie Jones
 * @since   1.0.0
 * @return  object|Plum_Island_Media_Telemetry
 */
function PIMTELEMETRY() {
	return Plum_Island_Media_Telemetry::instance();
}

PIMTELEMETRY();
