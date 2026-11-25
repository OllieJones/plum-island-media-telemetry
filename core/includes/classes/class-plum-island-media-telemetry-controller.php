<?php
namespace PIMTELEMETRY;
use Exception;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Plum_Island_Media_Telemetry_Controller extends WP_REST_Controller {
  private int $version;
  private string $base;

  public function init() {
    $this->version   = '1';
    $this->namespace = 'plumislandmedia';
    $this->base      = 'upload';
    $this->add_hooks();
  }

  private function add_hooks() {

	  add_action( 'rest_api_init', function () {

		  register_rest_route( "$this->namespace/v$this->version", "/$this->base", [
			  [
				  'callback'            => [ $this, 'post' ],
				  'methods'             => 'POST',
				  'permission_callback' => [ $this, 'permission' ],
			  ],
		  ] );
	  } );


	  add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
		  $route = $request->get_route();
		  $ip    = $this->get_origin_ip_address();
		  $sensitive_routes = array(
			  '/' . $this->namespace . '/',
		  );

		  foreach ( $sensitive_routes as $sensitive_route ) {
			  if ( str_starts_with( $route, $sensitive_route ) ) {
				  if ( ! $this->rate_limit_check( $ip ) ) {
					  error_log( PIMTELEMETRY_NAME . ' request refused (429) due to rate limited IP ' . $ip );

					  return new WP_Error(
						  'rate_limit_exceeded',
						  'Too many requests. Please try again later.',
						  array( 'status' => 429 )
					  );
				  }
			  }
		  }

		  return $result;
	  }, 10, 3 );
  }

	/**
	 * @param WP_REST_Request $req
	 *
	 * @return string|WP_error
	 */
  function post( WP_REST_Request $req ): string|WP_Error {
    require_once ABSPATH . 'wp-admin/includes/admin.php';
    $upload           = $req->get_json_params();
    $uploadCategoryId = $this->maybeMakeCategory( 'Upload',
      'Telemetry data uploaded from a WordPress site',
      'telemetry_upload' );

    $tags = [];
    try {
	    if ( ! is_array( $upload )
	         || ! array_key_exists( 'id', $upload )
	         || ! is_string( $upload['id'] )
	         || strlen( $upload['id'] ) <= 4
	         || strlen( $upload['id'] ) > 31
	         || 1 === preg_match( '/[^-_a-zA-Z0-9]/', $upload['id'] ) ) {
		    error_log( PIMTELEMETRY_NAME . ' request refused (400) due to bogus upload id ' . $upload['id'] . ' from IP ' . $this->get_origin_ip_address() );
		    return new WP_Error( 'malformed', 'Malformed data', array( 'status' => 400 ) );
	    }
	    if ( array_key_exists( 'mysqlVer', $upload ) ) {
		    $ver     = (object) $upload['mysqlVer'];
		    $tags [] = $ver->unconstrained ? 'Barracuda' : 'Antelope';
		    $tags [] = $ver->fork;
		    $tags [] = "$ver->fork$ver->major.$ver->minor.$ver->build";
	    }
	    if ( array_key_exists( 'wordpress', $upload ) ) {
		    $ver     = (object) $upload['wordpress'];
		    $plugins = explode( '|', $ver->active_plugins );
		    foreach ( $plugins as $plugin ) {
			    $tags [] = 'P:' . $plugin;
		    }
		    $tags [] = 'WordPress ' . $ver->wp_version;
		    if ( $ver->is_multisite ) {
			    $tags[] = 'Multisite:' . $ver->current_blog_id;
		    }
	    }
    } catch ( Exception $e ) {
	    return new WP_Error( 'malformed', 'Malformed data', array( 'status' => 400 ) );
    }

    $title    = [];
    $title [] = $upload['id'];
    if ( is_array( $upload ) && array_key_exists( 'monitor', $upload ) ) {
      $title [] = 'Monitor';
      $title [] = $upload['monitor'];
      $tags []  = 'Monitor';
    }
	$title[] = '[' . $this->get_origin_ip_address() . ']';
    $payload = '[renderjson]' . base64_encode( json_encode( (object) $upload ) ) . '[/renderjson]';
    $post    = [
      'post_author'    => 1,
      'post_excerpt'   => implode( '|', $tags ),
      'post_content'   => $payload,
      'post_title'     => implode( ' ', $title ),
      'post_status'    => 'private',
      'post_category'  => [ $uploadCategoryId ],
      'comment_status' => 'open',
      'ping_status'    => 'closed',
      'tags_input'     => $tags,
    ];
    wp_insert_post( $post, true, true );

    return json_encode( (object) [] );
  }

  function permission( WP_REST_Request $req ): bool {
    if ( $req->get_method() === 'POST' ) {
      return true;
    }

    return current_user_can( 'read_private_posts' );
  }

  /**
   * @return int|mixed|string|string[]|WP_Error|null
   */
  private function maybeMakeCategory( $category, $description, $nicename ) {
    $uploadCategoryId = term_exists( $category );
    if ( ! $uploadCategoryId ) {
      $uploadCategoryId = wp_insert_category( [
        'cat_name'             => $category,
        'category_description' => $description,
        'category_nicename'    => $nicename,
      ] );
    }

    return $uploadCategoryId;
  }

	/** Check for hitting a rate limit.
	 *
	 * We rely on transient expiration to make this work.
	 *
	 * @param string $ip_address The origin IP address.
	 * @param integer $limit The number of operations allowed in ...
	 * @param integer $period ...this number of seconds.
	 *
	 * @return bool False if the request hit the limit.
	 */
	private function rate_limit_check( string $ip_address, int $limit = 10, int $period = 3600 ): bool {
		$transient_key = $this->namespace . '_' .  $this->base . '_' . 'rate_limit_' . md5( $ip_address );
		$requests = get_transient( $transient_key );

		if ( false === $requests ) {
			set_transient( $transient_key, 1, $period );
			return true;
		}

		if ( $requests >= $limit ) {
			return false;
		}

		set_transient( $transient_key, $requests + 1, $period );
		return true;
	}

	/**
	 * Retrieve the origin IP address of the request, possibly behind some proxy.
	 *
	 * @return string
	 */
	private function get_origin_ip_address () {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
			if (array_key_exists($key, $_SERVER) === true){
				foreach (explode(',', $_SERVER[$key]) as $ip){
					$ip = trim($ip);

					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
						return $ip;
					}
				}
			}
		}
		return '';
	}

}