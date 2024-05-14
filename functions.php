<?php



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
