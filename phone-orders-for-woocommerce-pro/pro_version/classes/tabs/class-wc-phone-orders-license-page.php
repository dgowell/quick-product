<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_License_Page_Pro extends WC_Phone_Orders_Admin_Abstract_Page {
	public $title;
	public $priority = 30;
	protected $tab_name = 'license';

	public function __construct() {

		parent::__construct();

		$this->title = __( 'License', 'phone-orders-for-woocommerce' );

		$this->error_messages = array(
		    'missing'               => __( 'not found', 'phone-orders-for-woocommerce' ),
		    'license_not_activable' => __( 'is not activable', 'phone-orders-for-woocommerce' ),
		    'revoked'               => __( 'revoked', 'phone-orders-for-woocommerce' ),
		    'no_activations_left'   => __( 'no activations left', 'phone-orders-for-woocommerce' ),
		    'expired'               => __( 'expired', 'phone-orders-for-woocommerce' ),
		    'key_mismatch'          => __( 'key mismatch', 'phone-orders-for-woocommerce' ),
		    'invalid_item_id'       => __( 'invalid item ID', 'phone-orders-for-woocommerce' ),
		    'item_name_mismatch'    => __( 'item name mismatch', 'phone-orders-for-woocommerce' ),
		);
	}

	public function enqueue_scripts() {
	}

	public function action() {
	}

	public function render() {

		$license = get_option( 'edd_wpo_license_key', "" );
		$status  = get_option( 'edd_wpo_license_status' );
		$error   = get_option( 'edd_wpo_license_error' );
		if ( isset( $this->error_messages[ $error ] ) ) {
			$error = $this->error_messages[ $error ];
		}

		$this->tab_data = array(
			'title'                            => __('Plugin License', 'phone-orders-for-woocommerce'),
			'licenseKeyLabel'                  => __('License Key', 'phone-orders-for-woocommerce'),
			'licenseKeyNote'                   => __('look for it inside purchase receipt (email)', 'phone-orders-for-woocommerce'),
			'licenseActiveTitle'               => __('License is active', 'phone-orders-for-woocommerce'),
			'licenseInactiveTitle'             => __('License is inactive', 'phone-orders-for-woocommerce'),
			'activeLicenseSubmitButtonTitle'   => __('Deactivate License', 'phone-orders-for-woocommerce'),
			'inactiveLicenseSubmitButtonTitle' => __('Activate License', 'phone-orders-for-woocommerce'),
			'licenseKey'                       => $license,
			'licenseStatus'                    => $status,
			'errorMessage'                     => $error ? $error : '',
			'referrer'                         => wp_get_referer(),
			'tabName'                          => 'license',
                        'licenseHelp'                      => $this->get_tab_license_help_html(),
		);
		?>
                    <tab-license v-bind="<?php echo esc_attr(json_encode($this->tab_data)) ?>"></tab-license>
		<?php
	}

        protected function get_tab_license_help_html() {

            $site_url           = 'https://algolplus.com';
            $site_link_html     = sprintf('<a target="_blank" href="%s">%s</a>', $site_url, $site_url);
            $account_url        = 'https://algolplus.com/plugins/my-account';
            $account_link_html  = sprintf('<a target="_blank" href="%s">%s</a>', $account_url, $account_url);
            $dashboard_link     = sprintf('<a target="_blank" href="%s">%s</a>', admin_url( 'update-core.php' ), __(">Dashboard > Updates", 'phone-orders-for-woocommerce' ) );

            return '<div id="license_help_text">'.
                        '<h3 class="license_header">'.
                            __( 'Licenses', 'phone-orders-for-woocommerce' ).
                        '</h3>'.
                        '<div class="license_paragraph">'.
                            sprintf ( __( 'The license key you received when completing your purchase from %s will grant you access to updates until it expires.', 'phone-orders-for-woocommerce' ), $site_link_html ).
                            '<br>'.
                            __( 'You do not need to enter the key below for the plugin to work, but you will need to enter it to get automatic updates.', 'phone-orders-for-woocommerce' ).
                        '</div>'.
                        '<div class="license_paragraph">'.
                            sprintf( __( "If you're seeing a red message telling you that your key isn't valid or is out of installs, %s visit %s to manage your installs or renew / upgrade your license.", 'phone-orders-for-woocommerce'),"<br>", $account_link_html ).
                        '</div>'.
                        '<div class="license_paragraph">'.
                            sprintf( __( 'Not seeing an update but expecting one? In WordPress, go to %s and click "Check Again".', 'phone-orders-for-woocommerce'), $dashboard_link ).
                        '</div>'.
                    '</div>';

        }

        protected function ajax_activate_license( $request ) {

            $woe_edd = WC_Phone_Orders_EDD::getInstance();

            if ( $woe_edd->edd_wpo_activate_license() === false) {
                return $this->wpo_send_json_success(array(
                    'status' => false,
                    'error'  => __('Connection error', 'phone-orders-for-woocommerce'),
                ));
            }

            $error = get_option( 'edd_wpo_license_error' );

            if ( isset( $this->error_messages[ $error ] ) ) {
                $error = $this->error_messages[ $error ];
            }

            return $this->wpo_send_json_success(array(
                'status' => get_option( 'edd_wpo_license_status' ),
                'error'  => $error ? $error : '',
            ));
        }

        protected function ajax_check_license( $request ) {

            $woe_edd = WC_Phone_Orders_EDD::getInstance();

	    if( ! get_transient('edd_wpo_license_key_checked') ) {

		if ( $woe_edd->edd_wpo_check_license() === false ) {
		    return $this->wpo_send_json_success(array(
			'status' => false,
			'error'  => __('Connection error', 'phone-orders-for-woocommerce'),
		    ));
		}

		set_transient( 'edd_wpo_license_key_checked', 1, 1 * HOUR_IN_SECONDS );
	    }

            $error = get_option( 'edd_wpo_license_error' );

            if ( isset( $this->error_messages[ $error ] ) ) {
                $error = $this->error_messages[ $error ];
            }

            return $this->wpo_send_json_success(array(
                'status' => get_option( 'edd_wpo_license_status' ),
                'error'  => $error ? $error : '',
            ));
        }

        protected function ajax_deactivate_license( $request ) {

            $woe_edd = WC_Phone_Orders_EDD::getInstance();

            if ( $woe_edd->edd_wpo_deactivate_license() === false) {
                return $this->wpo_send_json_success(array(
                    'status'  => false,
                    'error'   => __('Connection error', 'phone-orders-for-woocommerce'),
                ));
            }

            $error = get_option( 'edd_wpo_license_error' );

            if ( isset( $this->error_messages[ $error ] ) ) {
                $error = $this->error_messages[ $error ];
            }

            return $this->wpo_send_json_success(array(
                'status'  => get_option( 'edd_wpo_license_status' ),
                'error'   => $error ? $error : '',
            ));
        }
}