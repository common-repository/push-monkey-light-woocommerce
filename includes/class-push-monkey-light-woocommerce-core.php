<?php
// WordPress Check
if ( ! defined( 'ABSPATH' ) ) exit;

// required core bundle files
require_once( plugin_dir_path( __FILE__ ) . 'class-push-monkey-light-woocommerce-client.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-push-monkey-light-woocommerce-debugger.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-push-monkey-light-woocommerce.php' );
require_once( plugin_dir_path( __FILE__ ) . './controllers/class-push-monkey-light-woocommerce-review-notice-controller.php' );
require_once( plugin_dir_path( __FILE__ ) . '../models/class-push-monkey-light-woocommerce-review-notice.php' );

/**
 * Class for Push Monkey Light WooCommerce.
 * Main class that connects the WordPress API
 * with the Push Monkey API
 */
class PM_Light_WooCommerce {

	// Public
	public $endpointURL;
	public $apiClient;

	// Const variable
	const ACCOUNT_KEY_KEY = 'pm_light_woocommerce_account_key';
	const WEBSITE_PUSH_ID_KEY = 'pm_light_woocommerce_website_push_id_key';
	const WEBSITE_NAME_KEY = 'pm_light_woocommerce_website_name';
	const USER_SIGNED_IN = 'pm_light_woocommerce_user_signed_in';
	const FLUSH_REWRITE_RULES_FLAG_KEY = 'pm_light_woocommerce_user_flush_key';
	const WOO_COMMERCE_ENABLED = 'pm_light_woocommerce_woo_enabled';

	/**
	 * Constructor that initializes.
	 */
	function __construct() {

		if ( is_ssl() ) {
			// Live 
			$this->endpointURL = esc_url( 'https://www.getpushmonkey.com' );
		} else {
			// Live
			$this->endpointURL = esc_url( 'http://www.getpushmonkey.com' );
		}
		$this->apiClient = new PM_Light_WooCommerce_Client( $this->endpointURL );
		$this->d = new PM_Light_WooCommerce_Debugger();
		$this->review_notice = new PM_Light_WooCommerce_Review_Notice();
		// Hooks up with the required WordPress actions.
		$this->pm_light_woocommerce_add_actions();
	}

	/**
	 * Checks if an Account Key is stored.
	 *
	 * @return     boolean  True if has account key, False otherwise.
	 */
	public function pm_light_woocommerce_has_account_key() {

		if( $this->pm_light_woocommerce_account_key() ) {

			return true;
		}
		return false;
	}

	/**
	 * Returns the stored Account Key.
	 *
	 * @return    str ( Return account key )
	 */
	public function pm_light_woocommerce_account_key() {

		$account_key = get_option( self::ACCOUNT_KEY_KEY, '' );
		if( ! $this->pm_light_woocommerce_account_key_is_valid( $account_key ) ) {

			return NULL;
		}
		return sanitize_text_field( $account_key );
	}

	/**
	 * Checks if an Account Key is valid.
	 *
	 * @param      str   $account_key  The account key.
	 *
	 * @return     boolean  ( Invalid account key )
	 */
	public function pm_light_woocommerce_account_key_is_valid( $account_key ) {

		if( ! strlen( $account_key ) ) {

			return false;
		}
		return true;
	}

	/**
	 * Checks if a user is signed in.
	 * @return boolean.
	 */
	public function pm_light_woocommerce_signed_in() {

		return get_option( self::ACCOUNT_KEY_KEY );
	}

	/**
	 * Signs in a user with an Account Key or a Token-Secret combination.
	 *
	 * @param    str  $account_key  The account key.
	 */
	public function pm_light_woocommerce_sign_in( $account_key ) {

		delete_option( PM_Light_WooCommerce_Client::PLAN_NAME_KEY );
		$response = $this->apiClient->pm_light_woocommerce_sign_in( $account_key );
		// API responce
		if ( $response ) {
			update_option( self::ACCOUNT_KEY_KEY, $account_key );
			update_option( self::FLUSH_REWRITE_RULES_FLAG_KEY, true);
			update_option( self::USER_SIGNED_IN, true );
		} else {
			$this->sign_in_error = __( 'The Account Key seems to be invalid.', 'push-monkey-light-woocommerce' );
		}
	}

	/**
	 * Write the service worker file.
	 *
	 * @return     boolen  ( boolean  True if has account key, False otherwise. )
	 */
	public function pm_light_woocommerce_service_worker_file_create() {

		$content_file = plugin_dir_path( __DIR__ ) . 'templates/pages/service_worker.php';
		$file_name = ABSPATH . 'service-worker-' . $this->pm_light_woocommerce_account_key() . '.php';
		// If check file exists or not
		if ( file_exists( $content_file ) ) {
			// If check file exists or not
			if ( ! file_exists( $file_name ) ) {
				// File write
				$file = fopen( $file_name, 'w' );
				$file_write = fwrite( $file, file_get_contents( $content_file ) );
				fclose( $file );
				chmod( $file_name, 0644 );
				return $file_write;
			} else {
				return true;
			}
		}
	}

	/**
	 * Service worker file error.
	 */
	public function pm_light_woocommerce_service_worker_file_error() {

	  if ( ( isset( $_GET['page'] ) ) && ( is_admin() ) && ( $_GET['page'] == "pm-light-woocommerce-main-config" ) ) {

			echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: Could not create service-worker-' . $this->pm_light_woocommerce_account_key() . '.php file', 'push-monkey-light-woocommerce' ) . '</p></div>';
		}
	}

	/**
	 * Signs out an user.
	 */
	public function pm_light_woocommerce_sign_out() {

		// delete plugin options
		delete_option( self::USER_SIGNED_IN );
		delete_option( self::ACCOUNT_KEY_KEY );
		delete_option( self::WEBSITE_PUSH_ID_KEY );
		delete_option( self::FLUSH_REWRITE_RULES_FLAG_KEY );
		delete_option( self::WOO_COMMERCE_ENABLED );
		delete_option( PM_Light_WooCommerce_Client::PLAN_NAME_KEY );
	}

	/**
	 * Check if this is the subscription version of Push Monkey
	 *
	 * @return     boolean  True if saas, False otherwise.
	 */
	public function pm_light_woocommerce_is_saas() {

		return file_exists( plugin_dir_path( __FILE__ ) . '../.saas' );
	}

	/**
	 * Adds all the WordPress action hooks required by Push Monkey.
	 */
	function pm_light_woocommerce_add_actions() {

		add_action( 'init', array( $this, 'pm_light_woocommerce_process_forms' ) );

		add_action( 'init', array( $this, 'pm_light_woocommerce_enqueue_scripts' ) );

		add_action( 'init', array( $this, 'pm_light_woocommerce_enqueue_styles' ) );

		add_action( 'init', array( $this, 'pm_light_woocommerce_catch_review_dismiss' ) );
		 
		add_action( 'wp_head', array( $this, 'pm_light_woocommerce_sw_meta' ) );

		add_action('admin_menu', array( $this, 'pm_light_woocommerce_register_settings_pages' ));

		add_action( 'transition_post_status', array( $this, 'pm_light_woocommerce_post_published' ), 10, 3 );

		// If not signed in, display an admin_notice prompting the user to sign in.
		if( ! $this->pm_light_woocommerce_signed_in() ) {

			add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_big_sign_in_notice' ) );
		} else {
			// if check service worker file create or not
			if ( $this->pm_light_woocommerce_service_worker_file_create() == false ) {

					add_action( 'admin_notices' , array( $this, 'pm_light_woocommerce_service_worker_file_error' ) );
			}
		}

		// If the plan is expired, present an admin_notice informing the user.
		if ( $this->pm_light_woocommerce_can_show_expiration_notice() ) {

			add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_big_expired_plan_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_big_upsell_notice' ) );

		add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_manifest_js' ) );

		add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_check_wp_version' ) );
	}

	/**
	 * Check wordpress version
	 */
	function pm_light_woocommerce_check_wp_version() {
		
		global $wp_version;
		// get plugin name
		$plugin_name = str_replace( '-', ' ', plugin_basename( plugin_dir_path( __DIR__ ) ) );
		// check WP version
		if ( version_compare( $wp_version, '4.8' , '<' ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>' . __( 'You are using older WordPress (' . $wp_version . ').', 'push-monkey-light-woocommerce' ) . ' <strong>' . $plugin_name . '</strong> ' . __( 'requires minimum 4.8 (newest better!).', 'push-monkey-light-woocommerce' ) . ' <a href="' . esc_url( site_url( '/wp-admin/update-core.php' ) ) . '">' . __( 'Update WordPress', 'push-monkey-light-woocommerce' ) . '</a></p></div>';
		}
	}

	/**
	 * See if the review notice has been dismissed
	 */
	function pm_light_woocommerce_catch_review_dismiss() {

		if ( isset( $_GET[PM_Light_WooCommerce_Review_Notice_Controller::REVIEW_NOTICE_DISMISS_KEY] ) ) {

			$this->review_notice->pm_light_woocommerce_set_dismiss( true );
		}
	}

	/**
	 * Register menu pages
	 */
	function pm_light_woocommerce_register_settings_pages() {
		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSI2NHB4IiBoZWlnaHQ9IjY0cHgiIHZpZXdCb3g9IjAgMCA2NCA2NCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5sb2dvLWdyYXktMTY8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJsb2dvLWdyYXktMTYiIGZpbGw9IiNFRUVFRUUiPiAgICAgICAgICAgIDxnIGlkPSJsb2dvIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2LjAwMDAwMCwgMC4wMDAwMDApIj4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTM2LjY0OTU4NzEsNDAuNTczMDk0IEM0MS4yODQwMTgxLDQwLjAxNTgwNjcgNDQuODcwMjQwMywzNi4yMDIwOTcxIDQ0Ljg3MDI0MDMsMzEuNTgwMzU3NiBDNDQuODcwMjQwMywyNi41NzUxMTExIDQwLjY2NDE2MzMsMjIuNTE3NTU3MSAzNS40NzU3MDQzLDIyLjUxNzU1NzEgQzMyLjUyMDE3MDksMjIuNTE3NTU3MSAyOS44ODM0MDYsMjMuODM0MTczNSAyOC4xNjEyNzI1LDI1Ljg5MjU4NDIgQzI2LjQ0MjQ3ODgsMjMuNzU5MTM4OCAyMy43NTcwMTA5LDIyLjM4NjQ1MDYgMjAuNzM5MTk4OSwyMi4zODY0NTA2IEMxNS41NTA3NCwyMi4zODY0NTA2IDExLjM0NDY2MjksMjYuNDQ0MDA0NiAxMS4zNDQ2NjI5LDMxLjQ0OTI1MTEgQzExLjM0NDY2MjksMzYuMDI0NDE0NyAxNC44NTg5Njg2LDM5LjgwNzc1MjMgMTkuNDI1NTI2OCw0MC40MjQxNDk2IEMxOC45MDA5ODksNDEuNTU0MDM2NCAxOC42MDkyODA1LDQyLjgwNjQ0OTYgMTguNjA5MjgwNSw0NC4xMjQ1ODkxIEMxOC42MDkyODA1LDQ5LjEyOTgzNTcgMjIuODE1MzU3Niw1My4xODczODk3IDI4LjAwMzgxNjYsNTMuMTg3Mzg5NyBDMzMuMTkyMjc1NSw1My4xODczODk3IDM3LjM5ODM1MjYsNDkuMTI5ODM1NyAzNy4zOTgzNTI2LDQ0LjEyNDU4OTEgQzM3LjM5ODM1MjYsNDIuODY0MDk4MiAzNy4xMzE2MDE4LDQxLjY2MzcxMDIgMzYuNjQ5NTg3MSw0MC41NzMwOTQgWiBNMTEuMzYzNDU3NywwLjA4NDE5ODUxNDUgQzE0LjkyODQ2NjcsMC43MjAyNzU0MDMgMTguNjAwODc0LDMuNDQzODE1NyAyMi4zODA2Nzk4LDguMjU0ODE5NDIgQzIyLjY0NTA3Nyw1Ljk1OTI1NjUgMjIuNzYwODc3NiwzLjUyMzA0MzU0IDIyLjcyODA4MTYsMC45NDYxODA1NTYgQzI3LjEyNTM2MTcsMS44NDEwMTkzNiAzMC43MjMyMzUsNC41OTQ2MzYyNiAzMy41MjE3MDE0LDkuMjA3MDMxMjUgQzMzLjk1NjU5NzIsNy41NDk2MjM4NCAzNC4wMzU4MDczLDUuMzgyMDg5MTIgMzMuNzU5MzMxNiwyLjcwNDQyNzA4IEMzOS43OTE5ODI5LDUuNDA3MTE3IDQyLjcyMTAzNTUsNy4zODA1MzI2NiA0Ni44MDgyODQzLDE1Ljg1MzMzNzEgQzQ5LjY3NDc3ODcsMjEuNzk1NTM2NCA0OS44MzczMjk0LDI4LjU1NzI2ODMgNDkuNzAyMDk0OSwzMi4xMTk2MDI0IEM0OS42ODg2NTc0LDMyLjQ3MzU3MzUgNDkuNjE4NDA4MiwzMi45OTgyMzU0IDQ5LjYxODQwODIsMzMuODAyNjUzIEM0OS42MTg0MDgyLDM0LjcyMTI5OTkgNTAuNjY0OTk4NCwzNS4yODQ2MTM3IDUxLjUwODU5OTIsMzQuNzAzODU3NCBDNDkuNDQ0MTYxMyw1MC4yMjU4NzcyIDM4Ljc0NDcwOTYsNjAuMjUwODIwNSAyNy40NDU5MTU1LDU5LjY2Mzg1ODEgQzIyLjQ5ODk4MTIsNTkuNDA2ODY5MyAyMC4wNjU4NDU2LDU4LjQ0MTQ0ODIgMTcuOTM2MzExLDU4LjIzMzgxMDYgQzE1LjMyNjUwMTUsNTcuOTc5MzQ0NSAxMi41MjAwOTkzLDU5LjA3Njk0NDcgMTAuOTkwMTQ1LDYyLjI2ODc3NTEgQzkuMTIyOTM3OTIsNjYuMTY0MTkwOCAyLjY4NzkxNjM3LDg4LjY2MzE5MjYgMTMuMTQ5NDcxLDg3LjQyNjY0ODkgQzE4LjkwMTA0NDcsODYuNzQyNTE3NCAxNi4yMzc5ODcyLDgyLjg5NzczMDggMTcuMzQ1MDU2Nyw4MC42MTQ1ODYxIEMxOC40ODQ2NTU4LDc4LjA4NzIzMDggMjMuNTU5MzAwOSw3OC42Nzk5MTY5IDI0LjY1Mjk3NTgsODAuNjE0NTg2MSBDMjUuMzczMzA0NSw4MS45NzY2NTMxIDI0LjI1NTA2NTIsOTQuMTQ5MTUxOSAxMi44OTIyNDE2LDk0LjQyOTY4NDIgQy01LjEzMzk4NTcxLDk1LjExMjE0MjYgMS4wMjY2MjkzLDcxLjkwODY1ODUgMS4zMjU1MzA1LDcwLjUxNTU2ODQgQzMuNjUzODcxODgsNTkuNjYzODU4MSA4LjM0MzI2NDk2LDU2LjIyMDY5OTEgNy40NzkzNTg0MSw1NC4wOTA3NzY4IEM2LjYxNTQ1MTg2LDUxLjk2MDg1NDUgMi4yMzkxODM2NCw0OC42ODY3MzIzIDAuOTA5NzIyMjIyLDQyLjM3NzYzODEgQzEuMTc1OTM3MjMsNDIuNTYyNTMzOSAxLjc5OTk4MTAxLDQyLjQ3MTgyMjEgMS43OTU4NTA5Nyw0MS44MzgzNDUgQzEuNzk0NzUyMzMsNDEuNjQ0OTM1OSAxLjc3ODM3NjgzLDQxLjQ5NjU3MyAxLjc0NjcyNDQ1LDQxLjM5MzI1NjMgQy0wLjM1NDQ2NzczNSwzMy41OTE1MDAyIC0wLjM5MTExODIxNiwyNy4xODgzMDY1IDEuNjM2NzczLDIyLjE4MzY3NTEgQzEuOTg5OTM1OTgsMjIuODcxMjAyMyAyLjk3MTY3OTY5LDIyLjk5OTc1NTkgMy42NTM4NzE4OCwyMi4zMzk0NTM0IEMzLjc1ODk4MTY4LDIyLjIzNzcxNjMgNC4wMzYzOTc3OCwyMS43OTc4ODY4IDQuNDE5NzY3NTcsMjEuMDY2OTU2IEM2LjMyNTEzNjg2LDE3LjQzNDE4ODYgMTAuOTMyNjkwOCw2LjkzMDA3ODYzIDExLjM2MzQ1NzcsMC4wODQxOTg1MTQ1IFoiIGlkPSJDb21iaW5lZC1TaGFwZSI+PC9wYXRoPiAgICAgICAgICAgICAgICA8ZyBpZD0iZmFjZS1jb3B5IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNy45MjY0NDAsIDM4LjA5MDgwOSkgcm90YXRlKDguMDAwMDAwKSB0cmFuc2xhdGUoLTI3LjkyNjQ0MCwgLTM4LjA5MDgwOSkgdHJhbnNsYXRlKDE2LjQyNjQ0MCwgMjYuNTkwODA5KSI+ICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNi41MDI0MDg2NiwzLjczMDI2MzE1IEM1Ljk0MzE3NzQsMy4yNzQ1MDcyNyA1LjIzMDEyODQ5LDMuMDAxMzY2MyA0LjQ1MzQ5MjQsMy4wMDEzNjYzIEMyLjY1NzQ4NzM3LDMuMDAxMzY2MyAxLjIwMTUzNzYyLDQuNDYyMDg1NzQgMS4yMDE1Mzc2Miw2LjI2Mzk3NDQ5IEMxLjIwMTUzNzYyLDguMDY1ODYzMjMgMi42NTc0ODczNyw5LjUyNjU4MjY3IDQuNDUzNDkyNCw5LjUyNjU4MjY3IEM1Ljk3Njc5OTU2LDkuNTI2NTgyNjcgNy4yNTU0NzY5MSw4LjQ3NTc2NjY2IDcuNjA4NjU2NDEsNy4wNTcyMzMxNyBDNy4zMjQ4OTM4MSw3LjI0MzMyNjY3IDYuOTg1NzcwNjMsNy4zNTE1MTA1NSA2LjYyMTQ2MjI1LDcuMzUxNTEwNTUgQzUuNjIzNjgxNjgsNy4zNTE1MTA1NSA0LjgxNDgyMDcxLDYuNTM5OTk5NzUgNC44MTQ4MjA3MSw1LjUzODk1MDQ0IEM0LjgxNDgyMDcxLDQuNTc4MDMzNjcgNS41NjAxMjY0OSwzLjc5MTc2MjY2IDYuNTAyNDA4NjYsMy43MzAyNjMxNSBaIiBpZD0iZXllLWxlZnQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQuNDA1MDk3LCA2LjI2Mzk3NCkgcm90YXRlKC0yMy4wMDAwMDApIHRyYW5zbGF0ZSgtNC40MDUwOTcsIC02LjI2Mzk3NCkgIj48L3BhdGg+ICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMjAuMDY3OTYzNywxLjgwMDA0MjM5IEMxOS41MDg3MzI1LDEuMzQ0Mjg2NSAxOC43OTU2ODM2LDEuMDcxMTQ1NTMgMTguMDE5MDQ3NSwxLjA3MTE0NTUzIEMxNi4yMjMwNDI0LDEuMDcxMTQ1NTMgMTQuNzY3MDkyNywyLjUzMTg2NDk3IDE0Ljc2NzA5MjcsNC4zMzM3NTM3MiBDMTQuNzY3MDkyNyw2LjEzNTY0MjQ3IDE2LjIyMzA0MjQsNy41OTYzNjE5MSAxOC4wMTkwNDc1LDcuNTk2MzYxOTEgQzE5LjU0MjM1NDYsNy41OTYzNjE5MSAyMC44MjEwMzIsNi41NDU1NDU5IDIxLjE3NDIxMTUsNS4xMjcwMTI0MSBDMjAuODkwNDQ4OSw1LjMxMzEwNTkxIDIwLjU1MTMyNTcsNS40MjEyODk3OCAyMC4xODcwMTczLDUuNDIxMjg5NzggQzE5LjE4OTIzNjgsNS40MjEyODk3OCAxOC4zODAzNzU4LDQuNjA5Nzc4OTggMTguMzgwMzc1OCwzLjYwODcyOTY4IEMxOC4zODAzNzU4LDIuNjQ3ODEyOSAxOS4xMjU2ODE2LDEuODYxNTQxODkgMjAuMDY3OTYzNywxLjgwMDA0MjM5IFoiIGlkPSJleWUtcmlnaHQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE3Ljk3MDY1MiwgNC4zMzM3NTQpIHJvdGF0ZSgtMjMuMDAwMDAwKSB0cmFuc2xhdGUoLTE3Ljk3MDY1MiwgLTQuMzMzNzU0KSAiPjwvcGF0aD4gICAgICAgICAgICAgICAgICAgIDxlbGxpcHNlIGlkPSJPdmFsLTMiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE0LjM4MzU5NSwgMTEuODYwNjIxKSByb3RhdGUoLTIyLjAwMDAwMCkgdHJhbnNsYXRlKC0xNC4zODM1OTUsIC0xMS44NjA2MjEpICIgY3g9IjE0LjM4MzU5NDkiIGN5PSIxMS44NjA2MjA5IiByeD0iMSIgcnk9IjEuMDg3NTM2MDYiPjwvZWxsaXBzZT4gICAgICAgICAgICAgICAgICAgIDxlbGxpcHNlIGlkPSJPdmFsLTMtQ29weSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTEuMzc0Nzk0LCAxMi4yNjQyMzYpIHNjYWxlKC0xLCAxKSByb3RhdGUoLTIyLjAwMDAwMCkgdHJhbnNsYXRlKC0xMS4zNzQ3OTQsIC0xMi4yNjQyMzYpICIgY3g9IjExLjM3NDc5NDUiIGN5PSIxMi4yNjQyMzYzIiByeD0iMSIgcnk9IjEuMDg3NTM2MDYiPjwvZWxsaXBzZT4gICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0xNC4yNjc0MTQ2LDIxLjEwNDg5NyBDMTQuNDM1MTc3OCwyMi43NDg0IDEwLjg4MDc3ODEsMjIuMjM0NjcwMyAxMC4xOTk4MzYsMjIuMDEzMTc4MiBDOC43MjA1MDYyOSwyMS41MzE5OTIgNy45MTY3Njk0MywyMC41NzI4NjY3IDcuNzg4NjI1NDEsMTkuMTM1ODAyMiBDOC4xNzM4MTA0NiwyMC4wNzMyNjUxIDkuMzc4MTQzMTgsMjAuNjUxMjMwOSAxMS40MDE2MjM2LDIwLjg2OTY5OTQgQzEyLjQ2NzExOTksMjAuOTg0NzM3NiAxMy4zMTE0MDUyLDIwLjYwMjIwMjUgMTMuNzkyNDY4NCwyMC42ODM2NzUzIEMxNC4xNDU0ODU1LDIwLjc0MzQ2MjIgMTQuMjQ0NTgyMSwyMC44ODEyMTY3IDE0LjI2NzQxNDYsMjEuMTA0ODk3IFoiIGlkPSJQYXRoLTIiPjwvcGF0aD4gICAgICAgICAgICAgICAgPC9nPiAgICAgICAgICAgIDwvZz4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg==';
		//NOTE: call a function to load this page. Loading a file instead of a function doesn't execute the page hook suffix.
		$hook_suffix = add_menu_page( __( 'Push Monkey Lite + WooCommerce', 'push-monkey-light-woocommerce' ), __( 'Push Monkey Lite + WooCommerce', 'push-monkey-light-woocommerce' ), 'manage_options', 'pm-light-woocommerce-main-config', array( $this, 'pm_light_woocommerce_submenu_statistics' ), $icon_svg );
		add_action( 'load-' . $hook_suffix , array( $this, 'pm_light_woocommerce_settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'pm_light_woocommerce_enqueue_styles_main' ) );

		$hook_suffix = add_submenu_page( 'pm-light-woocommerce-main-config', __( 'Settings', 'push-monkey-light-woocommerce' ), __( 'Settings', 'push-monkey-light-woocommerce' ),'manage_options', 'pm-light-woocommerce', array( $this, 'pm_light_woocommerce_submenu_woocommerce' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'pm_light_woocommerce_settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'pm_light_woocommerce_enqueue_styles_main' ) );
	}

	/**
	 * Render the Settings Screens
	 */
	function pm_light_woocommerce_submenu_statistics() {

		$this->pm_light_woocommerce_settings_screen_api( plugin_dir_path( __FILE__ ) . '../templates/pages/settings/statistics.php' );
	}

	function pm_light_woocommerce_submenu_woocommerce() {

		$this->pm_light_woocommerce_settings_screen_api( plugin_dir_path( __FILE__ ) . '../templates/pages/settings/woo-info.php' );
	}

	/**
	 * The Settings Screen API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function pm_light_woocommerce_settings_screen_api( $template_name ) {

		$website_name_key = self::WEBSITE_NAME_KEY;
		$registered = false;

		if ( isset( $_GET['pm_light_woocommerce_registered'] ) && isset( $_GET['pm_light_woocommerce_package_pending'] ) ) {

			$this->sign_in_error = __( 'You have signed up and we will verify your account soon.', 'push-monkey-light-woocommerce' );
		} else if ( isset( $_GET['pm_light_woocommerce_registered'] ) ) {

			$register_action = ( $_GET['pm_light_woocommerce_registered'] == '1' );
			$registered = sanitize_text_field( $register_action );
			$account_key = sanitize_text_field( wp_slash( $_GET['pm_light_woocommerce_account_key'] ) );
			$this->pm_light_woocommerce_sign_in( $account_key );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$signed_in = $this->pm_light_woocommerce_signed_in();

		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		$welcome_status_enabled = true;
		$welcome_status_message = "";
		if ( $this->pm_light_woocommerce_signed_in() ) {

			$has_account_key = true;
			$account_key = $this->pm_light_woocommerce_account_key();
			$output = $this->apiClient->pm_light_woocommerce_get_stats( $account_key );
			$plan_response = $this->apiClient->pm_light_woocommerce_get_plan_name( $this->pm_light_woocommerce_account_key() );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
			$welcome_status = $this->apiClient->pm_light_woocommerce_get_welcome_message_status( $this->pm_light_woocommerce_account_key() );
			if ( $welcome_status != false  && is_array( $welcome_status ) ) {

				$welcome_status_enabled = $welcome_status["enabled"];
				$welcome_status_message = $welcome_status["message"];
			}
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = esc_url( $this->apiClient->endpointURL . '/password_reset' );
		$return_url = esc_url( admin_url( 'admin.php?page=pm-light-woocommerce-main-config' ) );
		$website_name = esc_html( $this->pm_light_woocommerce_website_name() );
		$website_url = esc_url( site_url() );
		$logout_url = esc_url( admin_url( 'admin.php?page=pm-light-woocommerce-main-config&logout=1' ) );
		$upgrade_url = esc_url( $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin' );
		$is_subscription_version = esc_html( $this->pm_light_woocommerce_is_saas() );

		// Notification Format
		$notification_format_image = plugins_url( 'img/default/notification-image-upload-placeholder.png', plugin_dir_path( __FILE__ ) );

		// WooCommerce
		$woocommerce_is_active = false;
		$woo_settings = NULL;
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$woocommerce_is_active = true;
			$woo_settings = $this->apiClient->pm_light_woocommerce_get_woo_settings( $this->pm_light_woocommerce_account_key() );
		}
		$woo_enabled = get_option( self::WOO_COMMERCE_ENABLED, false );

		// Reivew Notice
		$notice = null;
		if ( $this->review_notice->pm_light_woocommerce_can_display_notice() ) {

			$notice = new PM_Light_WooCommerce_Review_Notice_Controller( $this->pm_light_woocommerce_is_saas() );
		}

		if ( ! is_object( $output ) ) {
			$output = new stdClass();
			if ( ! isset( $output->subscribers ) ) {
						$output->subscribers = 0;
			}
			if ( ! isset( $output->total_subscribers ) ) {
				$output->total_subscribers = 0;
			}
			if ( ! isset( $output->subscribers_yesterday ) ) {
				$output->subscribers_yesterday = 0;
			}
			if ( ! isset( $output->subscribers_today ) ) {
				$output->subscribers_today = 0;
			}
			if ( ! isset( $output->sent_notifications ) ) {
				$output->sent_notifications = 0;
			}
			if ( ! isset( $output->top_countries ) ) {
				$output->top_countries = array();
			}
		}
		require_once( $template_name );
	}

	/**
	 * Action executed when the Settings Screen has loaded
	 */
	function pm_light_woocommerce_settings_screen_loaded() {

		remove_action( 'admin_notices', array( $this, 'pm_light_woocommerce_big_sign_in_notice' ) );
		remove_action( 'admin_notices', array( $this, 'pm_light_woocommerce_big_expired_plan_notice' ) );
		add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_big_welcome_notice' ) );
	}

	/**
	 * Action executed when a new post transitions its status.
	 *
	 * @param      string  $new_status  The new status
	 * @param      string  $old_status  The old status
	 * @param      array  $post        The post
	 */
	function pm_light_woocommerce_post_published( $new_status, $old_status, $post ) {

		$this->d->pm_light_woocommerce_debug(print_r($_POST, true));
		$this->d->pm_light_woocommerce_debug(1);
		if ( isset( $_POST['pm_light_woocommerce_opt_out'] ) ) {

		$this->d->pm_light_woocommerce_debug(2);
			$optout = sanitize_text_field( wp_unslash( $_POST['pm_light_woocommerce_opt_out'] ) );
			update_post_meta( $post->ID, '_pm_light_woocommerce_opt_out', $optout );

		} else if ( $old_status != 'future' ) {

			$this->d->pm_light_woocommerce_debug(3);
			delete_post_meta( $post->ID, '_pm_light_woocommerce_opt_out' );
		}
		
		if ( ! $this->pm_light_woocommerce_has_account_key() ) {

		$this->d->pm_light_woocommerce_debug(6);
			return;
		}
		if ( $new_status === 'future' ) {

				$this->d->pm_light_woocommerce_debug(12);
			return;
		}
		if ( $old_status == 'publish' || $new_status != 'publish' ) {
			$this->d->pm_light_woocommerce_debug(7);
			return;
		}
	
		if( ! $this->pm_light_woocommerce_can_verify_optout() && $old_status != 'future' ) {

		$this->d->pm_light_woocommerce_debug(9);
			return;
		}
		$optout = get_post_meta( $post->ID, '_pm_light_woocommerce_opt_out', true ) === 'on';
		$can_send_push = false;
		if( $optout != 'on' ) {
			$can_send_push = true;
		}

		$locations = array();
		if ( isset( $_POST['pm_light_woocommerce_post_locations'] ) ) {

			$locations = $_POST['pm_light_woocommerce_post_locations'];
		}
		if( $can_send_push ) {

		$this->d->pm_light_woocommerce_debug(11);
			$this->d->pm_light_woocommerce_debug( "can send push" );
			$image = NULL;
			if ( has_post_thumbnail( $post ) ) {

				$this->d->pm_light_woocommerce_debug( "post has thumb" );
				$featured_image_url = get_the_post_thumbnail_url( $post, 'large' );
				$this->d->pm_light_woocommerce_debug( $featured_image_url );
        $uploads_info = wp_upload_dir();
				if ( empty( $uploads_info["error"] ) ) {

					$this->d->pm_light_woocommerce_debug( "no error in upload dir" );
					$this->d->pm_light_woocommerce_debug( $uploads_info["basedir"] );
					$search_path = $uploads_info["basedir"] . "/*/" . basename( $featured_image_url );
					$found_files = $this->pm_light_woocommerce_rglob( $search_path );
					$this->d->pm_light_woocommerce_debug( print_r( $found_files, true ) );
					if ( !empty( $found_files) ) {

						$image = $found_files[0];
					}
				}
			}
			$title = $post->post_title;
			$body = strip_tags( strip_shortcodes( $post->post_content ) );
			$post_id = $post->ID;
			$this->pm_light_woocommerce_send_push_notification( $title, $body, $post_id, false, '', $locations, $image );
		}
	}

	/**
   * Recursively search a directory for a file.
   * Return: array of paths to the found files.
	 */
	function pm_light_woocommerce_rglob( $pattern, $flags = 0 ) {

    $files = glob( $pattern, $flags );
    foreach ( glob( dirname( $pattern ).'/*', GLOB_ONLYDIR|GLOB_NOSORT ) as $dir ) {

        $files = array_merge( $files, $this->pm_light_woocommerce_rglob( $dir.'/'.basename( $pattern ), $flags ) );
    }
    return $files;
	}

	/**
	 * Checks if the author did not manually disable push notification for
	 * this specific Post, by clicking on the opt-out checkbox.
	 */
	function pm_light_woocommerce_can_verify_optout() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['pm_light_woocommerce_meta_box_nonce'] ) ) {

			return false;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['pm_light_woocommerce_meta_box_nonce'], 'pm_light_woocommerce_meta_box' ) ) {

			return false;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

			return false;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {

			return false;
		}

		return true;
	}

	/**
	 * This is the actual point when the Push Monkey API is contacted and the notification is sent.
	 * @param string $title
	 * @param string $body
	 * @param string $url_args
	 * @param boolean $custom
	 */
	function pm_light_woocommerce_send_push_notification( $title, $body, $url_args, $custom, $segments, $locations, $image = NULL ) {

		$this->d->pm_light_woocommerce_debug("send_push_notification $title");
		$account_key = $this->pm_light_woocommerce_account_key();
		$clean_title = trim( $title );
		$clean_body = trim( $body );
		$payloadVars = 'title=' . $clean_title . '&body=' . $clean_body . '&url_args=' . $url_args;

		$maxPayloadLength = 150;
		$maxTitleLength = 40;
		$maxBodyLength = 100;
		if( strlen( $payloadVars ) > $maxPayloadLength ) {

			$clean_title = substr( $clean_title, 0, $maxTitleLength );
			$clean_body = substr( $clean_body, 0, $maxBodyLength );
		}
		$this->apiClient->pm_light_woocommerce_send_push_notification( $account_key, $title, $body, $url_args, $custom, $segments, $locations, $image );
	}

	/**
	 * Get the name of the website. Can be either from get_bloginfo() or
	 * from a previously saved value.
	 * @return string
	 */
	function pm_light_woocommerce_website_name() {

		$name = get_option( self::WEBSITE_NAME_KEY, false );
		if( ! $name ) {

			$name = get_bloginfo( 'name' );
		}
		return $name;
	}

	/**
	 * Get the Website Push ID stored.
	 * @return string
	 */
	function website_push_ID() {

		$stored_website_push_id = get_option( self::WEBSITE_PUSH_ID_KEY, false);

		if ( $stored_website_push_id ) {

			return $stored_website_push_id;
		}

		$resp = $this->apiClient->pm_light_woocommerce_get_website_push_ID( $this->pm_light_woocommerce_account_key() );
		if ( isset( $resp->website_push_id ) ) {

			update_option( self::WEBSITE_PUSH_ID_KEY, $resp->website_push_id );
			return $resp->website_push_id;
		}
		if ( isset( $resp->error ) ) {

			$this->error = $resp->error;
		}
		return '';
	}

	/**
	 * Enqueue all the JS files required.
	 */
	function pm_light_woocommerce_enqueue_scripts() {

		if ( ! is_admin() ) {

			if ( $this -> pm_light_woocommerce_signed_in() ) {

				$url = "https://www.getpushmonkey.com/sdk/config-" . $this->pm_light_woocommerce_account_key() . ".js";
				wp_enqueue_script( 'push-monkey-light-woocommerce-sdk', $url, array( 'jquery' ) );
			}
		} else {

			wp_enqueue_script( 'pm-light-woocommerce-jquery-ui', plugins_url( 'js/plugins/jquery/jquery-ui.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'pm-light-woocommerce-boostrap', plugins_url( 'js/plugins/bootstrap/bootstrap.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'pm-light-woocommerce-raphael', plugins_url( 'js/plugins/morris/raphael-min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'pm-light-woocommerce-morris', plugins_url( 'js/plugins/morris/morris.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'pm-light-woocommerce-icheck', plugins_url( 'js/plugins/icheck/icheck.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'pm-light-woocommerce-jvectormap', plugins_url( 'js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'pm-light-woocommerce-jvectormap-world', plugins_url( 'js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'pm-light-woocommerce-boostrap-colorpicker', plugins_url( 'js/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
		
			wp_enqueue_script( 'pm-light-woocommerce-boostrap-fileinput', plugins_url( 'js/plugins/bootstrap/bootstrap-file-input.js', plugin_dir_path( __FILE__ ) ), array('jquery') );			

			wp_enqueue_script( 'pm-light-woocommerce-push-widget', plugins_url( 'js/default/push-monkey-light-woocommerce-push-widget.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'pm-light-woo', plugins_url( 'js/default/push-monkey-light-woocommerce-woo.js', plugin_dir_path( __FILE__ ) ), array('jquery') );	

			wp_enqueue_script( 'pm-light-woocommerce-admin', plugins_url( 'js/default/push-monkey-light-woocommerce-admin.js', plugin_dir_path( __FILE__ ) ), array('jquery') );				

			wp_enqueue_script( 'pm-light-woocommerce-plugins', plugins_url( 'js/plugins.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'pm-light-woocommerce-actions', plugins_url( 'js/actions.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			$data = $this->pm_light_woocommerce_set_global_data();
			wp_enqueue_script( 'pm-light-woocommerce-dashboard', plugins_url( 'js/main.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_localize_script( 'pm-light-woocommerce-dashboard', 'global_data', $data );
			wp_enqueue_script( 'pm-light-woocommerce-dashboard' );
		}
	}

	/**
	 * Enqueue all the CSS required.
	 */
	function pm_light_woocommerce_enqueue_styles( $hook_suffix ) {

		if ( is_admin() ) {
			
			wp_enqueue_style( 'pm-light-woocommerce-styles', plugins_url( '/css/styles.css', plugin_dir_path( __FILE__ ) ) );
			wp_enqueue_style( 'pm-light-woocommerce-additional', plugins_url( '/css/additional.css', plugin_dir_path( __FILE__ ) ) );
		} else {

			wp_enqueue_style( 'pm-light-woocommerce-animate', plugins_url( 'css/default/animate.css', plugin_dir_path( __FILE__ ) ) );
			wp_enqueue_style( 'pm-light-woocommerce', plugins_url( 'css/default/push-monkey.css', plugin_dir_path( __FILE__ ) ) );
		}
	}

	/**
	 * Enqueue the CSS for the Settings page
	 */
	function pm_light_woocommerce_enqueue_styles_main( ) {
		wp_enqueue_style( 'pm-light-woocommerce-config-style', plugins_url( 'css/main-config.css', plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * Multiple manifest js admin notice
	 */
	function pm_light_woocommerce_manifest_js() {
	  if ( ( isset( $_GET['page'] ) ) && ( file_exists( get_template_directory() . '/manifest.json' ) ) && ( is_admin() ) && ( $_GET['page'] == "pm-light-woocommerce-main-config" ) ) {

	  	$manifest_json = file_get_contents( get_template_directory() . '/manifest.json' );
			$json_array = json_decode( $manifest_json, true );
	    
	    if ( ( ! array_key_exists( 'gcm_sender_id', $json_array ) ) || ( ! array_key_exists( 'gcm_user_visible_only', $json_array ) ) ) {
	    	$pm_manifest_js = esc_url( plugins_url( 'assets/manifest.json', plugin_dir_path( __FILE__ ) ) );
	    	echo '<div class="notice notice-warning is-dismissible"> <p>' . __( 'Check manifest.json file of the', 'push-monkey-light-woocommerce' ) . ' <a href="' . $pm_manifest_js . '" target="_blank">' . __( 'plugin here', 'push-monkey-light-woocommerce' ) . '</a>' . __( '. And copy the "gcm_sender_id": "some-id","gcm_user_visible_only": true in your theme\'s manifest.json' ,'push-monkey-light-woocommerce' ) . '</p></div>';
	    }
	  }
	}

	/**
	* Add a custom <link> tag for the manifest
	*/
	function pm_light_woocommerce_sw_meta() {

			if ( ! file_exists( get_template_directory() . '/manifest.json' ) ) {
	    		echo '<link rel="manifest" href="' . esc_url( plugins_url( 'assets/manifest.json', plugin_dir_path( __FILE__ ) ) ) . '">';
			}

	}

	/**
	 * Set global data for JavaScript scripts
	 */
	function pm_light_woocommerce_set_global_data() {

		if ( $this->pm_light_woocommerce_signed_in() ) {

			$account_key = $this->pm_light_woocommerce_account_key();
			$output = $this->apiClient->pm_light_woocommerce_get_stats( $account_key );
			return array(
				'stats' => $output
			);
		} else {

			return array(
				'stats' => null
			);
		}
	}

	/**
	 * Central point to process forms.
	 */
	function pm_light_woocommerce_process_forms() {
		
		if ( isset( $_GET['logout'] ) ) {

			$this->pm_light_woocommerce_sign_out();
			wp_redirect( esc_url( admin_url( 'admin.php?page=pm-light-woocommerce-main-config' ) ) );
			exit;
		}

		if( isset( $_POST['pm_light_woocommerce_main_config_submit'] ) ) {

			$this->pm_light_woocommerce_process_main_config( $_POST );
		} else if ( isset( $_POST['pm_light_woocommerce_sign_in'] ) ) {

			$this->pm_light_woocommerce_process_sign_in( $_POST );
		} else if ( isset( $_POST['pm_light_woocommerce_woo_settings'] ) ) {

			$this->pm_light_woocommerce_process_woo_settings( $_POST, $_FILES );
		}
	}

	/**
	 * Process the Sign In form.
	 */
	function pm_light_woocommerce_process_sign_in( $post ) {

		$account_key = sanitize_text_field( wp_unslash( $post['account_key'] ) );
		if ( ! strlen( $account_key ) ) {

			$this->sign_in_error = __( 'The two fields can\'t be empty.', 'push-monkey-light-woocommerce' );
			return;
		}

		$signed_in = $this->pm_light_woocommerce_sign_in( $account_key );
		if ( $signed_in ) {

			wp_redirect( esc_url( admin_url( 'admin.php?page=pm-light-woocommerce-main-config' ) ) );
			exit;
		}
	}

	/**
	 * Process the form with the website name field, from the Settings page.
	 */
	function pm_light_woocommerce_process_main_config( $post ) {

		$website_name = sanitize_text_field( wp_unslash( $post[self::WEBSITE_NAME_KEY] ) );
		if( $website_name ) {

			update_option( self::WEBSITE_NAME_KEY, $website_name );
		}
	}

	/**
	 * Process the WooCommerce settings form.
	 */
	function pm_light_woocommerce_process_woo_settings( $post, $files ) {

		$this->d->pm_light_woocommerce_debug("Process Woo settings.");
		$this->d->pm_light_woocommerce_debug( print_r( $post, true ) );
		$account_key = $this->pm_light_woocommerce_account_key();
		$title = sanitize_text_field( wp_unslash( $post['abandoned_cart_title'] ) );
		$message = sanitize_text_field( $post['abandoned_cart_message'] );
		$delay = sanitize_text_field( $post['abandoned_cart_delay'] );
		$image = NULL;
		$image_path = NULL;
		// File sanitize
		$sanitize_file = $this->pm_light_woocommerce_sanitize_file_name( $files["abandoned_cart_image"]["name"] );
		if ( ! empty( $sanitize_file ) ) {

			$image_path = $files["abandoned_cart_image"]["tmp_name"];
			$image = $sanitize_file;
		}
		$woo_enabled_field = false;
		if ( isset( $post['pm_light_woocommerce_woo_enabled'] ) ) {

			$woo_enabled_field = true;
 		}
 		update_option( self::WOO_COMMERCE_ENABLED, $woo_enabled_field );
		$updated = $this->apiClient->pm_light_woocommerce_update_woo_settings( $account_key, $delay, $title, $message, $image_path, $image );
		if ( $updated ) {
			add_action( 'admin_notices', array( $this, 'pm_light_woocommerce_woo_settings_notice' ) );
			return;
		}
	}

	/**
	 * Renders the admin notice that prompts the user to sign in.
	 */
	function pm_light_woocommerce_big_sign_in_notice() {

		$image_url = esc_url( plugins_url( 'img/plugin-big-message-image.png', plugin_dir_path( __FILE__ ) ) );
		$settings_url = esc_url( admin_url( 'admin.php?page=pm-light-woocommerce-main-config' ) );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push-monkey-light-woocommerce-big-message.php' );
	}

	/**
	 * Admin notice to confirm that the Woo settings have been saved.
	 */
	function pm_light_woocommerce_woo_settings_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>' . __( 'Abandoned cart settings saved! *woohoo*', 'push-monkey-light-woocommerce' ) . '</p></div>';
	}	

	/**
	 * Renders a notice to say that the chosen plan is expired.
	 */
	function pm_light_woocommerce_big_expired_plan_notice() {

		if ( ! $this->pm_light_woocommerce_signed_in() ) {

			return;
		}

		$account_key = $this->pm_light_woocommerce_account_key();
		$plan_response = $this->apiClient->pm_light_woocommerce_get_plan_name( $account_key );
		$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		if ( ! $plan_expired ) {

			return;
		}
		$stats = $this->apiClient->pm_light_woocommerce_get_stats( $account_key );
		if ( ! isset( $stats->subscribers ) ) {

			return;
		}

		$subscribers = $stats->subscribers;
		$upgrade_url = esc_url( $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin' );
		$image_url = esc_url( plugins_url( 'img/plugin-big-expiration-notice.png', plugin_dir_path( __FILE__ ) ) );
		$settings_url = esc_url( admin_url( 'admin.php?page=pm-light-woocommerce-main-config' ) );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push-monkey-light-woocommerce-big-expiration-notice.php' );
	}

	/**
	 * Checks the Push Monkey API to see if the current price plan expired.
	 * @return boolean
	 */
	function pm_light_woocommerce_can_show_expiration_notice() {

		if ( ! $this->pm_light_woocommerce_signed_in() ) {

			return false;
		}
		$plan_response = $this->apiClient->pm_light_woocommerce_get_plan_name( $this->pm_light_woocommerce_account_key() );
		$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		return $plan_expired;
	}

	/**
	 * Renders an admin notice asking the user for an upgrade.
	 */
	function pm_light_woocommerce_big_upsell_notice() {

		global $hook_suffix;
		if ( $hook_suffix != 'plugins.php' ) {

			return;
		}

		if ( ! $this->pm_light_woocommerce_signed_in() ) {

			return;
		}

		$plan_response = $this->apiClient->pm_light_woocommerce_get_plan_name( $this->pm_light_woocommerce_account_key() );
		$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;

		$pm_light_woocommerce_us_notice_cookie = isset( $_COOKIE['pm_light_woocommerce_us_notice'] ) ? $_COOKIE['pm_light_woocommerce_us_notice'] : false;

		if ( $pm_light_woocommerce_us_notice_cookie ) {

			return;
		}

		if ( ! $plan_expired && $plan_can_upgrade ) {

			$upgrade_url = esc_url( $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=us-notice' );
			$price_plans = esc_url( $this->apiClient->endpointURL . '/#plans' );
			$image_url = esc_url( plugins_url( 'img/plugin-big-message-image.png', plugin_dir_path( __FILE__ ) ) );
			$close_url = esc_url( plugins_url( 'img/banner-close-dark.png', plugin_dir_path( __FILE__ ) ) );
			require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push-monkey-light-woocommerce-upsell-notice.php' );
		}
	}

	/**
	 * Renders an admin notice for a first time user. Displays a few useful links to get started.
	 */
	function pm_light_woocommerce_big_welcome_notice() {

		$pm_light_woocommerce_welcome_notice_cookie = isset( $_COOKIE['pm_light_woocommerce_welcome-notice'] ) ? $_COOKIE['pm_light_woocommerce_welcome_notice'] : false;

		if ( ! $this->pm_light_woocommerce_signed_in() ) {

			return;
		}

		if ( $pm_light_woocommerce_welcome_notice_cookie ) {

			return;
		}

		$image_url = esc_url( plugins_url( 'img/logo-party.png', plugin_dir_path( __FILE__ ) ) );
		$close_url = esc_url( plugins_url( 'img/banner-close-dark.png', plugin_dir_path( __FILE__ ) ) );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push-monkey-light-woocommerce-welcome-notice.php' );
	}

	/**
	 * File name sanitize.
	 *
	 * @param 	str  $pm_filename  The pm filename.
	 *
	 * @return  str  ( return file name )
	 */
	function pm_light_woocommerce_sanitize_file_name( $pm_filename ) {
		// Convert to ASCII
		$pm_sanitized_filename = remove_accents( $pm_filename );

		// Standard replacements.
		$invalid = array(
			' ' 	=> '-',
			'%20' => '-',
			'_' 	=> '-'
		);
		// Remore whitespace, underscore.
		$pm_sanitized_filename = str_replace( array_keys( $invalid ), array_values( $invalid ), $pm_sanitized_filename );
		// Remove all non-alphanumeric except.
		$pm_sanitized_filename = preg_replace( '/[^A-Za-z0-9-\. ]/', '', $pm_sanitized_filename );
		// Remove all but last.
		$pm_sanitized_filename = preg_replace( '/\.(?=.*\.)/', '', $pm_sanitized_filename );
		// Replace any more than one - in a row.
		$pm_sanitized_filename = preg_replace( '/-+/', '-', $pm_sanitized_filename );
		// Remove last - if at the end.
		$pm_sanitized_filename = str_replace( '-.', '.', $pm_sanitized_filename );
		// Lowercase.
		$pm_sanitized_filename = strtolower( $pm_sanitized_filename );
		// Return filename.
		return $pm_sanitized_filename;
	}

}