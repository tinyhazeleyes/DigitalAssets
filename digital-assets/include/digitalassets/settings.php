<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function digital_assets_settings_init() {

	register_setting( 'digital_assets', 'digital_assets_options' );
	register_setting( 'digital_assets', 'digital_assets_minimum_coin_price' );
	register_setting( 'digital_assets', 'digital_assets_percentage_fee' );
	register_setting( 'digital_assets', 'digital_assets_flat_fee' );
 	register_setting( 'digital_assets', 'digital_assets_binance_key' );
 	register_setting( 'digital_assets', 'digital_assets_binance_secret' );
 	
	add_settings_section(
	'digital_assets_general',
	__( 'Settings', 'digital_assets' ),
	'digital_assets_general_section',
	'digital_assets'
	);
 
	add_settings_field(
	'digital_assets_enabled',
	__( 'Enable', 'digital_assets' ),
	'digital_assets_enabled',
	'digital_assets',
	'digital_assets_general',
	[
	'label_for' => 'digital_assets_enable',
	'class' => 'digital_assets_row',
	'digital_assets_custom_data' => 'custom',
	]

	);
	
	add_settings_field(
	'digital_assets_binance_key',
	__( 'Binance Key', 'digital_assets' ),
	'digital_assets_binance_key',
	'digital_assets',
	'digital_assets_general',
	[
	'label_for' => 'digital_assets_binance_key',
	'class' => 'digital_assets_row',
	'digital_assets_custom_data' => 'custom',
	]

	);
 
 	add_settings_field(
	'digital_assets_binance_secret',
	__( 'Binance Secret', 'digital_assets' ),
	'digital_assets_binance_secret',
	'digital_assets',
	'digital_assets_general',
	[
	'label_for' => 'digital_assets_binance_secret',
	'class' => 'digital_assets_row',
	'digital_assets_custom_data' => 'custom',
	]

	);
	
	$coin = array('name' => 'Stellar Lumens' , 'symbol' => 'XLM');
    digital_assets_coin_sections($coin);

}
 
add_action( 'admin_init', 'digital_assets_settings_init' );


function digital_assets_coin_sections($coin) {
    
    add_settings_section(
	'digital_assets_'.strtolower($coin['symbol']).'_coin',
	__( '', 'digital_assets' ),
	'digital_assets_'.strtolower($coin['symbol']).'_coin_section',
	'digital_assets'
	);
	
	
	add_settings_field(
	'digital_assets_minimum_coin_price',
	__( 'Minimum Price', 'digital_assets' ),
	'digital_assets_minimum_coin_price',
	'digital_assets',
	'digital_assets_'.strtolower($coin['symbol']).'_coin',
	[
	'label_for' => 'digital_assets_minimum_coin_price',
	'class' => 'digital_assets_row',
	'digital_assets_custom_data' => 'custom',
	]
	);

	add_settings_field(
	'digital_assets_percent_fee',
	__( 'Percentage Fee', 'digital_assets' ),
	'digital_assets_percentage_fee',
	'digital_assets',
	'digital_assets_'.strtolower($coin['symbol']).'_coin',
	[
	'label_for' => 'digital_assets_percentage_fee',
	'class' => 'digital_assets_row',
	'digital_assets_custom_data' => 'custom',
	]
	);
	
	add_settings_field(
	'digital_assets_flat_fee',
	__( 'Flat Fee', 'digital_assets' ),
	'digital_assets_flat_fee',
	'digital_assets',
	'digital_assets_'.strtolower($coin['symbol']).'_coin',
	[
	'label_for' => 'digital_assets_flat_fee',
	'class' => 'digital_assets_row',
	'digital_assets_custom_data' => 'custom',
	]
	);
	$test = 'xlm';
	function digital_assets_xlm_coin_section( $args ) {
    ?>
    	<p>XLM Coin</p>
    <?php
    }
}
 

function digital_assets_general_section( $args ) {
?>
	<p>General</p>
<?php

}
 
function digital_assets_enabled( $args ) {

	$options = get_option( 'digital_assets_options' );
?>
	<select id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['digital_assets_custom_data'] ); ?>" name="digital_assets_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value="yes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'yes', false ) ) : ( '' ); ?>>
	<?php esc_html_e( 'yes', 'digital_assets' ); ?>
	</option>
	<option value="no" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no', false ) ) : ( '' ); ?>>
	<?php esc_html_e( 'no', 'digital_assets' ); ?>
	</option>
	</select>
<?php
}

function digital_assets_binance_key( $args ) {

	$key = get_option( 'digital_assets_binance_key' );
?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo $key; ?>" style="width: 600px;">
	</input>
<?php
}

function digital_assets_binance_secret( $args ) {

	$key = get_option( 'digital_assets_binance_secret' );
?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo $key; ?>" style="width: 600px;">
	</input>
<?php
}

function digital_assets_minimum_coin_price( $args ) {

	$options = get_option( 'digital_assets_minimum_coin_price' );
?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo $options; ?>">
	</input>
<?php
}

function digital_assets_percentage_fee( $args ) {

	$options = get_option( 'digital_assets_percentage_fee' );
?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo $options; ?>">
	</input>
<?php
}

function digital_assets_flat_fee( $args ) {

	$options = get_option( 'digital_assets_flat_fee' );
?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo $options; ?>">
	</input>
<?php
}

function digital_assets_options_page() {

	add_submenu_page( 
	'woocommerce',
	'Digital Assets',
	'',
	'manage_options',
	'digital_assets',
	'digital_assets_options_page_html'
	);
}
 
add_action( 'admin_menu', 'digital_assets_options_page' );
 
function digital_assets_options_page_html() {

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
 
if ( isset( $_GET['settings-updated'] ) ) {
	add_settings_error( 'digital_assets_messages', 'digital_assets_message', __( 'Settings Saved', 'digital_assets' ), 'updated' );
}
 
settings_errors( 'digital_assets_messages' );
?>
	<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="../../../wp-content/plugins/digital-assets/include/digitalassets/update-settings.php" method="post">
<?php
	settings_fields( 'digital_assets' );
	do_settings_sections( 'digital_assets' );
	submit_button( 'Save Settings' );
?>
	</form>
	</div>
<?php

} ?>
