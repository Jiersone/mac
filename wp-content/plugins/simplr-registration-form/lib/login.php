<?php
/*
**
** Login page shortcode handler
**
*/

function simplr_login_page() {
	global $errors;
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
		if(is_wp_error($errors)) {
		$error_header = '<div id="login_error">' .@$errors->get_error_message()  . "</div>\n";
		}
	ob_start();
	simplr_login_switch();
	$form = ob_get_contents();
	ob_end_clean();
	return @$error_header.$form;
}

function simplr_login_header() {
	// Don't index any of these forms
	add_filter( 'pre_option_blog_public', '__return_zero' );
	add_action( 'login_head', 'noindex' );

	if ( empty($wp_error) )
		$wp_error = new WP_Error();

	// Shake it!
	$shake_error_codes = array( 'empty_password', 'empty_email', 'invalid_email', 'invalidcombo', 'empty_username', 'invalid_username', 'incorrect_password' );
	$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );

	if ( $shake_error_codes && $wp_error->get_error_code() && in_array( $wp_error->get_error_code(), $shake_error_codes ) )
		add_action( 'login_head', 'wp_shake_js', 12 );
}

function simplr_login_includes($post,$option,$file,$path) {
	global $errors, $is_iphone, $interim_login, $current_site;

	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	$options = get_option('simplr_reg_options');

	global $wp;

	$action = @$_REQUEST['action'];

	if(@$_REQUEST['action'] == '' )
			wp_redirect('?action=login');

	if(isset($options->login_redirect) AND end($path) == $post->post_name) {

		switch($action) {

		case 'lostpassword':
		case 'retrievepassword':

			if ( isset($http_post) ) {
				$errors = retrieve_password();

				if ( !is_wp_error($errors) ) {
					$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'wp-login.php?checkemail=confirm';
					wp_safe_redirect( $redirect_to );
					exit();
				}

			}

			if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $errors->add('invalidkey', __('Sorry, that key does not appear to be valid.','simplr-reg'));
			$redirect_to = apply_filters( 'lostpassword_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );

			do_action('lost_password');
			$user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
		break;

		case 'login':
		case 'default':

			$secure_cookie = '';
			$interim_login = isset($_REQUEST['interim-login']);
			// If the user wants ssl but the session is not ssl, force a secure cookie.
			if ( !empty($_POST['log']) && !force_ssl_admin() ) {
				$user_name = sanitize_user($_POST['log']);
				if ( $user = get_userdatabylogin($user_name) ) {
					if ( get_user_option('use_ssl', $user->ID) ) {
						$secure_cookie = true;
						force_ssl_admin(true);
					}
				}
			}

			if ( isset( $_REQUEST['redirect_to'] ) ) {
				$redirect_to = $_REQUEST['redirect_to'];
				// Redirect to https if user wants ssl
				if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
					$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
			} else {
				$redirect_to = admin_url();
			}

			$reauth = empty($_REQUEST['reauth']) ? false : true;
				// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
				// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
				// the admin via http or https.
				if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
					$secure_cookie = false;

				$user = wp_signon('', $secure_cookie);

				$redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

				if ( !is_wp_error($user) && !$reauth ) {
					if ( $interim_login ) {
						$message = '<p class="message">' . __('You have logged in successfully.','simplr-reg') . '</p>';
						?>
						<script type="text/javascript">setTimeout( function(){window.close()}, 8000);</script>
						<p class="alignright">
						<input type="button" class="button-primary" value="<?php esc_attr_e('Close','simplr-reg'); ?>" onclick="window.close()" /></p>
						</div></body></html>
				<?php		exit;
					}

					if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
						// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
						if ( is_multisite() && !get_active_blog_for_user($user->id) && !is_super_admin( $user->id ) )
							$redirect_to = user_admin_url();
						elseif ( is_multisite() && !$user->has_cap('read') )
							$redirect_to = get_dashboard_url( $user->id );
						elseif ( !$user->has_cap('edit_posts') )
							$redirect_to = admin_url('profile.php');
					}
					wp_safe_redirect($redirect_to);
					exit();
				}

				$errors = $user;
				// Clear errors if loggedout is set.
				if ( !empty($_GET['loggedout']) || $reauth )
					$errors = new WP_Error();

				// If cookies are disabled we can't log in even with a valid user+pass
				if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
					$errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress.",'simplr-reg'));

				// Some parts of this script use the main login form to display a message
				if		( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )
					$errors->add('loggedout', __('You are now logged out.','simplr-reg'), 'message');
				elseif	( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )
					$errors->add('registerdisabled', __('User registration is currently not allowed.','simplr-reg'));
				elseif	( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )
					$errors->add('confirm', __('Check your e-mail for the confirmation link.','simplr-reg'), 'message');
				elseif	( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )
					$errors->add('newpass', __('Check your e-mail for your new password.','simplr-reg'), 'message');
				elseif	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )
					$errors->add('registered', __('Registration complete. Please check your e-mail.','simplr-reg'), 'message');
				elseif	( $interim_login )
					$errors->add('expired', __('Your session has expired. Please log-in again.','simplr-reg'), 'message');

				// Clear any stale cookies.
				if ( $reauth )
					wp_clear_auth_cookie();

				break;
			}
		}
	}

/**
 * Outputs the header for the login page.
 *
 * @uses do_action() Calls the 'login_head' for outputting HTML in the Log In
 *		header.
 * @uses apply_filters() Calls 'login_headerurl' for the top login link.
 * @uses apply_filters() Calls 'login_headertitle' for the top login title.
 * @uses apply_filters() Calls 'login_message' on the message to display in the
 *		header.
 * @uses $error The error global, which is checked for displaying errors.
 *
 * @param string $title Optional. WordPress Log In Page title to display in
 *		<title/> element.
 * @param string $message Optional. Message to display in header.
 * @param WP_Error $wp_error Optional. WordPress Error Object
 */
function login_header($title = 'Log In', $message = '', $wp_error = '') {
	global $error, $is_iphone, $interim_login, $current_site;

	// Don't index any of these forms
	add_filter( 'pre_option_blog_public', '__return_zero' );
	add_action( 'login_head', 'noindex' );

	if ( empty($wp_error) )
		$wp_error = new WP_Error();

	// Shake it!
	$shake_error_codes = array( 'empty_password', 'empty_email', 'invalid_email', 'invalidcombo', 'empty_username', 'invalid_username', 'incorrect_password' );
	$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );

	if ( $shake_error_codes && $wp_error->get_error_code() && in_array( $wp_error->get_error_code(), $shake_error_codes ) )
	add_action( 'login_head', 'wp_shake_js', 12 );
	do_action( 'login_enqueue_scripts' );
	do_action( 'login_head' ); ?>
<div class="login">
<?php
	$message = apply_filters('login_message', $message);
	if ( !empty( $message ) ) echo $message . "\n";

	// In case a plugin uses $error rather than the $wp_errors object
	if ( !empty( $error ) ) {
		$wp_error->add('error', $error);
		unset($error);
	}

	if ( $wp_error->get_error_code() ) {
		$errors = '';
		$messages = '';
		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data($code);
			foreach ( $wp_error->get_error_messages($code) as $error ) {
				if ( 'message' == $severity )
					$messages .= '	' . $error . "<br />\n";
				else
					$errors .= '	' . $error . "<br />\n";
			}
		}
		if ( !empty($errors) )
			echo '<div id="login_error">' . apply_filters('login_errors', $errors) . "</div>\n";
		if ( !empty($messages) )
			echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
	}
} // End of login_header()

/**
 * Outputs the footer for the login page.
 *
 * @param string $input_id Which input to auto-focus
 */
function login_footer($input_id = '') {
	?>
	<p id="backtoblog"><a href="<?php bloginfo('url'); ?>/" title="<?php esc_attr_e('Are you lost?','simplr-reg') ?>"><?php printf(__('&larr; Back to %s','simplr-reg'), get_bloginfo('title', 'display' )); ?></a></p>
	</div>
<?php if ( !empty($input_id) ) : ?>
<script type="text/javascript">
try{document.getElementById('<?php echo $input_id; ?>').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload();
</script>
<?php endif; ?>

<?php do_action('login_footer'); ?>
<?php
}

function wp_shake_js() {
	global $is_iphone;
	if ( $is_iphone )
		return;
?>
<script type="text/javascript">
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
function s(id,pos){g(id).left=pos+'px';}
function g(id){return document.getElementById(id).style;}
function shake(id,a,d){c=a.shift();s(id,c);if(a.length>0){setTimeout(function(){shake(id,a,d);},d);}else{try{g(id).position='static';wp_attempt_focus();}catch(e){}}}
addLoadEvent(function(){ var p=new Array(15,30,15,0,-15,-30,-15,0);p=p.concat(p.concat(p));var i=document.getElementById('loginform').id;g(i).position='relative';shake(i,p,20);});
</script>
<?php
}

/**
 * Handles sending password retrieval email to user.
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function retrieve_password() {
	global $wpdb, $current_site,$simplr_options;

	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.','simplr-reg'));

	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by_email(trim($_POST['user_login']));
		if ( empty($user_data) )
			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.','simplr-reg'));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_userdatabylogin($login);
	}

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.','simplr-reg'));
		return $errors;
	}

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )
		return new WP_Error('no_password_reset', __('Password reset is not allowed for this user','simplr-reg'));
	else if ( is_wp_error($allow) )
		return $allow;

	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
	if ( empty($key) ) {
		// Generate something random for a key...
		$key = wp_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);
		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
	}
	$message = __('Someone requested that the password be reset for the following account:','simplr-reg') . "\r\n\r\n";
	$message .= network_site_url() . "\r\n\r\n";
	$message .= sprintf(__('Username: %s','simplr-reg'), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.','simplr-reg') . "\r\n\r\n";
	$message .= __('To reset your password, visit the following address:','simplr-reg') . "\r\n\r\n";
	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Password Reset','simplr-reg'), $blogname );

	$title = apply_filters('retrieve_password_title', $title);
	$message = apply_filters('retrieve_password_message', $message, $key);

	if ( isset( $simplr_options->default_email ) ) {
		$from = $simplr_options->default_email;
	} else {
		$from = get_option('admin_email');
	}
	$headers = "From: ".$blogname." <".$from."> \r\n";

	if ( $message && !wp_mail($user_email, $title, $message, $headers) )
		wp_die( __('The e-mail could not be sent.','simplr-reg') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...','simplr-reg') );

	return true;
}

/**
 * Retrieves a user row based on password reset key and login
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 * @param string $login The user login
 *
 * @return object|WP_Error
 */
function check_password_reset_key($key, $login) {
	global $wpdb;

	$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new WP_Error('invalid_key', __('Invalid key','simplr-reg'));

	if ( empty($login) || !is_string($login) )
		return new WP_Error('invalid_key', __('Invalid key','simplr-reg'));

	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

	if ( empty( $user ) )
		return new WP_Error('invalid_key', __('Invalid key','simplr-reg'));

	return $user;
}

/**
 * Handles resetting the user's password.
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 */
function reset_password($user, $new_pass) {
	do_action('password_reset', $user, $new_pass);

	wp_set_password($new_pass, $user->ID);

	wp_password_change_notification($user);
}

/**
 * Handles registering a new user.
 *
 * @param string $user_login User's username for logging in
 * @param string $user_email User's email address to send password and add
 * @return int|WP_Error Either user's ID or error on failure.
 */
function register_new_user( $user_login, $user_email ) {
	$errors = new WP_Error();

	$sanitized_user_login = sanitize_user( $user_login );
	$user_email = apply_filters( 'user_registration_email', $user_email );

	// Check the username
	if ( $sanitized_user_login == '' ) {
		$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.','simplr-reg' ) );
	} elseif ( ! validate_username( $user_login ) ) {
		$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.','simplr-reg' ) );
		$sanitized_user_login = '';
	} elseif ( username_exists( $sanitized_user_login ) ) {
		$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.','simplr-reg' ) );
	}

	// Check the e-mail address
	if ( $user_email == '' ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.','simplr-reg' ) );
	} elseif ( ! is_email( $user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.','simplr-reg' ) );
		$user_email = '';
	} elseif ( email_exists( $user_email ) ) {
		$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.','simplr-reg' ) );
	}

	do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

	$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

	if ( $errors->get_error_code() )
		return $errors;

	$user_pass = wp_generate_password( 12, false);
	$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
	if ( ! $user_id ) {
		$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !','simplr-reg' ), get_option( 'admin_email' ) ) );
		return $errors;
	}

	update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

	wp_new_user_notification( $user_id, $user_pass );

	return $user_id;
}

function simplr_login_switch() {
	$options = get_option('simplr_reg_options');
	if(!isset($_GET['action'])) {$_GET['action'] = 'login';}
	$action = $_GET[ 'action'];
	global $errors;
switch ($action) {
		case 'logout' :
		check_admin_referer('log-out');
		wp_logout();

		$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'wp-login.php?loggedout=true';
		wp_safe_redirect( $redirect_to );
		exit();

		break;

		case 'lostpassword' :
		case 'retrievepassword' :

		?>

		<form name="lostpasswordform" id="lostpasswordform" action="<?php echo get_permalink($options->login_redirect); ?>?action=lostpassword" method="post">
		<p>
			<label><?php _e('Username or E-mail:') ?><br />
			<input type="text" name="user_login" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
		</p>
		<?php do_action('lostpassword_form'); ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( @$redirect_to ); ?>" />
		<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Get New Password','simplr-reg'); ?>" tabindex="100" /></p>
		</form>

		<p id="nav">
		<a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a>
		<?php if (get_option('users_can_register')) : ?>
		| <a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register','simplr-reg') ?></a>
		<?php endif; ?>
		</p>

		<?php
		login_footer('user_login');
		break;

		case 'resetpass' :
		case 'rp' :
		$user = check_password_reset_key($_GET['key'], $_GET['login']);

		if ( is_wp_error($user) ) {
			wp_redirect( site_url('wp-login.php?action=lostpassword&error=invalidkey') );
			exit;
		}

		$errors = '';

		if ( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] ) {
			$errors = new WP_Error('password_reset_mismatch', __('The passwords do not match.','simplr-reg'));
		} elseif ( isset($_POST['pass1']) && !empty($_POST['pass1']) ) {
			reset_password($user, $_POST['pass1']);
			login_header(__('Password Reset','simplr-reg'), '<p class="message reset-pass">' . __('Your password has been reset.','simplr-reg') . ' <a href="' . site_url('wp-login.php', 'login') . '">' . __('Log in','simplr-reg') . '</a></p>');
			login_footer();
			exit;
		}

		wp_enqueue_script('utils');
		wp_enqueue_script('user-profile');

		login_header(__('Reset Password','simplr-reg'), '<p class="message reset-pass">' . __('Enter your new password below.','simplr-reg') . '</p>', $errors );

		?>
		<form name="resetpassform" id="resetpassform" action="<?php echo get_permalink($options->login_redirect).'?action=resetpass&key=' . urlencode($_GET['key']) . '&login=' . urlencode($_GET['login']); ?>" method="post">
		<input type="hidden" id="user_login" value="<?php echo esc_attr( $_GET['login'] ); ?>" autocomplete="off" />

		<p>
			<label><?php _e('New password','simplr-reg') ?><br />
			<input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" /></label>
		</p>
		<p>
			<label><?php _e('Confirm new password','simplr-reg') ?><br />
			<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" /></label>
		</p>

		<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator','simplr-reg'); ?></div>
		<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).','simplr-reg'); ?></p>

		<br class="clear" />
		<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Reset Password','simplr-reg'); ?>" tabindex="100" /></p>
		</form>

		<p id="nav">
		<a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in','simplr-reg') ?></a>
		<?php if (get_option('users_can_register')) : ?>
		| <a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register','simplr-reg') ?></a>
		<?php endif; ?>
		</p>

		<?php
		login_footer('user_pass');
		break;

		case 'login' :
		default:
		$redirect_to = (!isset($redirect_to))?apply_filters('simplr_login_redirect',home_url(),$action):$redirect_to;
		if ( isset($_POST['log']) )
			$user_login = ( 'incorrect_password' == $errors->get_error_code() || 'empty_password' == $errors->get_error_code() ) ? esc_attr(stripslashes($_POST['log'])) : '';
		$rememberme = ! empty( $_POST['rememberme'] );
		?>

		<form name="loginform" id="loginform" action="<?php echo get_permalink($options->login_redirect); ?>?action=<?php echo $action; ?>" method="post">
		<p>
			<label><?php _e('Username','simplr-reg') ?><br />
			<input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr(@$user_login); ?>" size="20" tabindex="10" /></label>
		</p>
		<p>
			<label><?php _e('Password','simplr-reg') ?><br />
			<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
		</p>
		<?php do_action('login_form'); ?>
		<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php checked( $rememberme ); ?> /> <?php esc_attr_e('Remember Me','simplr-reg'); ?></label></p>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Log In','simplr-reg'); ?>" tabindex="100" />
		<?php	if ( isset($interim_login) ) { ?>
			<input type="hidden" name="interim-login" value="1" />
		<?php	} else { ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>" />
		<?php 	} ?>
			<input type="hidden" name="testcookie" value="1" />
		</p>
		</form>

		<?php if ( !isset($interim_login) ) { ?>
		<p id="nav">
		<?php if ( isset($_GET['checkemail']) && in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
		<?php elseif ( get_option('users_can_register') ) : ?>
		<a href="<?php echo site_url('wp-login.php?action=register', 'login') ?>"><?php _e('Register','simplr-reg') ?></a> |
		<a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found','simplr-reg') ?>"><?php _e('Lost your password?','simplr-reg') ?></a>
		<?php else : ?>
		<a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found','simplr-reg') ?>"><?php _e('Lost your password?','simplr-reg') ?></a>
		<?php endif; ?>
		</p>
		<?php } ?>

		<script type="text/javascript">
		function wp_attempt_focus(){
		setTimeout( function(){ try{
		<?php if ( isset($user_login) || isset($interim_login) ) { ?>
		d = document.getElementById('user_pass');
		d.value = '';
		<?php } else { ?>
		d = document.getElementById('user_login');
		<?php if ( 'invalid_username' == @$errors->get_error_code() ) { ?>
		if( d.value != '' )
		d.value = '';
		<?php
		}
		}?>
		d.focus();
		d.select();
		} catch(e){}
		}, 200);
		}

		<?php if ( !$error ) { ?>
		wp_attempt_focus();
		<?php } ?>
		if(typeof wpOnload=='function')wpOnload();
		</script>

		<?php
		login_footer();
		break;
		} // end action switch
}