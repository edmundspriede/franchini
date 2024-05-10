<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.0.1' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}







add_filter( 'woocommerce_product_import_process_item_data', function ( $data ) {
	global $raw_image_id, $raw_gallery_image_ids;
	if ( isset( $data['raw_image_id'] ) ) {
		$raw_image_id = $data['raw_image_id'];//save to process later
		unset( $data['raw_image_id'] );//unset this so that images are not imported by WooCommerce
	} else {
		$raw_image_id = '';
	}
	if ( isset( $data['raw_gallery_image_ids'] ) ) {
		$raw_gallery_image_ids = $data['raw_gallery_image_ids'];//save to process later
		unset( $data['raw_gallery_image_ids'] );//unset this so that images are not imported by WooCommerce
	} else {
		$raw_gallery_image_ids = array();
	}

	return $data;
} );
add_action( 'woocommerce_product_import_inserted_product_object', function ( $product, $data ) {
	global $raw_image_id, $raw_gallery_image_ids;
	
	exit();
	if ( class_exists( 'EXMAGE_WP_IMAGE_LINKS' ) ) {
		$save = false;
		if ( $raw_image_id ) {
			$add_image = EXMAGE_WP_IMAGE_LINKS::add_image( $raw_image_id, $image_id );
			if ( $add_image['id'] ) {
				$product->set_image_id( $add_image['id'] );
				$save = true;
			}
		}
		if ( $raw_gallery_image_ids ) {
			$gallery_image_ids = array();

			foreach ( $raw_gallery_image_ids as $image_url ) {
				$add_image = EXMAGE_WP_IMAGE_LINKS::add_image( $image_url, $image_id );
				if ( $add_image['id'] ) {
					$gallery_image_ids[] = $add_image['id'];
				}
			}
			if ( $gallery_image_ids ) {
				$product->set_gallery_image_ids( $gallery_image_ids );
				$save = true;
			}
		}
		if ( $save ) {
			$product->save();
		}
	}

}, 10, 2 );

function exmage_add_image( WP_REST_Request $request ) {
  
  $url = $request['url'];
  if ( class_exists( 'EXMAGE_WP_IMAGE_LINKS' ) ) {
				$post_parent    = 0;//ID of the post that you want this image to be attached to
				$external_image = EXMAGE_WP_IMAGE_LINKS::add_image( $url, $image_id, $post_parent );
			}	
	
  $response = new WP_REST_Response( array($url,$external_image ) );	
  return $response;	
}	

add_action( 'rest_api_init', function () {
  register_rest_route( 'exmage/v1', 'add', array(
    'methods' => 'GET',
    'callback' => 'exmage_add_image',
  ) );
} );


function allow_unsafe_urls ( $args ) {
       $args['reject_unsafe_urls'] = false;
       return $args;
    } ;

add_filter( 'http_request_args', 'allow_unsafe_urls' );

function ir_webhook_http_args($http_args , $arg, $id){
  
  return array_merge($http_args, array('sslverify'   => false));
}

add_action( 'woocommerce_webhook_http_args', 'ir_webhook_http_args', 10, 3 );


// Register webhook endpoint
add_action('rest_api_init', 'register_webhook_endpoint');
function register_webhook_endpoint() {
    register_rest_route('webhook/v1', '/new-customer', array(
        'methods' => 'POST',
        'callback' => 'new_customer_webhook_handler',
    ));
}

// Webhook handler function
function new_customer_webhook_handler($data) {
    $user_id = $data['user_id'];
    $user = get_userdata($user_id);
    
    // Check if the user has the role 'customer'
    if (in_array('customer', $user->roles)) {
        // Here you can perform actions you want when a new customer is created
        // For example, send a notification, update a database, etc.
        
        // For demonstration purposes, let's just log the event
        error_log('New customer created: ' . $user->user_login);
    }
}

// Hook into user registration to trigger the webhook
add_action('user_register', 'trigger_new_customer_webhook', 10, 1);
function trigger_new_customer_webhook($user_id) {
    $user = get_userdata($user_id);
    
    // Check if the user has the role 'customer'
    if (in_array('customer', $user->roles)) {
          // URL of the web service
          $url = 'https://oir.app.n8n.cloud/webhook/8eb2d216-1250-4921-897f-afe4f2bbeda6';

          // Data to send in the POST request
      	  $data = array( 'user' => $user );

          // Initialize cURL session
          $ch = curl_init($url);

          // Set cURL options for the POST request
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
          // Disable SSL verification
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

          // Execute the cURL session and store the response in $response
          $response = curl_exec($ch);

          // Check for cURL errors
          if (curl_errno($ch)) {
                    echo 'cURL error: ' . curl_error($ch);
          }

          // Close cURL session
          curl_close($ch);
   
    }
}


add_action('rest_api_init', 'register_custom_endpoint_1');

function register_custom_endpoint_1() {
    register_rest_route('custom/v1', '/update-brand/', array(
        'methods' => 'POST',
        'callback' => 'update_brand_meta',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));
}

function update_brand_meta(WP_REST_Request $request) {
    $brand = $request->get_param('name');
    $image_url = $request->get_param('url');
	$media = $request->get_param('media');
	$error = "";
	
	$existing_term = get_term_by('name', $brand, 'brands');

    // If the term exists, update it, otherwise create a new one
    if ($existing_term) {
        $term_id = $existing_term->term_id;
        wp_update_term($term_id, 'brands' , array('name' => $term_name));
		
    } else {
        $term = wp_insert_term($brand, 'brands');
        if (is_wp_error($term)) {
            // Handle error if term insertion fails
            $error = $term->get_error_message();
            return new WP_REST_Response(['success' => true,  "error" => $error ], 200);
        }
        $term_id = $term['term_id'];
    }

    // Add meta data for the term
    if ($term_id) {
        // Add or update meta data with key 'image'
        //update_term_meta($term_id, 'image', $image_url);
	    update_term_meta($term_id, 'brand-image', $media);
        
    } else {
         $error = "fail";
    }
	
	return new WP_REST_Response(['success' => true, "id" => $term_id, "error" => $error ], 200);
	
}	


add_action('rest_api_init', 'register_custom_endpoint_2');

function register_custom_endpoint_2() {
    register_rest_route('custom/v1', '/add-brand-product/', array(
        'methods' => 'POST',
        'callback' => 'add_brand_product',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));
}

function add_brand_product(WP_REST_Request $request) {
    $brand = $request->get_param('brand');
    $product = $request->get_param('id');
	$error = "";
	
	$existing_term = get_term_by('name', $brand, 'brands');

    // If the term exists, update it, otherwise create a new one
    if ($existing_term) {
        $term_id = $existing_term->term_id;
        $t = wp_set_object_terms($product, $term_id, 'brands', true); // Replace 'product_cat' with your taxonomy name (e.g., 'product_tag' for tags)
   
		
    } else {
      
            return new WP_REST_Response(['success' => false,  "error" => $error ], 200);
          }
       
    

   
	
	return new WP_REST_Response(['success' => true, "id" => $product, "terms" => $t ], 200);
	
}	
