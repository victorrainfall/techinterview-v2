<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


/**
 * Custom functions
 */

/**
 * @snippet     Show current year
 * @author      Victor Hsu
 * @date        14-01-2025
 */
 
function year_shortcode() {
	$year = date('Y');
	return $year;
}
add_shortcode('year', 'year_shortcode');


/**
 * @snippet     Home - Product Grid
 * @author      Victor Hsu
 * @date        15-01-2025
 */

add_shortcode('home_product_grid', 'home_product_grid_callback');
function home_product_grid_callback()
{
    ob_start();
    
    $args = array(
        'post_type'         => 'product',
        'status'            => 'publish',
        'orderby'           => 'date',
        'order'             => 'DESC',
        'posts_per_page'    => 40,
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) :
    ?>
        <div class="home-product-grid">
            <?php
            while ( $the_query->have_posts() ) :
                $the_query->the_post();
                $pid = get_the_ID();
                $permalink = get_permalink( $pid );

                $title = get_the_title( $pid );
                $img_url = get_the_post_thumbnail_url($pid, 'full');
                $salespoints = get_field('salespoints');
                $features = get_field('features');

                $product = wc_get_product( $pid );
                $price = floatval( $product->get_price() );
				$regular_price = floatval( $product->get_regular_price() );
				$sale_price = floatval( $product->get_sale_price() );
				$formatted_price = '$' . number_format($price, 0, '.', ',');
                $formatted_regular_price = '$' . number_format($regular_price, 0, '.', ',');
				$formatted_sale_price = '$' . number_format($sale_price, 0, '.', ',');

            ?>
            
                <a class="grid-item" href="<?= $permalink ?>">
                    <div class="img-ctn">
                        <img src="<?= $img_url ?>" width="100%" />
                        <?php check_product_tags( $product ); ?>
                    	<button class="add2cart-btn" data-pid="<?= $pid ?>"><i class="fa-solid fa-cart-shopping"></i>Add to Cart</button>
                    </div>
                    <div class="text-ctn">
                        <h4 class="title"><?= $title ?></h4>
                        
                        <?php
						if( $salespoints ): ?>
							<div class="salespoints-ctn"><?= $salespoints; ?></div>
						<?php endif; ?>

                        <?php
						if( $features ): ?>
						<ul class="features-ctn">
						    <?php foreach( $features as $feature ): ?>
						        <li><?php check_product_features( $feature ); ?></li>
						    <?php endforeach; ?>
						</ul>
						<?php endif; ?>

						<ul class="price-ctn">
							<?php if($sale_price && $sale_price < $regular_price) : ?>
								<li class="regular-price"><del><?= $formatted_regular_price ?></del></li>
								<li class="sale-price"><?= $formatted_sale_price ?></li>
							<?php else : ?>
								<li class="sale-price"><?= $formatted_price ?></li>
							<?php endif; ?>
						</ul>

                    </div>
                </a>

            <?php endwhile; ?>
        </div>
    <?php
    endif;
    wp_reset_postdata();
    
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
};

function check_product_tags( $product ) {
    $tag_conditions = array(
        'new' => '<div class="tag-ctn green"><i class="fa-solid fa-plus"></i></i>New</div>',
        'on-sale' => '<div class="tag-ctn red"><i class="fa-regular fa-thumbs-up"></i>On Sale</div>',
    );
    foreach ($tag_conditions as $tag_name => $return_value) {
        $tag_id = get_term_by('slug', $tag_name, 'product_tag');
        if ($tag_id && has_term($tag_id->term_id, 'product_tag', $product->get_id())) {
            echo $return_value;
        }
    }
}

function check_product_features($input) {
    switch ($input) {
        case 'online':
            echo '<i class="fa-solid fa-earth-oceania green" title="Available online and ready to ship."></i>';
            break;
        case 'retail':
            echo '<i class="fa-solid fa-shop green" title="Available at one or more retail stores."></i>';
            break;
        case 'no-retail':
            echo '<i class="fa-solid fa-shop grey" title="Not available to purchase at retail stores."></i>';
            break;
        case 'free-shipping':
            echo '<i class="fa-regular fa-calendar-check red" title="Eligible for free shipping."></i>';
            break;
        case 'same-day-dispatch':
            echo '<i class="fa-solid fa-truck-fast blue" title="Order this product before 2pm for same day dispatch"></i>';
            break;
        default:
            break;
    }
}


/**
 * @snippet     Custom JS
 * @author      Victor Hsu
 * @date        15-01-2025
 */


function my_custom_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            // Account Text
            updateHeaderContent();

            function updateHeaderContent() {
                $.ajax({
                    url: '<?= admin_url('admin-ajax.php') ?>',
                    type: 'POST',
                    data: {
                        action: 'update_header_content',
                    },
                    success: function (response) {
                        if (response.success) {
                            if (response.data.logged_in) {
                                $('.header-account-text').html('<i class="fa-regular fa-circle-user"></i>My Account');
                            } else {
                                $('.header-account-text').html('<i class="fa-solid fa-arrow-right-to-bracket"></i>Login');
                            }
                            $('.header-cart-text').html(response.data.cart_subtotal || '$0.00');
                        }
                    },
                });
            }
            
            // Tooltip
            $('.home-product-grid .text-ctn .features-ctn li i').tooltip({
                position: {
                    my: "center bottom-10",
                    at: "center top"
                }
            });


            // Hide adminbar on scroll
            var targetElement = $('#wpadminbar');
            var scrollThreshold = 20;

            $(window).scroll(function () {
                if ($(this).scrollTop() > scrollThreshold) {
                    targetElement.addClass('hide-for-now');
                } else {
                    targetElement.removeClass('hide-for-now');
                }
            });


            // Add to Cart & Redirect
            $('.home-product-grid .add2cart-btn').click(function( e ){
                e.preventDefault();
                var button = $(this);
                var productId = button.data('pid');
                $.ajax({
                    url: '<?= admin_url('admin-ajax.php') ?>',
                    type: 'POST',
                    data: {
                        action: 'add_to_cart_ajax',
                        product_id: productId,
                    },
                    beforeSend: function () {
                        button.html('<i class="fa-solid fa-cart-shopping"></i>Adding to cart...');
                        button.prop('disabled', true); 
                    },
                    success: function (response) {
                        if (response.success) {
                            button.html('<i class="fa-solid fa-check"></i> Added to cart');
                            window.location.href = '/victortest/cart/';
                        } else {
                            button.html('<i class="fa-solid fa-cart-shopping"></i> Add to Cart');
                            button.prop('disabled', false);
                            console.log('Failed to add product:', response.data);
                        }
                    },
                });

            });


            // Update or remove from Cart
            $( document.body ).on( 'updated_cart_totals removed_from_cart', function(){
                updateHeaderContent();
            });

        });
    </script>
    <?php
}
add_action('wp_footer', 'my_custom_js');


function login_or_account_shortcode() {
    if (is_user_logged_in()) {
        return '<i class="fa-regular fa-circle-user"></i>My Account';
    } else {
        return '<i class="fa-solid fa-arrow-right-to-bracket"></i>Login';
    }
}
add_shortcode('login_or_account', 'login_or_account_shortcode');


function cart_subtotal_shortcode() {
    if (class_exists('WC_Cart')) {
        $cart = WC()->cart;
        if ($cart && $cart->get_cart_contents_count() > 0) {
            return $cart->get_cart_subtotal();
        }
    }
    return '$0.00';
}
add_shortcode('cart_subtotal', 'cart_subtotal_shortcode');


function ajax_update_header_content() {
    $response = [
        'logged_in' => is_user_logged_in(),
        'cart_subtotal' => '$0.00',
    ];

    if (class_exists('WC_Cart') && WC()->cart) {
        $cart = WC()->cart;
        if ($cart->get_cart_contents_count() > 0) {
            $response['cart_subtotal'] = $cart->get_cart_subtotal();
        }
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_update_header_content', 'ajax_update_header_content');
add_action('wp_ajax_nopriv_update_header_content', 'ajax_update_header_content');


function add_to_cart_ajax_handler() {
    if (!isset($_POST['product_id'])) {
        wp_send_json_error(['message' => 'Product ID missing.']);
    }

    $product_id = intval($_POST['product_id']);
    $added = WC()->cart->add_to_cart($product_id);

    if ($added) {
        wp_send_json_success(['message' => 'Product added to cart.']);
    } else {
        wp_send_json_error(['message' => 'Failed to add product to cart.']);
    }
}
add_action('wp_ajax_add_to_cart_ajax', 'add_to_cart_ajax_handler');
add_action('wp_ajax_nopriv_add_to_cart_ajax', 'add_to_cart_ajax_handler');


