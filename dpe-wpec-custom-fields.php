<?php
/*
Plugin Name: WPEC Custom Fields
Plugin URI: 
Description: Replaces WPEC's custom meta functionality with WordPress's default custom fields capabilities.
Version: 0.2
Author: David Paul Ellenwood
Author URI: http://www.dpedesign.com/
License: GPL2
*/

/*  Copyright 2011  David Paul Ellenwood  (email : david@dpedesign.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Add Wordpress default custom post meta to WPSC products
function dpe_add_custom_meta(){
	add_post_type_support( 'wpsc-product', 'custom-fields' );
}
add_action('init', 'dpe_add_custom_meta');

// Switches out the default WPSC Advanced meta box with a custom version
function dpe_switch_product_advanced_forms() {
	remove_meta_box( 'wpsc_product_advanced_forms', 'wpsc-product', 'normal' );
	add_meta_box( 'dpe_wpsc_product_advanced_forms', __('Advanced Settings', 'wpsc'), 'dpe_wpsc_product_advanced_forms', 'wpsc-product', 'normal', 'high' );
}
add_action( 'admin_head' , 'dpe_switch_product_advanced_forms' );

// Turn off the WPSC functionality for saving custom fields since we're using the default WP functionality.
function dpe_switch_submit_product() {
	remove_action( 'save_post', 'wpsc_admin_submit_product', 10, 2 );
	add_action( 'save_post', 'dpe_wpsc_admin_submit_product', 10, 2 );
}
add_action( 'admin_init' , 'dpe_switch_submit_product' );


// Copy of the WPSC advanced forms metabox code with the custom fields code removed.
function dpe_wpsc_product_advanced_forms() {
	global $post, $wpdb, $variations_processor, $wpsc_product_defaults;
	$product_data = get_post_custom( $post->ID );

	$product_data['meta'] = $product_meta = array();
	if ( !empty( $product_data['_wpsc_product_metadata'] ) )
		$product_data['meta'] = $product_meta = maybe_unserialize( $product_data['_wpsc_product_metadata'][0] );

	$custom_fields = $wpdb->get_results( "
		SELECT
			`meta_id`, `meta_key`, `meta_value`
		FROM
			`{$wpdb->postmeta}`
		WHERE
			`post_id` = {$post->ID}
		AND
			`meta_key` NOT LIKE '\_%'
		ORDER BY
			LOWER(meta_key)", ARRAY_A
	);
	if( !isset( $product_meta['engraved'] ) )
		$product_meta['engraved'] = '';

	if( !isset( $product_meta['can_have_uploaded_image'] ) )
		$product_meta['can_have_uploaded_image'] = '';

?>

        <table>
		<tr>
			<td class='itemfirstcol' colspan='2'><br /> <strong><?php _e( 'Merchant Notes:', 'wpsc' ); ?></strong><br />

			<textarea cols='40' rows='3' name='meta[_wpsc_product_metadata][merchant_notes]' id='merchant_notes'><?php 
				if ( isset( $product_meta['merchant_notes'] ) )
				echo stripslashes( trim( $product_meta['merchant_notes'] ) );
			?></textarea>
			<small><?php _e( 'These notes are only available here.', 'wpsc' ); ?></small>
		</td>
	</tr>
	<tr>
		<td class='itemfirstcol' colspan='2'><br />
			<strong><?php _e( 'Personalisation Options', 'wpsc' ); ?>:</strong><br />
			<input type='hidden' name='meta[_wpsc_product_metadata][engraved]' value='0' />
			<input type='checkbox' name='meta[_wpsc_product_metadata][engraved]' <?php echo ( ( $product_meta['engraved'] == true ) ? 'checked="checked"' : '' ); ?> id='add_engrave_text' />
			<label for='add_engrave_text'><?php _e( 'Users can personalize this Product by leaving a message on single product page', 'wpsc' ); ?></label>
			<br />
		</td>
	</tr>
	<tr>
		<td class='itemfirstcol' colspan='2'>
			<input type='hidden' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' value='0' />
			<input type='checkbox' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' <?php echo ( $product_meta['can_have_uploaded_image'] == true ) ? 'checked="checked"' : ''; ?> id='can_have_uploaded_image' />
			<label for='can_have_uploaded_image'> <?php _e( 'Users can upload images on single product page to purchase logs.', 'wpsc' ); ?> </label>
			<br />
		</td>
	</tr>
        <?php
	if ( get_option( 'payment_gateway' ) == 'google' ) {
?>
	<tr>
		<td class='itemfirstcol' colspan='2'>

			<input type='checkbox' <?php echo $product_meta['google_prohibited']; ?> name='meta[_wpsc_product_metadata][google_prohibited]' id='add_google_prohibited' /> <label for='add_google_prohibited'>
			<?php _e( 'Prohibited <a href="http://checkout.google.com/support/sell/bin/answer.py?answer=75724">by Google?</a>', 'wpsc' ); ?>
			</label><br />
		</td>
	</tr>
	<?php
	}
	do_action( 'wpsc_add_advanced_options', $post->ID );
?>
	<tr>
		<td class='itemfirstcol' colspan='2'><br />
			<strong><?php _e( 'Enable Comments', 'wpsc' ); ?>:</strong><br />
			<select name='meta[_wpsc_product_metadata][enable_comments]'>
				<option value='' <?php echo ( ( isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '' ) ? 'selected' : '' ); ?> ><?php _e( 'Use Default', 'wpsc' ); ?></option>
				<option value='1' <?php echo ( ( isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '1' ) ? 'selected' : '' ); ?> ><?php _e( 'Yes', 'wpsc' ); ?></option>
				<option value='0' <?php echo ( ( isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '0' ) ? 'selected' : '' ); ?> ><?php _e( 'No', 'wpsc' ); ?></option>
			</select>
			<br/><?php _e( 'Allow users to comment on this Product.', 'wpsc' ); ?>
		</td>
	</tr>
    </table>
<?php
}

// From ./wp-e-commerce/wp-admin/includes/product-functions.php
function dpe_wpsc_admin_submit_product( $post_ID, $post ) {
	global $current_screen, $wpdb;

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $current_screen ) || $current_screen->id != 'wpsc-product' || $post->post_type != 'wpsc-product' || empty( $_POST['meta'] ) )
		return $post_ID;

    //Type-casting ( not so much sanitization, which would be good to do )
    $post_data = $_POST;
    $product_id = $post_ID;
	$post_data['additional_description'] = isset($post_data['additional_description']) ? $post_data['additional_description'] : '';
    $post_meta['meta'] = (array)$_POST['meta'];
	if ( isset( $post_data['meta']['_wpsc_price'] ) )
		$post_data['meta']['_wpsc_price'] = abs( (float) str_replace( ',', '', $post_data['meta']['_wpsc_price'] ) );
	if ( isset( $post_data['meta']['_wpsc_special_price'] ) )
		$post_data['meta']['_wpsc_special_price'] = abs((float)str_replace( ',','',$post_data['meta']['_wpsc_special_price'] ));
	if($post_data['meta']['_wpsc_sku'] == __('N/A', 'wpsc'))
		$post_data['meta']['_wpsc_sku'] = '';
	if( isset( $post_data['meta']['_wpsc_is_donation'] ) )
		$post_data['meta']['_wpsc_is_donation'] = 1;
	else
		$post_data['meta']['_wpsc_is_donation'] = 0;
	if ( ! isset( $post_data['meta']['_wpsc_limited_stock'] ) ){
		$post_data['meta']['_wpsc_stock'] = false;
	} else {
		$post_data['meta']['_wpsc_stock'] = isset( $post_data['meta']['_wpsc_stock'] ) ? (int) $post_data['meta']['_wpsc_stock'] : 0;
	}

	unset($post_data['meta']['_wpsc_limited_stock']);
	if(!isset($post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'])) $post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'] = '';
        if(!isset($post_data['quantity_limited'])) $post_data['quantity_limited'] = '';
        if(!isset($post_data['special'])) $post_data['special'] = '';
        if(!isset($post_data['meta']['_wpsc_product_metadata']['no_shipping'])) $post_data['meta']['_wpsc_product_metadata']['no_shipping'] = '';
	
	$post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'];
	$post_data['meta']['_wpsc_product_metadata']['quantity_limited'] = (int)(bool)$post_data['quantity_limited'];
	$post_data['meta']['_wpsc_product_metadata']['special'] = (int)(bool)$post_data['special'];
	$post_data['meta']['_wpsc_product_metadata']['no_shipping'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['no_shipping'];
	
	// Product Weight
	if(!isset($post_data['meta']['_wpsc_product_metadata']['display_weight_as'])) $post_data['meta']['_wpsc_product_metadata']['display_weight_as'] = '';
        if(!isset($post_data['meta']['_wpsc_product_metadata']['display_weight_as'])) $post_data['meta']['_wpsc_product_metadata']['display_weight_as'] = '';
	
	$weight = wpsc_convert_weight($post_data['meta']['_wpsc_product_metadata']['weight'], $post_data['meta']['_wpsc_product_metadata']['weight_unit'], "pound", true);
	$post_data['meta']['_wpsc_product_metadata']['weight'] = (float)$weight;
        $post_data['meta']['_wpsc_product_metadata']['display_weight_as'] = $post_data['meta']['_wpsc_product_metadata']['weight_unit'];
	
	// table rate price
	$post_data['meta']['_wpsc_product_metadata']['table_rate_price'] = isset( $post_data['table_rate_price'] ) ? $post_data['table_rate_price'] : array();
	
	// if table_rate_price is unticked, wipe the table rate prices
	if ( empty( $post_data['table_rate_price']['state'] ) ) {
		$post_data['meta']['_wpsc_product_metadata']['table_rate_price']['table_price'] = array();
		$post_data['meta']['_wpsc_product_metadata']['table_rate_price']['quantity'] = array();
	}
	
	if ( ! empty( $post_data['meta']['_wpsc_product_metadata']['table_rate_price']['table_price'] ) ) {
		foreach ( (array) $post_data['meta']['_wpsc_product_metadata']['table_rate_price']['table_price'] as $key => $value ){
			if(empty($value)){
				unset($post_data['meta']['_wpsc_product_metadata']['table_rate_price']['table_price'][$key]); 
				unset($post_data['meta']['_wpsc_product_metadata']['table_rate_price']['quantity'][$key]); 
			} 
		}
	}

   
	$post_data['meta']['_wpsc_product_metadata']['shipping']['local'] = (float)$post_data['meta']['_wpsc_product_metadata']['shipping']['local'];
	$post_data['meta']['_wpsc_product_metadata']['shipping']['international'] = (float)$post_data['meta']['_wpsc_product_metadata']['shipping']['international'];
	
	
	// Advanced Options
	$post_data['meta']['_wpsc_product_metadata']['engraved'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['engraved'];	
	$post_data['meta']['_wpsc_product_metadata']['can_have_uploaded_image'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['can_have_uploaded_image'];
	if(!isset($post_data['meta']['_wpsc_product_metadata']['google_prohibited'])) $post_data['meta']['_wpsc_product_metadata']['google_prohibited'] = '';
	$post_data['meta']['_wpsc_product_metadata']['google_prohibited'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['google_prohibited'];
	$post_data['meta']['_wpsc_product_metadata']['external_link'] = (string)$post_data['meta']['_wpsc_product_metadata']['external_link'];
	$post_data['meta']['_wpsc_product_metadata']['external_link_text'] = (string)$post_data['meta']['_wpsc_product_metadata']['external_link_text'];
	$post_data['meta']['_wpsc_product_metadata']['external_link_target'] = (string)$post_data['meta']['_wpsc_product_metadata']['external_link_target'];
	
	$post_data['meta']['_wpsc_product_metadata']['enable_comments'] = $post_data['meta']['_wpsc_product_metadata']['enable_comments'];
	$post_data['meta']['_wpsc_product_metadata']['merchant_notes'] = $post_data['meta']['_wpsc_product_metadata']['merchant_notes'];
	
	$post_data['files'] = $_FILES;

	if(isset($post_data['post_title']) && $post_data['post_title'] != '') {

	$product_columns = array(
		'name' => '',
		'description' => '',
		'additional_description' => '',
		'price' => null,
		'weight' => null,
		'weight_unit' => '',
		'pnp' => null,
		'international_pnp' => null,
		'file' => null,
		'image' => '0',
		'quantity_limited' => '',
		'quantity' => null,
		'special' => null,
		'special_price' => null,
		'display_frontpage' => null,
		'notax' => null,
		'publish' => null,
		'active' => null,
		'donation' => null,
		'no_shipping' => null,
		'thumbnail_image' => null,
		'thumbnail_state' => null
	);

	foreach($product_columns as $column => $default)
	{
		if (!isset($post_data[$column])) $post_data[$column] = '';

		if($post_data[$column] !== null) {
			$update_values[$column] = stripslashes($post_data[$column]);
		} else if(($update != true) && ($default !== null)) {
			$update_values[$column] = stripslashes($default);
		}
	}
	// if we succeed, we can do further editing (todo - if_wp_error)
	
	// if we have no categories selected, assign one.
	if( isset( $post_data['tax_input']['wpsc_product_category'] ) && count( $post_data['tax_input']['wpsc_product_category'] ) == 1 && $post_data['tax_input']['wpsc_product_category'][0] == 0){
		$post_data['tax_input']['wpsc_product_category'][1] = wpsc_add_product_category_default($product_id);
	
	}
	// and the meta * Customized by DPE *
	dpe_wpsc_update_product_meta($product_id, $post_data['meta']);

	// and the custom meta
	wpsc_update_custom_meta($product_id, $post_data);

	// sort out the variations
	wpsc_edit_product_variations( $product_id, $post_data );

	//and the alt currency
	if ( ! empty( $post_data['newCurrency'] ) ) {
		foreach( (array) $post_data['newCurrency'] as $key =>$value ){
			wpsc_update_alt_product_currency( $product_id, $value, $post_data['newCurrPrice'][$key] );
		}
	}

	if($post_data['files']['file']['tmp_name'] != '') {
		wpsc_item_process_file($product_id, $post_data['files']['file']);
	} else {
		if (!isset($post_data['select_product_file'])) $post_data['select_product_file'] = null;
	  	wpsc_item_reassign_file($product_id, $post_data['select_product_file']);
	}

	if(isset($post_data['files']['preview_file']['tmp_name']) && ($post_data['files']['preview_file']['tmp_name'] != '')) {
 		wpsc_item_add_preview_file($product_id, $post_data['files']['preview_file']);
	}
	do_action('wpsc_edit_product', $product_id);
	wpsc_ping();
	}
	return $product_id;
}

// Check if this is a purely numeric meta key (as WP defaults to) and if so, don't add it.
// This prevents the custom fields from being double entered.
function dpe_wpsc_update_product_meta($product_id, $product_meta) {
    if($product_meta != null) {
		foreach((array)$product_meta as $key => $value) {
			if( !is_numeric( $key ) ) {
				update_post_meta($product_id, $key, $value);
			}
		}
	}
}

// Add a quick style fix to set the correct size for the custom fields' textarea
function dpe_wpec_cf_admin_styles() {
	global $post_type;
	if ( ( array_key_exists( 'post_type', $_GET ) && ($_GET['post_type'] == 'wpsc-product') ) || ($post_type == 'wpsc-product')) {	
		$plugin_url = plugins_url( 'admin-styles.css', __FILE__ );
		wp_enqueue_style( 'dpe-wpec-custom-meta', $plugin_url );
	}
}
add_action( 'admin_print_styles', 'dpe_wpec_cf_admin_styles' );



?>