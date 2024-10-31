<?php
/*
Plugin Name: SEPA Direct Debit for Woocommerce
Plugin URI: http://vansteinengroentjes.nl
Description: payIBAN Payment gateway for WooCommerce SEPA direct debit payments in more than 33 countries.
Version: 4.2.3
Author: van Stein en Groentjes
Author URI: https://vansteinengroentjes.nl
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



load_plugin_textdomain( 'woocommerce-payiban-free', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' ); 




/**
 * Adds a box to the main column on the Post and Page edit screens to let the user select the recurrent period
 */
add_action( 'add_meta_boxes', 'payibanfree_product_period_mb' );
function payibanfree_product_period_mb() {
    add_meta_box(
        'wc_product_period_mb',
        __( 'PayIBAN: Recurring payment option.', 'woocommerce-payiban-free' ),
        'payibanfree_product_period_inner_mb',
        'product',
        'normal',
        'high'
    );
}


function payibanfree_change_cart($price,$cart_item,$cart_item_key) {
	$_productID= $cart_item['data']->get_id(); 
	$_real_price = $cart_item['data']->get_price();
	$all_values = array("eenmalig"=>""
					,"dag" => __("per day", 'woocommerce-payiban-free')
					,"week" => __("per week", 'woocommerce-payiban-free')
					,"maand" => __("per month", 'woocommerce-payiban-free')
					,"twee maanden" => __("bimonthly", 'woocommerce-payiban-free')
					,"kwartaal" => __("quarterly", 'woocommerce-payiban-free')
					,"halfjaar" => __("semi-annualy", 'woocommerce-payiban-free')
					,"jaar" => __("annualy", 'woocommerce-payiban-free')
					);
	$period_number = get_post_meta($_productID, "product_numberofperiod", true);
	
	$price_ori = $_real_price;
	$initial_price = "";
	
	$perc = get_post_meta( $_productID, 'product_discountperc', true );
	$fixed = get_post_meta( $_productID, 'product_pricefixed', true );
	$price = $_real_price;
	if ($perc != "" && $perc > 0 && $perc <= 100){
		$price = $price * (1.0- $perc/100.0);
	}if ($fixed != "" && $fixed > 0 ){
		$price = $fixed;
	}
	if ($price != $_real_price){
		$initial_price = " (first term)";
	}
	$price = wc_price($price);
	


	if ($period_number > 0){
	  echo $price.$initial_price.'
			'.$all_values[get_post_meta($_productID, "product_period", true)].'
			';
	}else{
		echo $price.$initial_price;
	}
}
add_action( 'woocommerce_cart_item_price', 'payibanfree_change_cart', 1, 3 );


function payibanfree_change_summary() {
	$_productID= get_the_ID();  
	$all_values = array("eenmalig"=>""
					,"dag" => __("per day", 'woocommerce-payiban-free')
					,"week" => __("per week", 'woocommerce-payiban-free')
					,"maand" => __("per month", 'woocommerce-payiban-free')
					,"twee maanden" => __("per two months", 'woocommerce-payiban-free')
					,"kwartaal" => __("per quarter", 'woocommerce-payiban-free')
					,"halfjaar" => __("per six months", 'woocommerce-payiban-free')
					,"jaar" => __("per year", 'woocommerce-payiban-free')
					);

	$period_number = get_post_meta($_productID, "product_numberofperiod", true);
	if ($period_number > 0){

		$initial_price = "";

		$perc = get_post_meta( $_productID, 'product_discountperc', true );
    	$fixed = get_post_meta( $_productID, 'product_pricefixed', true );
    	if ($perc > 0 && $perc <= 100){
    		$initial_price = __(" first term with ","machtiging-free-payiban").$perc.__("% discount.","woocommerce-payiban-free");
    	}
    	if ($fixed > 0 ){
    		$initial_price = __(" first term price: ","machtiging-free-payiban").wc_price($fixed).__(".","woocommerce-payiban-free");
    	}
	
					
		echo '
		<div class="product-period">
			'.$all_values[get_post_meta($_productID, "product_period", true)];
			if ($period_number != "" && $period_number > 0){
				echo ' ('.$period_number.' times)'.$initial_price;
			}
			echo '
		</div>
		';
	}else{
		echo '
		<div class="product-period">'.$initial_price.'
		</div>
		';
	}

}
add_action( 'woocommerce_single_product_summary', 'payibanfree_change_summary', 10 );

function payibanfree_change_summary_checkout(){
  global $woocommerce;
  $cart = WC()->cart;
  $price_fixed = 0;
  $total_price = $cart->get_total();
  foreach ( $cart->cart_contents as $product ) {
		$value3 = get_post_meta( $product["product_id"], 'product_discountperc', true );
		$value4 = get_post_meta( $product["product_id"], 'product_pricefixed', true );
		if ($value3 > 0 && $value3 <= 100){
			$discount_fixed += $product["data"]->get_price() * ($value3/100.0);
		}
		if ($value4 != ""){
			$price_fixed = $value4;
		}
        
    }
    if ($price_fixed > 0){
		echo __("The first term price will be ","woocommerce-payiban-free").wc_price( $price_fixed ).".";
    }
    elseif ($discount_fixed>0){
    	echo __("A first term discount will be applied of ","woocommerce-payiban-free").wc_price( -$discount_fixed ).".";
    }
 }

add_action( 'woocommerce_review_order_before_payment', 'payibanfree_change_summary_checkout', 1 );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function payibanfree_product_period_inner_mb( $post ) {

	  // Add an nonce field so we can check for it later.
	  wp_nonce_field( plugin_basename( __FILE__ ), 'wc_product_period_inner_mb_nonce' ); 
	  
	  /*
	   * Use get_post_meta() to retrieve an existing value
	   * from the database and use the value for the form.
	   */
	  $value = get_post_meta( $post->ID, 'product_period', true );

	  $value2 = get_post_meta( $post->ID, 'product_numberofperiod', true );

	  $value3 = get_post_meta( $post->ID, 'product_discountperc', true );

      $value4 = get_post_meta( $post->ID, 'product_pricefixed', true );
	  if ($value4 == ""){
      	$value4 = 0;
      }
	

		/*	‘eenmalig’ = one off
		‘maand’ = monthly
		‘twee maanden’ = two monthly’
		‘kwartaal’ = quarterly
		‘halfjaar’ = every six months
		‘jaar’ = yearly*/
	  $all_values = array("eenmalig","dag","week","maand","twee maanden","kwartaal","halfjaar","jaar");
	  $all_names = array(__("one off", 'woocommerce-payiban-free'),__("every day", 'woocommerce-payiban-free'),__("every week", 'woocommerce-payiban-free'),__("every month", 'woocommerce-payiban-free'),__("two monthly", 'woocommerce-payiban-free'),__("quarterly", 'woocommerce-payiban-free'),__("every six months", 'woocommerce-payiban-free'),__("yearly", 'woocommerce-payiban-free'));

	  echo '<p>'.__( 'Specify the billing period of this product. You can use this option to use periodic billing by PayIBAN without the need of the plugin: Woocommerce Subscriptions.', 'woocommerce-payiban-free' ).' </p>';
	  echo '<label for="product_period_field">';
	  echo __( "Recurring period:", 'woocommerce-payiban-free' );
	  echo '</label> ';
	  echo '<select id="product_period_field"  name="product_period_field">';
	  for ($i=0;$i<count($all_values);$i++){
	  	if ($value == $all_values[$i]){
	  		echo '<option value="'.$all_values[$i].'" selected>'.$all_names[$i].'</option>';
	  	}else{
	  		echo '<option value="'.$all_values[$i].'">'.$all_names[$i].'</option>';
	  	}
			  
	  }
	  			
	  echo '</select>';

	  echo '<p>'.__( 'Specify the number of terms that will be billed, put 0 for infinite.', 'woocommerce-payiban-free' ).' </p>';
	  echo '<label for="product_numberofperiod_field">';
	  echo __( "Number of periods:", 'woocommerce-payiban-free' );
	  echo '</label> ';
	  echo '<input type="text" value="'.$value2.'" id="product_numberofperiod_field"  name="product_numberofperiod_field">';
	  


	  echo '<p>'.__( 'Specify an optional discount for the first term in percentage or set a fixed price for the first period (only valid for PayIBAN payment gateway). Leave at 0 or -1 if you do not want to use this function, specifying both results in percentage having precedence over the fixed amount.', 'woocommerce-payiban-free' ).' </p>';
	  echo '<label for="product_discountperc_field">';
	  echo __( "First term discount in %:", 'machtiging-free-payiban' );
	  echo '</label> ';
	  echo '<input type="number" value="'.$value3.'" id="product_discountperc_field"  name="product_discountperc_field">';
	  echo '<br/><label for="product_discountperc_field">';
	  echo __( "Fixed first term price:", 'machtiging-free-payiban' );
	  echo '</label> ';
	  echo '<input type="number" value="'.$value4.'" id="product_pricefixed_field"  name="product_pricefixed_field">';
}//function



/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function payibanfree_product_period_save_postdata( $post_id ) {
  /*
   * We need to verify this came from the our screen and with proper authorization,
   * because save_post can be triggered at other times.
   */
  // Check if our nonce is set.
  if ( ! isset( $_POST['wc_product_period_inner_mb_nonce'] ) )
    return;
  $nonce = $_POST['wc_product_period_inner_mb_nonce'];
  // Verify that the nonce is valid.
  if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) )
      return;
  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;
  // Check the user's permissions.
  if ( 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }
  /* OK, its safe for us to save the data now. */
  // Sanitize user input.
  $mydata = sanitize_text_field( $_POST['product_period_field'] );
  // Update the meta field in the database.
  update_post_meta( $post_id, 'product_period', $mydata );

  $mydata = sanitize_text_field( $_POST['product_numberofperiod_field'] );
  // Update the meta field in the database.
  update_post_meta( $post_id, 'product_numberofperiod', $mydata );

  $mydata = intval(sanitize_text_field( $_POST['product_discountperc_field'] ));
  update_post_meta( $post_id, 'product_discountperc', $mydata );

  $mydata = intval(sanitize_text_field( $_POST['product_discountfixed_field'] ));
  update_post_meta( $post_id, 'product_discountfixed', $mydata );

  $mydata = intval(sanitize_text_field( $_POST['product_pricefixed_field'] ));
  update_post_meta( $post_id, 'product_pricefixed', $mydata );
}
add_action( 'save_post', 'payibanfree_product_period_save_postdata' );



/**
 * Check if WooCommerce is active
 **/
if ( true || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	
	/**
	* Add welcome screen
	**/
	add_action('admin_menu', 'payibanfree_welcome_screen_page');
	function payibanfree_welcome_screen_page(){
		add_dashboard_page('Welcome', 'Welcome', 'read', 'payibanfree-plugin-welcome', 'payibanfree_welcome_page');
	}
	function payibanfree_welcome_page(){
		echo '<iframe src="'.__("https://www.payiban.com/welcome/",'woocommerce-payiban-free' ).'" style="position: absolute; height: 1000px; width:100%; border: none"></iframe>';
	}
	add_action('activated_plugin','payibanfree_welcome_redirect');
	function payibanfree_welcome_redirect($plugin)
	{
		if($plugin=='payiban-sepa-direct-debit-for-woocommerce/woocommerce-payIBAN-free.php') {
			//create new account

			wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_payibanfree'));
			die();
		}
	}



	add_action( 'admin_head', 'payibanfree_remove_menu_entry' );
	function payibanfree_remove_menu_entry(){
		remove_submenu_page( 'index.php', 'payibanfree-plugin-welcome' );
	}

	add_action( 'plugins_loaded', 'woocommerce_payibanfree_init', 0 );
	function woocommerce_payibanfree_init() {

		
		if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
		if ( !function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH.'/wp-admin/includes/plugin.php';
		}
		
		//functions to check iban account numbers
		require_once 'checkiban.php';

		
		
		class WC_payIBANfree extends WC_Payment_Gateway{

			public function __construct() {
				global $woocommerce;


				$this -> id = 'payibanfree';
				$this -> method_title = 'PayIBAN';
				$this -> icon = plugins_url( plugin_basename( dirname( __FILE__ ) ).'/payibanlogo.png', dirname( __FILE__ ) );
				$this -> has_fields = true;

				$this -> init_form_fields();
				$this -> init_settings();

				$this -> title = $this -> settings['title'];
				$this -> description = $this -> settings['description'];

				$this -> test_mode = $this -> settings['test_mode'];
				$this -> merchant_id = $this -> settings['merchant_id'];
				$this -> text_language = $this -> settings['text_language'];
				$this -> merchant_pass = $this -> settings['merchant_pass'];
				$this -> redirect_page_id = $this -> settings['redirect_page_id'];
				$this -> liveurl = 'not needed';
				$this -> merchant_mail = $this -> settings['merchant_mail'];
				$this -> generalterm = $this -> settings['generalterm'];

				

				$this -> msg['message'] = '';
				$this -> msg['class'] = '';

				if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
					add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
				} else {
					add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
				}



				
			}

			/*Function to set the options for the admin, generates the fields for the form*/
			function init_form_fields() {
			
				$this -> form_fields = array(
					
					'enabled' => array(
						'title' => __( 'Enable/Disable', 'woocommerce-payiban-free' ),
						'type' => 'checkbox',
						'label' => __( 'Enable payIBAN Payment Module.', 'woocommerce-payiban-free' ),
						'default' => 'no' ),
					'test_mode' => array(
						'title' => __( 'Test mode', 'woocommerce-payiban-free' ),
						'type' => 'checkbox',
						'label' => __( 'In the test mode, payments are not processed.', 'woocommerce-payiban-free' ),
						'default' => 'yes' ),
					'title' => array(
						'title' => __( 'Title:', 'woocommerce-payiban-free' ),
						'type'=> 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-payiban-free' ),
						'default' => __( 'SEPA Direct Debit processed by', 'woocommerce-payiban-free' ) ),
					'description' => array(
						'title' => __( 'Description:', 'woocommerce-payiban-free' ),
						'type' => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-payiban-free' ),
						'default' => __( 'Pay securely by using your IBAN bank account with PayIBAN.', 'woocommerce-payiban-free' ) ),
					'generalterm' => array(
						'title' => __( 'General terms:', 'woocommerce-payiban-free' ),
						'type' => 'textarea',
						'description' => __( 'This controls the general term message which the user sees during checkout.', 'woocommerce-payiban-free' ),
						'default' => __( 'By signing this (recurring) mandate form, you authorise (merchant) to send instructions.', 'woocommerce-payiban-free' ) ),
					'merchant_id' => array(
						'title' => __( 'Username', 'woocommerce-payiban-free' ),
						'type' => 'text',
						'description' => __( 'Check username and password within section API and plugin of your account.  <a href="https://www.payiban.com/nl/aanmelden/">Click to register your free account.</a>', 'woocommerce-payiban-free' ) ),
					'merchant_pass' => array(
						'title' => __( 'Password', 'woocommerce-payiban-free' ),
						'type' => 'text',
						'description' =>  __( 'API password given to you by payIBAN.<br/>Need support: <a href="mailto:support@payiban.com">support@payiban.com</a>.', 'woocommerce-payiban-free' ) ),
					'merchant_mail' => array(
						'title' => __( 'Admin Email', 'woocommerce-payiban-free' ),
						'type' => 'text',
						'description' =>  __( '!Important: Email adress for notices about cancelled subscriptions', 'woocommerce-payiban-free' ),
						'default' => get_option( 'admin_email' ) ),
					'text_language' => array(
						'title' => __( 'Default text language', 'woocommerce-payiban-free' ),
						'type' => 'text',
						'description' =>  __( 'Language of the TAN-code text message. Two letters like "nl", "en", "es" etc.', 'woocommerce-payiban-free' ),
						'default' => 'nl' ),
					'redirect_page_id' => array(
						'title' => __( 'Return Page' ),
						'type' => 'select',
						'options' => $this -> get_pages( 'Select Page' ),
						'description' => __( 'URL of success page', 'woocommerce-payiban-free' ),
					)
				);
			}

			public function create_url( $args ) {
		        $base_url = 'https://www.payiban.com/nl/';

		        $base_url = add_query_arg( 'wc-api', 'software-api', $base_url );
		        return $base_url . '&' . http_build_query( $args );
			}

			public function admin_options() {
			    
		        // check user capabilities
		        if (!current_user_can('manage_options')) {
		            return;
		        }
		       

				echo '<h3>'.__( 'PayIBAN Payment Gateway', 'woocommerce-payiban-free' ).'</h3>';
				echo '<p>'.__( 'PayIBAN plugin made by Bas van Stein, van Stein en Groentjes', 'woocommerce-payiban-free' ).'</p>';
				
				echo '<table class="form-table">';
				// Generate the HTML For the settings form.

				$this -> generate_settings_html();
				
				echo '</table>';
			}

			/**
			 * Form that the user sees on checkout. Handles filling in data, requesting TAN code and final payment registration.
			 * */
			function payment_fields() {
				echo wpautop( wptexturize( $this -> description ) );
				global $woocommerce;

				$redirect_url = ( $this -> redirect_page_id=='' || $this -> redirect_page_id==0 )?get_site_url() . '/':get_permalink( $this -> redirect_page_id );
				$user_id = get_current_user_id();

				
				echo '
					<h5>'.__( '1. IBAN and mobile.', 'woocommerce-payiban-free' ).' </h5>


				   
				    <input name="iban" id="iban" type="text" value="" placeholder="'.__( 'IBAN number', 'woocommerce-payiban-free' ).'"/>

				    <input name="mobilephone" id="mobilephone" type="text" value="" placeholder="'.__( 'Mobile number', 'woocommerce-payiban-free' ).'"/>';

				    /*<label for="geslacht">'.__( 'Account name:', 'woocommerce-payiban-free' ).'*</label><br/>
				    <select name="geslacht" >
				        <option value="man">'.__( 'Mr.', 'woocommerce-payiban-free' ).'</option>
				        <option value="vrouw">'.__( 'Ms.', 'woocommerce-payiban-free' ).'</option>
				    </select>
				    <input name="voorletters" id="payIBAN_inititials" type="text"  placeholder="'.__( 'Initials', 'woocommerce-payiban-free' ).'"/>
				    <input name="tussenvoegsel" id="payIBAN_preposition" type="text" placeholder="'.__( 'Preposition', 'woocommerce-payiban-free' ).'" />
				    <input name="achternaam" id="payIBAN_familyname" type="text"  placeholder="'.__( 'Family name', 'woocommerce-payiban-free' ).'"/>*/
				 echo '
				 	<hr>
				    <h5>'.__( '2. Request your TAN-code', 'woocommerce-payiban-free' ).'</h5><br/>
				    <input type="submit" name="tancode_aanvraag" value="'.__( 'Request TAN code', 'woocommerce-payiban-free' ).'"><br/>
				     <hr>
				    <h5>'.__( '3. Fill in your TAN-code.', 'woocommerce-payiban-free' ).'</h5>
				  
				    
				    <input name="tancode" type="text" id="payIBAN_TANcode" placeholder="TAN-code"/>';
					echo '<p><br/>'.$this -> generalterm.'</p>';

			}

			/**
			 * FUNCTION TO CHECK THE FORM ABOVE
			 */
			function validate_fields() {
				global $woocommerce;
				//Requested TAN Code

				if ( $_POST['tancode'] == '' || isset( $_POST['tancode_aanvraag'] ) ) {
					$api_mobielnummer = sanitize_text_field($_POST['mobilephone']);

					if ( $api_mobielnummer == '' ) {
						wc_add_notice( __('Error! For receiving the PayIBAN TAN code, a valid number is required (0612345678).', 'woocommerce-payiban' ) ,'error');
					
						return false;
					}

					if ( strpos( $api_mobielnummer, '06' ) === 0 ) {
						$api_mobielnummer = preg_replace( '/06/', '316', $api_mobielnummer, 1 );
					}


					$language = $this ->text_language;

					$api_username = $this ->merchant_id;
					$api_password = $this ->merchant_pass;
					$api_test = 'nee';
					if ( $this->test_mode == 'no' ) {
						$api_test = 'nee';
					}else {
						$api_test = 'ja';
					}
					//SEND TAN CODE
					try {
						$url = 'https://api.payiban.com/webservice/index.php'; // basis url van elke xmlstring request
						$xml = '<?xml version="1.0" encoding="UTF-8"?> <webservice> <user>'.$api_username.'</user> <password>'.$api_password.'</password> <request>sms_tancode</request> <test>'.$api_test.'</test> <output>xml</output> <data> <item> <taalcode>'.$language.'</taalcode> <mobielnummer>'.$api_mobielnummer.'</mobielnummer> </item> </data> </webservice>';

						
						$xmlresponse = file_get_contents( $url.'?xmlstring='.urlencode( $xml ) );

						//de response verwerken
						print_r($xmlresponse);
						//@todo@ split on nothing for you;
						$result = simplexml_load_string( $xmlresponse );
						$error_message=$result->error;
						if ( $error_message != '' ) {
							wc_add_notice( __( 'ERROR! ', 'woocommerce-payiban-free' ) . $error_message  ,'error');
						}else {
							wc_add_notice( __( 'Go to step 3 and enter your TAN code. ', 'woocommerce-payiban-free' ) ,'error');
						}
					}catch (Exception $e) {
						wc_add_notice( __( 'Caught exception: ', 'woocommerce-payiban-free' ).$e->getMessage() ,'error');
						
					}
					return false;
				}else {
					if ( $_POST['iban'] == '' || $_POST['billing_first_name']=='' ||  $_POST['billing_last_name']=='' ) {
						wc_add_notice( __( 'ERROR! Please fill in all fields. ', 'woocommerce-payiban-free' ) ,'error');
						return false;
					}
					if ( $this->Proef11( $_POST['iban'] ) == false && verify_iban($_POST['iban']) == false) {
						wc_add_notice( __( 'ERROR! The given IBAN account number is not valid. ', 'woocommerce-payiban-free' ) ,'error');
						return false;
					}

					return true;
				}
			}

			/**
			 * Process the payment and return the result
			 * */
			function process_payment( $order_id ) {
				global $woocommerce;

				$order = new WC_Order( $order_id );

				// API account settings
				$api_username = $this ->merchant_id;
				$api_password = $this ->merchant_pass;
				$api_test = 'nee';
				if ( $this->test_mode == 'no' ) {
					$api_test = 'nee';
				}else {
					$api_test = 'ja';
				}
				$api_aantal_termen = 0;

				if (!is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) 
					|| WC_Subscriptions_Order::order_contains_subscription( $order_id ) === false) {

					
					
					$price = $order->get_total();

					//check cart
					$period_check = "";
					$period_number = 0;
					$first_discount_perc = 0;
					$product_pricefixed = -1;
					foreach ( $woocommerce->cart->cart_contents as $product ) {
						$temp_p = get_post_meta($product['product_id'], "product_period", true);
						$temp_n = get_post_meta($product['product_id'], "product_numberofperiod", true);
						
						$value3 = get_post_meta( $product['product_id'], 'product_discountperc', true );
      					$value4 = get_post_meta( $product['product_id'], 'product_pricefixed', true );

						if ($period_check == ""){
							$period_check = $temp_p;
							$period_number = $temp_n;
						}
						if ($first_discount_perc == 0 || $first_discount_perc==""){
							$first_discount_perc = $value3;
							if ($value4 != ""){
								$product_pricefixed = $value4;
							}
						}
			            if ($period_check != $temp_p || $period_number != $temp_n){
			            	wc_add_notice( __( 'Only one type of recurring product can be bought at once. Please remove some of the items from the cart.', 'woocommerce-payiban-free' ) ,'error');
							return;
			            }


			        }
			        

					//check special field
					$api_periode = $period_check;
					if (intval($period_number) > 0){
						$api_aantal_termen = $period_number;
					}
					$all_values = array("eenmalig","dag","week","maand","twee maanden","kwartaal","halfjaar","jaar");
					if (! in_array($api_periode, $all_values)) {
						//One time payment
						$api_periode = 'eenmalig';
					}
					

				} else {


					if (WC_Subscriptions_Order::get_subscription_trial_length( $order ) > 0){
						wc_add_notice( __( 'ERROR! payIBAN does not support trial periods or signup fees. ', 'woocommerce-payiban-free' ) ,'error');
						
						return;
					}

					$periodname = WC_Subscriptions_Order::get_subscription_period( $order );

					$price = WC_Subscriptions_Order::get_price_per_period( $order );
					if (WC_Subscriptions_Order::get_total_initial_payment( $order ) != $price){
						wc_add_notice( __( 'ERROR! payIBAN does not support trial periods or signup fees. ', 'woocommerce-payiban-free' ) ,'error');
						return;
					}
					$period = WC_Subscriptions_Order::get_subscription_interval( $order );

					if ( strtolower( $periodname ) == 'day' && $period!=1 ) {
						wc_add_notice( __( 'ERROR! payIBAN only supports per day, per week, per month, per two / three months, per six months and per year. ', 'woocommerce-payiban-free' ) ,'error');
						return false;
					}
					if ( strtolower( $periodname ) == 'week' && $period!=1 ) {
						wc_add_notice( __( 'ERROR! payIBAN only supports per day, per week, per month, per two / three months, per six months and per year. ', 'woocommerce-payiban-free' ) ,'error');
						return false;
					}
					if ( strtolower( $periodname ) == 'year' && $period==1 ) {
						$period = 12;
					}

					if ( strtolower( $periodname ) == 'day' && $period==1 ) {
						$api_periode = 'dag';
					}
				    else if ( strtolower( $periodname ) == 'week' && $period==1 ) {
						$api_periode = 'week';
					}else if ( $period==1 ) {
						$api_periode = 'maand';
					}else if ( $period==2 ) {
						$api_periode = 'twee maanden';
					}else if ( $period==3 ) {
						$api_periode = 'kwartaal';
					}else if ( $period==6 ) {
						$api_periode = 'halfjaar';
					}else if ( $period==12 ) {
						$api_periode = 'jaar';
					}else {
						wc_add_notice( __( 'ERROR! payIBAN only supports per day, per week, per month, per two / three months, per six months and per year. ', 'woocommerce-payiban-free' ) ,'error');
						return false;
					}
					
				}

				// Het bedrag van de machtiging
				$api_bedrag = $price;

				$api_first_bedrag = $price;
				if ($first_discount_perc > 0 && $first_discount_perc <= 100){
					$api_first_bedrag = $api_first_bedrag * (1.0- $first_discount_perc/100.0);
				}if ($product_pricefixed > 0 ){
					$api_first_bedrag = $product_pricefixed;
				}

				$year = date( 'Y' );
				$month = date( 'm' );
				$monthint = intval( date( 'n' ) );
				$datum = date( 'Y-m-d' );
				$day = intval( date( 'j' ) );

				//get voorletters from first_name
				$words = preg_split("/[\s,_-]+/", sanitize_text_field($_POST['billing_first_name']));
				$acronym = "";

				foreach ($words as $w) {
					$acronym .= $w[0]. ".";
				}
				$acronym = trim($acronym);


				// Posts vanuit het formulier
				$api_tancode        = sanitize_text_field($_POST['tancode']);
				$api_toevoegdatum   = $datum;
				$api_geslacht       = "man"; //$_POST['geslacht'];
				$api_voorletters    = $acronym; //sanitize_text_field($_POST['billing_first_name']);
				$api_tussenvoegsel  = ""; //$_POST['tussenvoegsel'];
				$api_achternaam     = sanitize_text_field($_POST['billing_last_name']);
				$api_straat         = sanitize_text_field($_POST['billing_address_1']);
				$api_postcode       = sanitize_text_field($_POST['billing_postcode']);
				$api_woonplaats     = sanitize_text_field($_POST['billing_city']);
				$api_emailadres     = sanitize_email($_POST['billing_email']);
				$api_iban           = sanitize_text_field($_POST['iban']);
				$api_mobielnummer   = sanitize_text_field($_POST['mobilephone']);
				$api_referentie     = 'payIBAN order '.$order_id;


				// TOEVOEGEN van een nieuwe machtiging
				$url = 'https://api.payiban.com/webservice/index.php'; // Basis url van elke xmlstring request
				$xml = '
				<?xml version="1.0" encoding="UTF-8"?>
				<webservice>
				    <user>'.$api_username.'</user>
				    <password>'.$api_password.'</password>
				    <request>import_machtiging</request>
				    <test>'.$api_test.'</test>
				    <data>
				        <item>
				            <tancode>'.$api_tancode.'</tancode>
				            <periode>'.$api_periode.'</periode>
				            <toevoegdatum>'.$api_toevoegdatum.'</toevoegdatum>
				            <bedrag>'.$api_first_bedrag.'</bedrag>
            				<std_bedrag>'.$api_bedrag.'</std_bedrag>';

				if ($api_aantal_termen > 0){
					$xml .= '<termijnbetaling>true</termijnbetaling>
							<aantaltermijnen>'.$api_aantal_termen.'</aantaltermijnen>
							<bedragdelendoortermijnen>false</bedragdelendoortermijnen>
							<frequency>'.$api_periode.'</frequency>';
				}
				$xml .= '     <bedrijfsnaam></bedrijfsnaam>
				            <geslacht>'.$api_geslacht.'</geslacht>
				            <voorletters>'.$api_voorletters.'</voorletters>
				            <tussenvoegsel>'.$api_tussenvoegsel.'</tussenvoegsel>
				            <achternaam>'.$api_achternaam.'</achternaam>
				            <straat>'.$api_straat.'</straat>
				            <postcode>'.$api_postcode.'</postcode>
				            <woonplaats>'.$api_woonplaats.'</woonplaats>
				            <emailadres>'.$api_emailadres.'</emailadres>
				            <iban>'.$api_iban.'</iban>
				            <bic></bic>
				            <mobielnummer>'.$api_mobielnummer.'</mobielnummer>
				            <referentie>'.$api_referentie.'</referentie>
				        </item>
				    </data>
				</webservice>';


				$xmlresponse = file_get_contents( $url.'?xmlstring='.urlencode( $xml ));

				$result = simplexml_load_string( $xmlresponse );


				if ( $result->error != '' ) {
					wc_add_notice( __( 'Payment error:', 'woocommerce-payiban-free' ) . $result->error  ,'error');
					return false;
				}else {
					$mandaatid = (string)$result->data->item->mandateid;
					//update the order meta data
					update_post_meta( $order_id, 'Mandaatid', $mandaatid);

					//get other data from api

					$api_username = $this ->merchant_id;
					$api_password = $this ->merchant_pass;
					//here we can get the other information from the API
					$url = 'https://api.payiban.com/webservice/index.php?user='.$api_username.'&password='.$api_password.'&request=machtiging&kolom=mandateid&waarde='.$mandaatid; // basis url van elke xmlstring request
				


					$xmlresponse = file_get_contents( $url );
					//de response verwerken
					$result = simplexml_load_string( $xmlresponse );
					//print_r($result);
					$error_message=$result->error;
					if ($result && $result->machtiging && $result->machtiging->iban != ''){
						$current_iban = $result->machtiging->iban;
						$current_bic = $result->machtiging->bic;
						$current_gebruikers_id = $result->machtiging->gebruikers_id;
						$current_rekening = $result->machtiging->rekeningnummer;
						$current_period = $result->machtiging->frequentie;
						$current_price = $result->machtiging->bedrag;
						update_post_meta( $order_id, 'IBAN', (string)$current_iban);
						update_post_meta( $order_id, 'BIC', (string)$current_bic);
						update_post_meta( $order_id, 'Accountholder', (string)$current_gebruikers_id);
						update_post_meta( $order_id, 'Rekening', (string)$current_rekening);
						update_post_meta( $order_id, 'PayIBAN periode', (string)$current_period);
						update_post_meta( $order_id, 'PayIBAN prijs', (string)$current_price);
					}
					

					$order->payment_complete();
					// Return thank you page redirect
					return array(
						'result' => 'success',
						'redirect' => $this->get_return_url( $order )
					);
				}


			}

			function Proef11( $bankrek ) {
				$csom = 0;                                       // variabele initialiseren
				$pos = 9;                                        // het aantal posities waaruit een bankrekeningnr hoort te bestaan
				for ( $i = 0; $i < strlen( $bankrek ); $i++ ) {
					$num = substr( $bankrek, $i, 1 );                  // bekijk elk karakter van de ingevoerde string
					if ( is_numeric( $num ) ) {                      // controleer of het karakter numeriek is
						$csom += $num * $pos;                       // bereken somproduct van het cijfer en diens positie
						$pos--;                                     // naar de volgende positie
					}
				}
				$postb = ( ( $pos > 1 ) && ( $pos < 7 ) );         // True als resterende posities tussen 1 en 7 => Postbank
				$mod = $csom % 11;                                                                                                                                                                                                // bereken restwaarde van somproduct/11.
				return $postb || !( $pos || $mod );             // True als het een postbanknr is of restwaarde=0 zonder resterende posities
			}






			// get all pages HELPER FUNCTION
			function get_pages( $title = false, $indent = true ) {
				$wp_pages = get_pages( 'sort_column=menu_order' );
				$page_list = array();
				if ( $title ) $page_list[] = $title;
				foreach ( $wp_pages as $page ) {
					$prefix = '';
					// show indented child pages?
					if ( $indent ) {
						$has_parent = $page->post_parent;
						while ( $has_parent ) {
							$prefix .=  ' - ';
							$next_page = get_page( $has_parent );
							$has_parent = $next_page->post_parent;
						}
					}
					// add to page list array array
					$page_list[$page->ID] = $prefix . $page->post_title;
				}
				return $page_list;
			}

			/**
			 * When a subscription is canceled, notify the admin by email and cancel the subscription in woocommerce
			 * Admin has to cancel the subscription in payIBAN.
			 * */
			function cancel_payiban( $order, $product_id ) {
				global $woocommerce;


				$order_id = $order->id;
				$mandaatid = get_post_meta( $order_id, 'Mandaatid', true );

				$api_username = $this ->merchant_id;
				$api_password = $this ->merchant_pass;
				$api_test = 'nee';
				if ( $this->test_mode == 'no' ) {
					$api_test = 'nee';
				}else {
					$api_test = 'ja';
				}


				$url = 'https://api.payiban.com/webservice/index.php'; // Basis url van elke xmlstring request
				$xml = '
				<?xml version="1.0" encoding="UTF-8"?>
				<webservice>
					<user>'.$api_username.'</user>
				    <password>'.$api_password.'</password>
				    <request>verwijder_machtiging</request>
				    <test>'.$api_test.'</test>
				    <output>xml</output>
				    <data>
				        <item>
				            <mandateid>'.$mandaatid.'</mandateid>
				        </item>
				    </data>
				</webservice>';



				$xmlresponse = file_get_contents( $url.'?xmlstring='.urlencode( $xml ));

				$result = simplexml_load_string( $xmlresponse );

				if ( $result->error != '' ) {
					wc_add_notice( __( 'Error canceling your subscription, please contact the website admin.<br/>', 'woocommerce-payiban-free' ) . $result->error  ,'error');
					
					return;
				}else {
					if ( $this -> merchant_mail != '' ) {
						$message = __( 'Dear Administrator,\nOne of your customers has cancelled a subscription.\nThe subscription is also canceled in payIBAN.\n\nDetails of the order:', 'woocommerce-payiban-free' );

						$_pf = new WC_Product_Factory();
						$_product = $_pf->get_product( $product_id );

						if (is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ){ 
							$periodname = WC_Subscriptions_Order::get_subscription_period( $order );
							$price = WC_Subscriptions_Order::get_price_per_period( $order );
							$period = WC_Subscriptions_Order::get_subscription_interval( $order );
						}else{
							$periodname = 'one-time';
							$period = '';
							$price = $_product->price;
						}

						

						$message .= 'Customer ID: '.$order->user_id.'\n';
						$message .= 'Order number: '.$order->get_order_number().'\n';
						$message .= ''.$price.' per '.$period.' '.$periodname.'\n';
						$message .= 'Product description: '.$_product->get_title().'\n\nKind regards,\n payIBAN plugin.';

						wp_mail( $this -> merchant_mail, __( 'IMPORTANT: Cancelled Subscription', 'woocommerce-payiban-free' ), $message );
						//wc_add_notice( __('Subscription successfully ended. ', 'woocommerce-payiban-free'), $notice_type = 'success' );
					}
					return true;
				}
			}
		}

		

		

		/**
		 * Add the Gateway to WooCommerce
		 * */
		function woocommerce_add_payibanfree_gateway( $methods ) {
			$methods[] = 'WC_payIBANfree';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_payibanfree_gateway' );

	}
}

