<?php
/*
Plugin Name:  Digital Assets Plugin
Plugin URI:   https://lumensmarket.com
Description:  Changes digital currency quantity to USD amount.
Version:      1/15/2018
Author:       tinyhazeleyes
Author URI:   https://developer.wordpress.org/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  https://lumensmarket.com
Domain Path:  /languages
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class digital_assets {
    	/**
	 * The only instance of digital_assets.
	 *
	 * @var digital_assets
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return digital_assets
	 */
	public static function instance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new digital_assets();

			self::$instance->includes();
		}
		return self::$instance;
	}
    private function __construct() {
    	add_action('admin_menu', 'register_digital_assets_submenu_page',99);
    	add_action( 'init', 'custom_remove_footer_credit', 10 );
    	add_filter( 'registration_redirect', 'my_redirect_home' );
    	add_filter( 'woocommerce_product_tabs', 'remove_woocommerce_product_tabs', 98 );
    	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_product_description_tab' );
	//add_action( 'woocommerce_after_single_product_summary', 'woocommerce_product_additional_information_tab' );
	//add_action( 'woocommerce_after_single_product_summary', 'comments_template' );
	add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
	add_action('woocommerce_after_checkout_validation', 'after_checkout_validation');
	add_filter('woocommerce_sale_flash', 'woo_custom_hide_sales_flash');
	//add_action('woocommerce_payment_complete', 'woocommerce_send_coins', 10, 1);
	add_action( 'woocommerce_order_status_completed', 'woocommerce_send_coins', 10, 1 );
	add_action( 'woocommerce_email_before_order_table', 'add_order_instruction_email', 15, 2 );
	add_filter('woocommerce_get_price_html', "only_sale_price", 99, 2);
	add_action( 'template_redirect', 'custom_shop_page_redirect' );
	add_filter('woocommerce_get_price', 'my_custom_price', 99);
	add_filter( 'woocommerce_registration_redirect', 'user_verification_woocommerce_registration_redirect', 10, 1 );
	add_action('woocommerce_before_customer_login_form', 'registration_message', 2);
	add_filter( 'woocommerce_registration_redirect', 'user_verification_woocommerce_registration_redirect', 10, 1 );
	}
    
	public function get_plugin_dir() {
		return plugin_dir_path( __FILE__ );
	}

	public function includes() {
		require_once( $this->get_plugin_dir() . 'include/autoload.php' );
		
		require_once( $this->get_plugin_dir() . 'include/digitalassets/settings.php' );
		
	}
	
	public function get_site_key( $site_key_encoded = false) {
	    
    	if (empty($site_key_encoded)) {
    		$protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey(AUTH_KEY);
    		$protected_key_encoded = $protected_key->saveToAsciiSafeString();
    		$protected_key = KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded);
    		$site_key = $protected_key->unlockKey(AUTH_KEY);
    		$site_key_encoded = $site_key->saveToAsciiSafeString();
    		$site_key = Key::loadFromAsciiSafeString($site_key_encoded);
    		add_option('digital_assets_site_key_encoded', $site_key_encoded);
    		add_option('digital_assets_binance_key', '');
    		add_option('digital_assets_binance_secret', '');
    		return $site_key;
    	} else {
    		$site_key = Key::loadFromAsciiSafeString($site_key_encoded);
    		return $site_key;
    	}
}

} // End Class

function digital_assets() {
	return digital_assets::instance();
}

digital_assets();

/**
 * Check if WooCommerce is active
 **/
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
	$site_key_encoded = get_option('digital_assets_site_key_encoded');
	$site_key = digital_assets::get_site_key( $site_key_encoded );
	$digital_assets_binance_key_encrypted =	get_option('digital_assets_binance_key_encrypted');
	$digital_assets_binance_secret_encrypted = get_option('digital_assets_binance_secret_encrypted');
	if (!empty($digital_assets_binance_key_encrypted) && !empty($digital_assets_binance_secret_encrypted) ) {
	    $decrypted_api_key = Crypto::decrypt($digital_assets_binance_key_encrypted, $site_key);	
	    $decrypted_api_secret = Crypto::decrypt($digital_assets_binance_secret_encrypted, $site_key);
	    print("working");	
    	}

	$api = new Binance\API($decrypted_api_key,$decrypted_api_secret);
	$mem_var = new Memcached();
	$mem_var->addServer("127.0.0.1", 11211);
    
	$ticker = $mem_var->get("Binance_Tracker_CLI");

	if (empty($ticker)) {
	    $ticker = $mem_var->get("Binance_Tracker");
	    if (empty($ticker)) {
			$ticker = $api->prices();
			$mem_var->set("Binance_Tracker", $ticker, 5) or die(" Keys Couldn't be Created.");
	    }
	}
		
	if (empty($ticker)) {
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price',10);
		add_filter( 'woocommerce_is_purchasable', false );
	}
		
	function register_digital_assets_submenu_page() {
	    add_submenu_page( 'woocommerce', 'Digital Assets', 'Digital Assets', 'manage_options', 'digital_assets', 'digital_assets_submenu_page_callback' ); 
	}
	function digital_assets_submenu_page_callback() {
		?>
		<div class="wrap">
		<h1><?php _e( 'Notes:'); ?></h1>
		<p><?php _e( 'Helpful stuff here'); ?></p>
		</div>
		<?php
	}
	
	function custom_remove_footer_credit () {
	    remove_action( 'storefront_footer', 'storefront_credit', 20 );
	    add_action( 'storefront_footer', 'custom_storefront_credit', 20 );
	} 
	
	function custom_storefront_credit() {
		?>
		<div class="site-info">
			&copy; <?php echo get_bloginfo( 'name' ) . ' ' . get_the_date( 'Y' ); ?>
		</div><!-- .site-info -->
		<?php
	}
	
	function my_redirect_home( $registration_redirect ) {
	    return home_url('/registered');
	}
	
	function remove_woocommerce_product_tabs( $tabs ) {
		unset( $tabs['description'] );
		unset( $tabs['reviews'] );
		unset( $tabs['additional_information'] );
		return $tabs;
	}

	function custom_override_checkout_fields( $fields ) {
		$fields['order']['order_comments']['placeholder'] = 'Make sure to put the correct Stellar address or you will not get your coins and your order cannot be refunded.';
		$fields['order']['order_comments']['label'] = 'Stellar Address';
		$fields['order']['order_comments']['required'] = true;
		return $fields;
	}
	
	function after_checkout_validation( $posted ) {
	    $string = $_POST['order_comments'];
	    $length = strlen($string);
	    $firstCharacter = $string[0];
	
	    if ($firstCharacter != "G" || $length != 56) {
	         wc_add_notice( __( "Not Stellar Address", 'woocommerce' ), 'error' );
	    }
	
	}
	
	function woo_custom_hide_sales_flash()
	{
	    return false;
	}
	
	function woocommerce_send_coins($order_id) {
	    global $api;
	    $asset = "XLM";
	    $binancefee = 0.010;
	    $order = wc_get_order( $order_id );
	    $address = $order->get_customer_note();
	    $totalcoins = totalcoins($order_id) + $binancefee;
	    $currenttotalprice = my_custom_price($totalcoins);
	    $totalorderprice = $order->get_total();
	    if($currenttotalprice < ($totalorderprice * 1.02)) {
	        $api->withdraw($asset, $address, $totalcoins, '', 'DigitalAssets');
	    } 
	}

	function add_order_instruction_email($order) {
	    $totalcoins = totalcoins($order->get_id());
	    $currenttotalprice = my_custom_price($totalcoins);
	    $totalorderprice = $order->get_total();
	    if($currenttotalprice >= ($totalorderprice * 1.02)) {
		echo '<p><strong>Error:</strong> The market price increased too much while you were trying to purchase your coins.  Please contact us immediately.</br>Market prices change dramatically and you must make your purchase immediately to ensure it processes correctly.  You will lose your account if you continue to have issues with paying in a timely manner!</p>';
	      }
	}
	
	function totalcoins($order_id) {
	    $order = wc_get_order( $order_id );
	    $items = $order->get_items();
	    $totalcoins  = 0;
	    foreach ($items as $item) {
	            $totalcoins = (get_post_meta( $item['product_id'] , '_regular_price',true) + $totalcoins) * $item['qty'];
	    }
	    return $totalcoins;
	}
	
	function only_sale_price($price, $product){
	    return wc_price($product->get_price());        
	}
	
	function custom_shop_page_redirect() {
		if(($options['digital_assets_enable'] == 'no') && (is_checkout() || is_cart()) ) {
			wp_redirect( home_url('/disabled' ) );
		}
	}
	
	function market_coin_price($ticker) {
		return ($ticker['ETHUSDT'] * $ticker['XLMETH']);
	}
	
	//Return the new price (this is the price that will be used everywhere in the store)
	function my_custom_price($numberofcoins){
		global $ticker; 
		$options = get_option( 'digital_assets_options' );
	
	 	if ($options['digital_assets_enable'] == 'no') {
	 		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price',10);
			add_filter( 'woocommerce_is_purchasable', false );		
	 	}
	 	
	 	if (market_coin_price($ticker) > get_option( 'digital_assets_minimum_coin_price' )) {
				$coinprice = market_coin_price($ticker);
				$new_price = $coinprice * $numberofcoins * (1 + get_option( 'digital_assets_percentage_fee' )/100) + get_option( 'digital_assets_flat_fee' );
				return $new_price;				
		} else {
				$coinprice = get_option( 'digital_assets_minimum_coin_price' );
				$new_price = $coinprice * $numberofcoins * (1 + get_option( 'digital_assets_percentage_fee' )/100) + get_option( 'digital_assets_flat_fee' );
				return $new_price;
		}
		
	}
	
	function user_verification_woocommerce_registration_redirect(){
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$approved_status = get_user_meta($user_id, 'user_activation_status', true);
			//if the user hasn't been approved destroy the cookie to kill the session and log them ou
			if ( $approved_status == 1 ){
				return get_permalink(wc_get_page_id('myaccount'));
			} else{
				wp_logout();
				return get_permalink(woocommerce_get_page_id('myaccount')) . "?approved=false";
			}
		}
	}
	
	function registration_message(){
	  $not_approved_message = '<p class="registration">Send in your registration application today!<br /> NOTE: Your account will be held for moderation and you will be unable to login until it is approved.  You are required to submit your license and photo immediately after registering.  Do NOT register if you cannot provide the required information or your account will be denied.</p>';
	  if( isset($_REQUEST['approved']) ){
	    $approved = sanitize_text_field($_REQUEST['approved']);
	    if ($approved == 'false')  echo '<p class="registration successful">Registration successful! You will be notified upon approval of your account.</p>';
	    else echo $not_approved_message;
	  }
	  else echo $not_approved_message;
	}
}	
?>