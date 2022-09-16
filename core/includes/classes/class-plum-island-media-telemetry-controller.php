<?php

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
  }

  /**
   * @param WP_REST_Request $req
   *
   * @return string
   */
  function post( WP_REST_Request $req ): string {
    require_once ABSPATH . 'wp-admin/includes/admin.php';
    $upload           = $req->get_json_params();
    $uploadCategoryId = $this->maybeMakeCategory( 'Upload',
      'Telemetry data uploaded from a WordPress site',
      'telemetry_upload' );

    $tags = [];
    try {
      if ( is_array( $upload ) && array_key_exists( 'mysqlVer', $upload ) ) {
        $ver     = (object) $upload['mysqlVer'];
        $tags [] = $ver->unconstrained ? 'Barracuda' : 'Antelope';
        $tags [] = $ver->fork;
        $tags [] = "$ver->fork$ver->major.$ver->minor.$ver->build";
      }
      if ( is_array( $upload ) && array_key_exists( 'wordpress', $upload ) ) {
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
      /* empty, intentionally, don't croak if data is bogus */
    }

    $title    = [];
    $title [] = $upload['id'];
    if ( is_array( $upload ) && array_key_exists( 'monitor', $upload ) ) {
      $title [] = 'Monitor';
      $title [] = $upload['monitor'];
      $tags []  = 'Monitor';
    }
    $payload = '[renderjson]' . base64_encode( json_encode( (object) $upload ) ) . '[/renderjson]';
    $post    = [
      'post_author'    => 1,
      'post_excerpt'   => implode( '|', $tags ),
      'post_content'   => $payload,
      'post_title'     => implode( ' ', $title ),
      'post_status'    => 'publish',
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

}