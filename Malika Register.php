<?php
/*
  Plugin Name: Malika Reseller
  Plugin URI: http://code.tutsplus.com
  Description: Updates user rating based on number of posts.
  Version: 1.0
  Author: Yusuf Eko N.
  Author URI: https://malika.id
 */

 function registration_form( $address = array()) {
    $load_address = 'billing'; 
    $form = malika_get_field_user($address);
    $check = array_filter($form);
	 
	 ob_start();
     malika_layout_field($form,$load_address);
	 $form_register = ob_get_clean();

    _style_register_reseller();
   ?>
<div class="woocommerce">
<div class="woocommerce-MyAccount-content">
<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ;?>">

<h3><?php// echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3>

<div class="woocommerce-address-fields">
    <div class="woocommerce-address-fields__field-wrapper">
    <p class="form-row form-row-wide validate-required" id="username_field" data-priority="">
        <label for="username" class="">Username <abbr class="required" title="harus diisi">*</abbr></label>
        <span class="woocommerce-input-wrapper">
            <input type="text" class="input-text " name="username" id="username" placeholder="" value="" autocomplete="username">
        </span>
    </p>
    <p class="form-row form-row-wide validate-required" id="password_field" data-priority="">
        <label for="password" class="">Password <abbr class="required" title="harus diisi">*</abbr></label>
        <span class="woocommerce-input-wrapper">
            <input type="password" class="input-pass " name="password" id="password" placeholder="" value="" autocomplete="password">
        </span>
    </p>
	<p class="form-row form-row-wide validate-required" id="shopname_field" data-priority="">
        <label for="shopname" class="">Nama Toko <abbr class="required" title="harus diisi">*</abbr></label>
        <span class="woocommerce-input-wrapper">
            <input type="text" class="input-text " name="shopname" id="shopname" placeholder="" value="" autocomplete="shopname">
        </span>
    </p>
    <p class="form-row form-row-wide" id="website_field" data-priority="">
        <label for="website" class="">Website (optional)</label>
        <span class="woocommerce-input-wrapper">
            <input type="text" class="input-pass " name="website" id="website" placeholder="" value="" autocomplete="website">
        </span>
    </p>
    <p class="form-row form-row-first">
        <label> --------------------------------------- </label>
    </p>
        <?php // menata ulang
	 		echo $form_register;
        ?>
    </div>

    <?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

    <p>
        <input type="submit" class="button" name="register-reseller" value="register reseller" />
    </p>
</div>

</form>
</div>
</div>
<?php
malika_wscript_js();
malika_wscript_ongkir($form);
}

function registration_validation( $args = array() )  {
    global $reg_errors;
    $reg_errors = new WP_Error;

    $check = array_filter($args);
    if(empty($check)){
        $reg_errors->add('data','Tidak ada data yang dikirimkan');
    }

    if ( empty( $args['username'] ) || empty( $args['password'] ) ) {
        $reg_errors->add('field', 'Required form field is missing');
    }

    if ( 4 > strlen( $args['username'] ) ) {
        $reg_errors->add( 'username_length', 'Username too short. At least 4 characters is required' );
    }

    if ( username_exists( $args['username'] ) ){
        $reg_errors->add('user_name', 'Sorry, that username already exists!');
    }

    if ( ! validate_username( $args['username'] ) ) {
        $reg_errors->add( 'username_invalid', 'Sorry, the username you entered is not valid' );
    }

    if ( 5 > strlen( $args['password'] ) ) {
        $reg_errors->add( 'password', 'Password length must be greater than 5' );
    }

    if ( !is_email( $args['billing_email'] ) ) {
        $reg_errors->add( 'email_invalid', 'Email is not valid' );
    }

    if ( email_exists( $args['billing_email'] ) ) {
        $reg_errors->add( 'email', 'Email Already in use' );
    }

    if ( ! empty( $args['website'] ) ) {
        if ( ! filter_var( $args['website'], FILTER_VALIDATE_URL ) ) {
            $reg_errors->add( 'website', 'Website is not a valid URL' );
        }
    }
	
	if(empty($args[$key])){
        $reg_errors->add('shopname_error','Nama Toko tidak boleh kosong');
    }

    // check billing data
    $data = _reg_form_reseller();
    foreach($data as $key => $val){
        if($key!='billing_negara'){
            if(empty($args[$key])){
                $reg_errors->add($key.'_error',$val['label'].' tidak boleh kosong');
            }
        }
    }

    if ( is_wp_error( $reg_errors ) ) {
        foreach ( $reg_errors->get_error_messages() as $error ) {
     
            echo '<div>';
            echo '<strong>ERROR</strong>:';
            echo $error . '<br/>';
            echo '</div>';
        }
          
    }
}

function complete_registration($args = array()) {
    global $reg_errors;

    $check = array_filter($args);
    if(empty($check)){
        die('Tidak ada data yang di input');
    }

    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
        $userdata = array(
        'user_login'    => $args['username'],
        'user_email'    => $args['billing_email'],
        'user_pass'     => $args['password'],
        'user_url'      => $args['website'],
        'first_name'    => $args['billing_first_name'],
        'role'          => 'reseller'
        );

        $user_id = wp_insert_user( $userdata );
		update_user_meta($user_id,'reseller_shopname',$args['shopname']);
		
        $data = _reg_form_reseller();
        foreach ($data as $key => $val) {
            update_user_meta($user_id,$key,$args[$key]);
        }

        echo 'Registration complete. Goto <a href="' . get_site_url() . '/my-account">login page</a>.';   
    }
}

function custom_registration_function() {
    if ( isset($_POST['register-reseller'] ) ) {

        $args = array(
            'username'  => isset($_POST['username'])?$_POST['username']:'',
            'password'  => isset($_POST['password'])?$_POST['password']:'',
			'shopname' 	=> isset($_POST['shopname'])?$_POST['shopname']:'',
            'website'   => isset($_POST['website'])?$_POST['website']:''
        );

        $data = _reg_form_reseller();
        foreach($data as $key => $val){
            $args[$key] = isset($_POST[$key])?$_POST[$key]:'';
        }

        registration_validation($args);
         
        // sanitize user form input
        $args['username'] = sanitize_user($args['username']);
        $args['password'] = esc_attr($args['password']);
		$args['shopname'] = sanitize_user($args['shopname']);
        $args['website'] = esc_url($args['website']);
        
        foreach ($data as $key => $val) {
            if($key=='billing_email'){
                $args[$key] = sanitize_email($args[$key]);
            }else{
                $args[$key] = sanitize_text_field($args[$key]);
            }
        }
 
        // call @function complete_registration to create the user
        // only when no WP_error is found
        complete_registration($args);
    }
}

// Register a new shortcode: [cr_custom_registration]
add_shortcode( 'malika-register-reseller', 'custom_registration_shortcode' );
 
// The callback function that will replace [book]
function custom_registration_shortcode() {
	$address = _reg_form_reseller();
	
	ob_start();
	custom_registration_function();
    registration_form($address);
    return ob_get_clean();
}

// Create Form Register To billing
function _reg_form_reseller(){
   $args = Array ( 
       'billing_first_name' => Array ( 
            'label'         => 'Nama Lengkap',
            'required'      => 1,
            'class'         => Array ('form-row-first' ),
            'autocomplete'  => 'given-name', 
            'priority'      => 10, 
            'value'         => '' 
       ), 
       'billing_provinsi'   => Array ( 
           'label'          => 'Provinsi', 
           'placeholder'    => 'Provinsi', 
           'required'       => 1, 
           'clear'          => '',
           'type'           => 'select', 
           'class'          => Array ('form-row-wide' ), 
           'options'        => Array (''), 
           'value'          => 0 
       ), 
        'billing_country'   => Array ( 
            'type'          => 'country', 
            'label'         => 'Negara', 
            'required'      => 1,
            'class'         => Array ('form-row-wide','address-field','update_totals_on_change' ), 
            'autocomplete'  => 'country', 
            'priority'      => 40, 
            'value'         => 'ID' 
        ), 
        'billing_address_1' => Array ( 
            'label'         => 'Alamat', 
            'placeholder'   => 'Alamat Rumah', 
            'required'      => 1, 
            'class'         => Array ('form-row-wide','address-field' ), 
            'autocomplete'  => 'address-line1', 
            'priority'      => 50, 
            'value'         => '' 
        ), 
        'billing_negara'    => Array ( 
            'label'         => 'Negara', 
            'placeholder'   => 'Negara', 
            'required'      => 1, 
            'clear'         => '',
            'type'          => 'select', 
            'class'         => Array ('form-row-wide' ), 
            'options'       => Array (''), 
            'value'         => 0 
        ), 
        'billing_kabupaten' => Array ( 
            'label'         => 'Kota/Kabupaten', 
            'placeholder'   => 'Kota/Kabupaten', 
            'required'      => 1, 
            'clear'         => '',
            'type'          => 'select', 
            'class'         => Array ('form-row-wide' ), 
            'options'       => Array (''), 
            'value'         => 0  
        ), 
        'billing_district'  => Array ( 
            'label'         => 'Kecamatan', 
            'placeholder'   => 'Kecamatan', 
            'required'      => 1, 
            'clear'         => '',
            'type'          => 'select', 
            'class'         => Array ('form-row-wide' ), 
            'options'       => Array (''), 
            'value'         => 0  
        ), 
        'billing_city'      => Array ( 
            'label'         => 'Kota', 
            'required'      => 1, 
            'class'         => Array ('form-row-wide','address-field' ),
            'autocomplete'  => 'address-level2' ,
            'priority'      => 70, 
            'value'         => '' 
        ), 
        'billing_state'     => Array ( 
            'type'          => 'state', 
            'label'         => 'Provinsi', 
            'required'      => 1, 
            'class'         => Array ('form-row-wide','address-field' ), 
            'validate'      => Array ('state' ), 
            'autocomplete'  => 'address-level1', 
            'priority'      => 80, 
            'country_field' => 'billing_country', 
            'value'         => '' 
        ), 
        'billing_postcode'  => Array ( 
            'label'         => 'Kode Pos', 
            'required'      => 1, 
            'class'         => Array ('form-row-wide' ,'address-field' ), 
            'validate'      => Array ('postcode' ), 
            'autocomplete'  => 'postal-code', 
            'priority'      => 90, 
            'value'         => '' 
        ), 
        'billing_phone'     => Array ( 
            'label'         => 'Telp', 
            'required'      => 1, 
            'type'          => 'tel', 
            'class'         => Array ( 'form-row-wide' ), 
            'validate'      => Array ( 'phone' ), 
            'autocomplete'  => 'tel', 
            'priority'      => 100, 
            'value'         => '' 
        ), 
        'billing_email' => Array ( 
            'label'         => 'Alamat email', 
            'required'      => 1, 
            'type'          => 'email', 
            'class'         => Array ( 'form-row-wide' ), 
            'validate'      => Array ( 'email' ), 
            'autocomplete'  => 'email username', 
            'priority'      => 110, 
            'value'         => '' 
        ) 
    );

    return $args;
}

function _style_register_reseller(){

?>
    <style>
#billing_city_field,
#billing_last_name_field,
#billing_company_field,
#billing_state_field,
#billing_country_field,
#billing_address_2,
#billing_postcode{
	display:none !important;
	position:relative;
}
.woocommerce-address-fields .select2-container--default .select2-selection--single,
input[type="text"], input[type="password"], input[type="date"], input[type="datetime"], input[type="datetime-local"], input[type="month"], input[type="week"], input[type="email"], input[type="number"], input[type="search"], input[type="tel"], input[type="time"], input[type="url"], textarea, select{
	border:none;
	border-bottom:1px solid;
	color:#888;
}
#billing_phone_field,
#billing_email_field,
#billing_first_name_field{
	width:100% !important;
}
.select2-container{
	z-index:900;
}
.select2-container .select2-choice{
	border-bottom:1px solid;
	border-bottom-color: rgba(84, 84, 84, 0.1);
	border-radius:unset;
}
.select2-container .select2-choice .select2-arrow{
	font-size:1.25em;
	width:20px;
}
.woocommerce-address-fields input,
.woocommerce-address-fields select{
	font-weight:normal;
}

@media(min-width:1280px){
	.woocommerce-MyAccount-content {
    	float: right;
    	width: 78%;
	}
	.woocommerce form .form-row input.input-text, .woocommerce form .form-row textarea{
		width:50%;
	}
    #password_field,
    #website_field,
	#billing_negara_field,
	#billing_country_field,
	#billing_provinsi_field,
	#billing_kabupaten_field,
	#billing_district_field,
	#s2id_billing_city,
	#s2id_billing_kota,
	#billing_state_field,
	#billing_kodepos{
		width:50% !important;
	}
	
}
</style>
<?php
}