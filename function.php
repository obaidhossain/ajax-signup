----- functions.php -----

// ajax login localized
function ajax_login_init() {
    wp_register_script('ajax-login-script', get_stylesheet_directory_uri() . '/assets/ajax-login-script.js', array('jquery') ); 
    wp_enqueue_script('ajax-login-script');

    wp_localize_script( 'ajax-login-script', 'ajax_login_var', array( 
        'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
		'ajax_url' => admin_url( 'admin-ajax.php' ),
        'signup_redirect' => wc_get_page_permalink( "myaccount" ),
		'required_message' => __('Please fill all required fields.','themefic'), 
		'valid_email' => __('Please Enter valid email.','themefic'),
		'loading_text' => __('Loading...','themefic'),
    ));
}




// ajax sign up function
function ajax_register() {
	// First check the nonce, if it fails the function will break
	check_ajax_referer( 'woocommerce-register', 'tf-reg-nounce' );

	  $username = esc_attr($_POST['username']);
	  $email = esc_attr($_POST['email']);
	  $password = $_POST['password'];
	  $password2 = $_POST['password2'];
	  $tos_agree = $_POST['tos_agree'];
	  $user_data = array(
	      'user_login' => $username,
	      'user_email' => $email,
	      'user_pass' => $password,
		  'user_pass' => $password2,
		  'terms' => $tos_agree
	  	);
	  $user_id = wp_insert_user($user_data);
	  pmpro_changeMembershipLevel(1, $user_id);
	
	if (!is_wp_error($user_id)){
		$info = array();
        $info['user_login'] = $username;
        $info['user_password'] = $password;
        $info['remember'] = true;			
 
		$ver = do_shortcode('[alg_wc_ev_verification_status content_template="{verification_status}"]');
		$ver = str_replace ( '<div class="alg-wc-ev-verification-status"><strong>', '', $ver );
		$ver = str_replace ( '</strong></div>', '', $ver );
		
		if ($ver == 'Unverified') {
			$args = array(
				'loggedin'	=> false,
				'is_verify'	=> false,
				'message'	=> __( 'Thank you for creating account. You need to verify first. Verification link has been sent to your email.', 'themefic' )
			);
		} else {
			$args = array(
				'loggedin'	=> true,
				'is_verify'	=> true,
				'message'	=> __( 'Your account has been created and verified. You will be redirected to your account now.', 'themefic' )
			);	
			$user_signon = wp_signon( $info, false );
		}

		echo json_encode( $args );
		
	} else {
		if ( isset( $user_id->errors[ 'empty_user_login' ] ) ) {
			$username_error = true;
		} else{
			$username_error = false;
		}
		if ( isset( $user_id->errors[ 'existing_user_login' ] ) ) {
			$username_exists = true;
		} else{
			$username_exists = false;
		}	
		if ( isset( $user_id->errors[ 'email_exists' ] ) ) {
			$email_exists = true;
		} else{
			$email_exists = false;
		}	
		$error_string = $user_id->get_error_message();

		echo json_encode( array(
			'loggedin' => false, 
			'message' =>__( $error_string, 'themefic' ),
			'invalid_username' => $username_error,
			'user_exists' => $username_exists,
			'email_exists' => $email_exists,
		) );
	}
	
	die;
}


// ajax sign up 
function ajax_signup_form_load_call() { ?>
		<form method="post" class="tf_woo_action_form_loaded" id="tf_action_signup" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
        <h2><?php esc_html_e( 'Sign Up With Email', 'woocommerce' ); ?></h2>
<?php do_action( 'woocommerce_register_form_start' ); ?>

<div class="reg_msg success" style="display:none"></div> 
<div class="reg_msg fail" style="display:none"></div> 
<div class="reg_loader" style="display:none"><?php _e('Loading...','themefic');?></div>

<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

    <p class="tf_woo_form_field">
        <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
        <input type="text" class="tf_woo_form_input" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
    </p>

<?php endif; ?>

<p class="tf_woo_form_field">
    <label for="reg_email"><?php esc_html_e( 'Email', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
    <input type="email" class="tf_woo_form_input" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
</p>

<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

    <p class="tf_woo_form_field">
        <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
        <input type="password" class="tf_woo_form_input" name="password" id="reg_password" autocomplete="new-password" />
    </p>

    <p class="tf_woo_form_field">
        <label for="reg_password2"><?php esc_html_e( 'Confirm Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
        <input type="password" class="tf_woo_form_input" name="password2" id="reg_password2" autocomplete="new-password" />
    </p>

<?php else : ?>

    <p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>

<?php endif; ?>

<p class="tf_woo_form_action">
            <label><input class="tf_woo_form_input" name="tos_agree" type="checkbox" id="tos_agree" /> <span><?php printf( __( 'I have read and agree to <a href="/terms-of-use-policies-and-disclaimers" target="_blank">Terms of Use</a> as well as <a href="/privacy-policy" target="_blank">Privacy Policy</a> and <a href="/cookies-policy" target="_blank">Cookies Policy</a>', 'themefic' ) ); ?></span></label>
        </p>

<p class="woocommerce-form-row form-row">
<input type="hidden" name="action" value="ajax_register">
    <?php wp_nonce_field( 'woocommerce-register', 'tf-reg-nounce' ); ?>
    <button type="submit" class="tf_woo_form_submit" name="register" value="<?php esc_attr_e( 'Create Account', 'woocommerce' ); ?>"><?php esc_html_e( 'Create Account', 'woocommerce' ); ?></button>
</p>

<?php do_action( 'woocommerce_register_form_end' ); ?>

</form>

    <p class="tf_signup_message"><?php printf( __( 'Already have an account? %1$s Sign in %2$s here.', 'themefic' ), '<a class="tf_popup_link tf_email_login text-red" data-popup-element="ajax_login_form_load" data-width="600px">', '</a>' ); ?></p>
    <style>
    <?php 
        $signup_social_text = get_field('signup_social_text','option');
        echo '#tf_action_signup #nsl-custom-login-form-1:before {content: "'.$signup_social_text.'";}';
        $signin_social_text = get_field('signin_social_text','option');
        echo '#tf_action_login #nsl-custom-login-form-1>div:nth-child(2):before {content: "'.$signin_social_text.'";}';
    ?>
    </style>
    <script>ajax_popup_function();</script>
<?php die(); 
}


