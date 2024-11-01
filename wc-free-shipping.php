<?php
/**
 * Plugin Name: WooCommerce Free Shipping
 * Description: Boost your sales with free shipping!
 *
 * Version: 7.7.1
 * Author: Plugin Territory
 * Author URI: http://pluginterritory.com
 *
 * Text Domain: wc-free-shipping
 * Domain Path: /languages
 *
 * Requires at least: 5.0
 * Tested up to: 6.2.2
 *
 * Requires PHP: 5.6
 *
 * WC requires at least: 3.0
 * WC tested up to: 7.7.1
 *
 * 
 * Copyright: 2015-2023 Plugin Territory
 * License: GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// i18n
add_action( 'plugins_loaded', 'pt_wc_free_shipping_load_plugin_textdomain' );
function pt_wc_free_shipping_load_plugin_textdomain() {
	load_plugin_textdomain( 'wc-free-shipping', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// activation
function pt_wc_free_shipping_activate() {
	$woocommerce_free_shipping_settings = get_option( 'woocommerce_free_shipping_settings' );

	$default_values = array(
							'minimum_order_value' => $woocommerce_free_shipping_settings[ 'min_amount' ],
							'no_free_shipping'    => esc_html__( 'Free shipping at %s or more. Order additional %s and get free shipping...', 'wc-free-shipping' ),
							'free_shipping'       => esc_html__( 'Free shipping at %s or more. You have free shipping!', 'wc-free-shipping' ),
							'exclude_virtual'     => 'yes',
							'virtual_excluded'    => esc_html__( 'One or more virtual products were excluded from free shipping offer. Total excluded: %s', 'wc-free-shipping' ),
						);

	$settings = wp_parse_args( get_option( 'pt_wc_free_shipping_settings' ), $default_values );
	update_option( 'pt_wc_free_shipping_settings', $settings );
}
register_activation_hook( __FILE__, 'pt_wc_free_shipping_activate' );

// our init actions
add_action( 'plugins_loaded', 'pt_wc_free_shipping_init' );
function pt_wc_free_shipping_init() {

	// Init vars for our admin menu page
	add_action( 'admin_init', 'pt_wc_free_shipping_admin_init' );

	// Show our admin menu entry
	add_action( 'admin_menu', 'pt_wc_free_shipping_admin_menu' );

	// add a 'Settings' link to the plugin action links
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pt_wc_free_shipping_add_plugin_setup_link' );

	// ask for rating
	add_filter( 'admin_footer_text', 'pt_wc_free_shipping_admin_footer_text', 1, 2 );
}

function pt_wc_free_shipping_add_plugin_setup_link( $actions ) {
	$manage_url = admin_url( 'options-general.php?page=pt_wc_free_shipping_page' );
	$settings   = array( 'settings' => sprintf( '<a href="%s">%s</a>', $manage_url, esc_html__( 'Settings', 'wc-free-shipping' ) ) );
	// add the link to the front of the actions list
	return ( array_merge( $settings, $actions ) );
}

/**
 * Lets create the infrastructure for our plugin
 */
function pt_wc_free_shipping_admin_init() {

	register_setting( 'pt_wc_free_shipping_settings',
		'pt_wc_free_shipping_settings' );

	add_settings_section( 'pt_wc_free_shipping_settings_section',
		esc_html__('WooCommerce Free Shipping settings', 'wc-free-shipping') ,
		'pt_wc_free_shipping_fields',
		'pt_wc_free_shipping_settings_page' );
}

function pt_wc_free_shipping_fields() {

	$woocommerce_free_shipping_settings = get_option( 'woocommerce_free_shipping_settings', '' );

	$default_values = array(
							'minimum_order_value' => $woocommerce_free_shipping_settings[ 'min_amount' ],
							'no_free_shipping'    => esc_html__( 'Free shipping at %s or more. Order additional %s and get free shipping...', 'wc-free-shipping' ),
							'free_shipping'       => esc_html__( 'Free shipping at %s or more. You have free shipping!', 'wc-free-shipping' ),
							'exclude_virtual'     => '',
							'virtual_excluded'    => esc_html__( 'One or more virtual products were excluded from free shipping offer. Total excluded: %s', 'wc-free-shipping' ),

						);

	$settings = apply_filters( 'pt_wc_free_shipping_settings', wp_parse_args( get_option( 'pt_wc_free_shipping_settings' ), $default_values ) );

	?>
	<div class="options_group">
	  <table class="form-table">
	    <tr>
	      <th scope="row" valign="top"> <label for="minimum_order_value">
	        <?php echo apply_filters( 'pt_wc_free_shipping_minimum_value_text', esc_html__( 'Minimum order value (without taxes) for enabling free shipping', 'wc-free-shipping' ) ) ?>
	        </label>
	      </th>
	      <td>
	        <input type="text" name="pt_wc_free_shipping_settings[minimum_order_value]" id="minimum_order_value" class="regular-text"  value="<?php echo $settings['minimum_order_value']?>" />
	      	<p class="description">
	        <?php esc_html_e( 'Customers will need to order at least this amount to get free shipping.', 'wc-free-shipping' )?>
	        </p>
			</td>
	    </tr>
	    <tr>
	      <th scope="row" valign="top"> <label for="exclude_virtual">
	        <?php esc_html_e( 'Virtual products', 'wc-free-shipping' ) ?>
	        </label>
	      </th>
	      <td>
	      	<label for="exclude_virtual">
	      		<input type="checkbox" name="pt_wc_free_shipping_settings[exclude_virtual]" id="exclude_virtual" <?php checked( $settings['exclude_virtual'], '1' ); ?> value="1" />
	        	<?php esc_html_e( 'Skip virtual products', 'wc-free-shipping' ) ?>
	        
	      	</label>
	      <p class="description">
	        <?php esc_html_e( 'Don\'t include virtual products in minimum order value.', 'wc-free-shipping' )?>
	        </p> </td>
	    </tr>
	    <tr>
	      <th scope="row" valign="top"> <label for="no_free_shipping">
	        <?php esc_html_e( 'Order more  message', 'wc-free-shipping' ) ?>
	        </label>
	      </th>
	      <td>
	      	<input type="text" name="pt_wc_free_shipping_settings[no_free_shipping]" id="no_free_shipping" class="regular-text"  value="<?php echo $settings['no_free_shipping']?>" />
	      <p class="description">
	        <?php esc_html_e( 'Message at cart when minimum order value is not yet met, e.g. no free shipping.', 'wc-free-shipping' )?>
	        </p> </td>
	    </tr>
	    <tr>
	      <th scope="row" valign="top"> <label for="free_shipping">
	        <?php esc_html_e( 'Free shipping enabled message', 'wc-free-shipping' ) ?>
	        </label>
	      </th>
	      <td>
	      	<input type="text" name="pt_wc_free_shipping_settings[free_shipping]" id="free_shipping" class="regular-text"  value="<?php echo $settings['free_shipping']?>" />
	      <p class="description">
	        <?php esc_html_e( 'Message at cart when minimum order value is already met, e.g. free shipping.', 'wc-free-shipping' )?>
	        </p> </td>
	    </tr>
	    <tr>
	      <th scope="row" valign="top"> <label for="virtual_excluded">
	        <?php esc_html_e( 'Virtual excluded message', 'wc-free-shipping' ) ?>
	        </label>
	      </th>
	      <td>
	      	<input type="text" name="pt_wc_free_shipping_settings[virtual_excluded]" id="virtual_excluded" class="regular-text"  value="<?php echo $settings['virtual_excluded']?>" />
	      <p class="description">
	        <?php esc_html_e( 'Message at cart when one or more virtual products were excluded from order total, e.g. they don\'t ship and are not counted towards order total.', 'wc-free-shipping' )?>
	        </p> </td>
	    </tr>
			<?php
				do_action( 'pt_wc_free_shipping_after_settings' );
			?>
	  </table>
	</div>
	<?php
}

function pt_wc_free_shipping_admin_menu() {
	if ( current_user_can( 'manage_options' ) ) {
		add_options_page( esc_html__('WooCommerce Free Shipping', 'wc-free-shipping'),
			 esc_html__('Free Shipping', 'wc-free-shipping') ,
			 'manage_options',
			 'pt_wc_free_shipping_page',
			 'pt_wc_free_shipping_show_page');
	}
}

function pt_wc_free_shipping_show_page() {
?>
<div class="wrap">
  <div id="icon" class="icon32"></div>
	<div id="pt_wc_free_shipping_options" class="options_panel">
	  <form method="post" action="options.php">
		<?php
			settings_fields( 'pt_wc_free_shipping_settings' );
			do_settings_sections( 'pt_wc_free_shipping_settings_page' );
			submit_button();
		?>
	  </form>
	</div>
</div>
<?php
}

function pt_wc_is_free_shipping_enabled() {

	if ( false === ( $enabled = get_transient( 'pt_wc_free_shipping_enabled' ) ) ) { 

		$enabled = 'no';

		// this code runs when there is no valid transient set

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();

		foreach ( $raw_zones as $raw_zone ) {

			$zone    = new WC_Shipping_Zone( $raw_zone );
			$methods = $zone->get_shipping_methods();

			if ( ! empty( $methods ) ) {

				foreach ( $methods as $method ) {

					if ( 'free_shipping' === $method->id ) {

						$free_shipping_added = true;

						if ( 'yes' === $method->enabled ) {

							$enabled = 'yes';
							break 2;

						}
					}
				}
			}
		}
		set_transient( 'pt_wc_free_shipping_enabled', $enabled, 1 * HOUR_IN_SECONDS ); 
	}
	return $enabled;

}

function pt_wc_free_shipping_get_cart_virtual_value() {

	$subtotal = 0;

	foreach( WC()->cart->get_cart() as $item ) {

		if ( 'yes' == $item['data']->is_virtual() ) {

			$subtotal += $item['line_total'];

		}
	}

	return $subtotal;
}


function pt_wc_free_shipping_show_message() {

	if ( 'yes' === pt_wc_is_free_shipping_enabled() ) {

		$settings = get_option( 'pt_wc_free_shipping_settings' );
		$value    = apply_filters( 'pt_wc_free_shipping_minimum_value', WC()->cart->subtotal_ex_tax );

		if ( WC()->cart->needs_shipping() ) {

			if ( $settings[ 'exclude_virtual' ] ) {

				$value_virtual = pt_wc_free_shipping_get_cart_virtual_value(); 

				$value -= $value_virtual;

			}

			if ( $settings[ 'minimum_order_value' ] <= $value ) {

				wc_print_notice( sprintf( $settings[ 'free_shipping' ],    wc_price( $settings[ 'minimum_order_value' ] ) ) );

			} else {

				wc_print_notice( sprintf( $settings[ 'no_free_shipping' ], wc_price( $settings[ 'minimum_order_value' ] ), wc_price( $settings[ 'minimum_order_value' ] - $value ) ), 'notice' );

				if ( $settings[ 'exclude_virtual' ] && $value_virtual ) {

					wc_print_notice( sprintf( $settings[ 'virtual_excluded' ], wc_price( $value_virtual ) ), 'notice' );

				}

			}

			do_action( 'pt_wc_free_shipping_after_show_message', $value );

		}
	}
}
add_action(  'woocommerce_before_cart',          'pt_wc_free_shipping_show_message' );
add_action(  'woocommerce_before_mini_cart',     'pt_wc_free_shipping_show_message' );
add_action(  'woocommerce_before_checkout_form', 'pt_wc_free_shipping_show_message' );

/**
 * Mini Cart
 */
if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
    function woocommerce_mini_cart( $args = array() ) {

        // Show Default Cart View
        $defaults = array( 'list_class' => '' );
        $args     = wp_parse_args( $args, $defaults );
        wc_get_template( 'cart/mini-cart.php', $args );

    }
}

function pt_wc_free_shipping_is_available( $is_available ) {

	$settings = get_option( 'pt_wc_free_shipping_settings' );
	$value    = apply_filters( 'pt_wc_free_shipping_minimum_value', WC()->cart->subtotal_ex_tax );

	if ( $settings[ 'exclude_virtual' ] ) {

		$value -= pt_wc_free_shipping_get_cart_virtual_value();

	}

	if ( $settings[ 'minimum_order_value' ] <= $value ) {

		return true;

	} else {

		return false;

	}
}
add_filter( 'woocommerce_shipping_free_shipping_is_available', 'pt_wc_free_shipping_is_available' );

function pt_wc_free_shipping_check_is_enabled_admin_message() {

	global $pagenow;

	// Bail out if not our page
	if ( 'options-general.php' !== $pagenow || ( isset( $_GET['page'] ) && 'pt_wc_free_shipping_page' !== $_GET['page'] ) ) {

		return;

	}

	$free_shipping_added = false;

	$data_store = WC_Data_Store::load( 'shipping-zone' );
	$raw_zones  = $data_store->get_zones();

	foreach ( $raw_zones as $raw_zone ) {

		$zone    = new WC_Shipping_Zone( $raw_zone );
		$methods = $zone->get_shipping_methods();

		if ( ! empty( $methods ) ) {

			foreach ( $methods as $method ) {

				if ( 'free_shipping' === $method->id ) {

					$free_shipping_added = true;

					if ( 'yes' !== $method->enabled ) {

						$message = sprintf( esc_html__( 'WooCommerce Free Shipping needs you to please enable %s method at zone %s.' , 'pt-wc-ups-free-shipping' ), 
							'<strong>' . $method->get_method_title() . '</strong>', 
							'<strong><a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone->get_id() ) . '">' . $zone->get_zone_name() . '</a></strong>' );

						echo '<div class="error fade"><p>' . $message . '</p></div>' . "\n";

					}

					set_transient( 'pt_wc_free_shipping_enabled', $method->enabled, 1 * HOUR_IN_SECONDS );

				}
			}
		}
	}

	if ( ! $free_shipping_added ) {

		$message = sprintf( esc_html__( 'WooCommerce Free Shipping needs you to please add %s method to any shipping zone where you want to use it.' , 'pt-wc-ups-free-shipping' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping' ) . '">' . esc_html__( 'Free shipping', 'pt-wc-ups-free-shipping' ). '</a>' );

		echo '<div class="error fade"><p>' . $message . '</p></div>' . "\n";

	}

}
add_action( 'admin_notices', 'pt_wc_free_shipping_check_is_enabled_admin_message' );

function pt_wc_free_shipping_admin_footer_text( $footer_text ) {
	global $current_screen;

	// list of admin pages we want this to appear on
	$pages = array(
		'settings_page_pt_wc_free_shipping_page',
	);

	if ( isset( $current_screen->id ) && in_array( $current_screen->id, $pages ) ) {

		$footer_text = sprintf( __( 'Enoying this plugin? Then why not rate <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> for <strong>WooCommerce Free Shipping</strong> on <a href="%1$s" target="_blank">WordPress.org</a> and make the developer happy too? <a href="%1$s" target="_blank" title="Click and rate &#9733;&#9733;&#9733;&#9733;&#9733;, we both know it will make you feel good inside.">:-)</a>', 'wc-free-shipping' ), 'https://wordpress.org/support/view/plugin-reviews/wc-free-shipping?filter=5#postform' );
	}

	return $footer_text;
}
