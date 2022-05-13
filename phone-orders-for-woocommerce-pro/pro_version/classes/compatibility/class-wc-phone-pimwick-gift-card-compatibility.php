<?php
// class loaded in ADMIN areay only

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class WC_Phone_Pimwick_Gift_Card_Compatibility {

    protected $errors = array();

    public function __construct() {

	$this->errors = array();

	add_action('wp_loaded', function () {

	    if ( ! class_exists( 'PW_Gift_Cards' ) ) {
		return;
	    }

	    add_filter('wpo_gift_card_enabled', '__return_true');

	    add_action('wpo_gift_card_process_cards', array($this, 'process_cards'));
	    add_filter('wpo_gift_card_cards', array($this, 'get_applied_cards'));

	    add_filter('wpo_gift_card_errors', array($this, 'get_errors'));

	    add_action('wpo_gift_card_remove_order_lines', array($this, 'remove_order_lines'));
	    add_action('wpo_gift_card_create_order_lines', array($this, 'create_order_lines'), 10, 2);

	    add_filter('wpo_load_order_data', array($this, 'add_load_order_data'), 10, 3);
	});
    }

    public function process_cards($cart_data) {

	$this->remove_all_gift_cards();

	if ( ! empty( $cart_data['gift_card']['cards'] ) ) {
	    $this->add_gift_cards($cart_data['gift_card']['cards']);
	}

	//to fix recalculate totals
	if ( property_exists( WC()->cart, 'pwgc_calculated_total' ) ) {
            unset(WC()->cart->pwgc_calculated_total);
        }
    }

    public function add_gift_cards($cards) {

	global $pw_gift_cards_redeeming;

	foreach ( $cards as $card) {

	    $result = $pw_gift_cards_redeeming->add_gift_card_to_session( $card['card_number'] );

	    if ( $result === true ) {
		$gift_card = new PW_Gift_Card( $card['card_number'] );
		if ( $gift_card->get_balance() <= 0 ) {
		    $this->errors[] = __( 'This gift card has a zero balance.', 'pw-woocommerce-gift-cards' );
		}
	    } else {
		$this->errors[] = $result;
	    }
	}
    }

    public function remove_all_gift_cards() {
	$session_data = (array) WC()->session->get( PWGC_SESSION_KEY );
	$session_data['gift_cards'] = array();
        WC()->session->set( PWGC_SESSION_KEY, $session_data );
    }

    public function get_applied_cards($cards) {

	$session_data = (array) WC()->session->get( PWGC_SESSION_KEY );

	if ( ! isset( $session_data['gift_cards'] ) ) {
	    return $cards;
	}

	foreach ( $session_data['gift_cards'] as $card_number => $discount_amount ) {

	    $pw_gift_card = new PW_Gift_Card( $card_number );

	    if ( ! $pw_gift_card->get_id() ) {
		continue;
	    }

	    $balance = apply_filters( 'pwgc_to_current_currency', $pw_gift_card->get_balance() ) - $discount_amount;
	    $balance = apply_filters( 'pwgc_remaining_balance_cart', $balance, $pw_gift_card );

	    $title  = '<span class="wpo-gift-card-label__card-number">' . $pw_gift_card->get_number() . '</span> <br/>';

	    $title .= '<span class="wpo-gift-card-label__balance">' . sprintf( __( 'Remaining balance is %s', 'pw-woocommerce-gift-cards' ), wc_price( $balance ) )  . '</span>';

	    if ( $pw_gift_card->has_expired() ) {
		$title  .= '<br/><span class="wpo-gift-card-label__expired">' . __( 'Expired', 'pw-woocommerce-gift-cards' ) . '</span>';
	    }

	    $cards[] = array(
		'card_number'	=> $pw_gift_card->get_number(),
		'amount'	=> $discount_amount,
		'title'		=> '<span class="wpo-gift-card-label">' . $title . '</span>',
	    );

	}

	return $cards;
    }

    public function get_errors($errors) {
	return array_merge($errors, $this->errors);
    }

    public function remove_order_lines($order) {

	global $pw_gift_cards_purchasing;
	global $pw_gift_cards_redeeming;

	$pw_gift_cards_purchasing->deactivate_gift_cards_from_order($order->get_id(), $order, "order_id: {$order->get_id()} phone orders edit");
	$pw_gift_cards_redeeming->credit_gift_cards($order->get_id(), $order, "order_id: {$order->get_id()} phone orders edit");

	$order->remove_order_items( 'pw_gift_card' );
    }

    public function create_order_lines($order, $cart) {

	$session_data = (array) WC()->session->get( PWGC_SESSION_KEY );

	if ( !isset( $session_data['gift_cards'] ) ) {
            return;
        }

        foreach ( $session_data['gift_cards'] as $card_number => $amount ) {
            $pw_gift_card = new PW_Gift_Card( $card_number );
            if ( $pw_gift_card->get_id() ) {

                $item = new WC_Order_Item_PW_Gift_Card();

                $item->set_props( array(
                    'card_number'   => $pw_gift_card->get_number(),
                    'amount'        => apply_filters( 'pwgc_to_default_currency', $amount ),
                ) );

                $order->add_item( $item );
            }
        }

    }

    public function add_load_order_data($result, $order, $is_edit) {

	global $pw_gift_cards_purchasing;
	global $pw_gift_cards_redeeming;

	$cards = array();

	foreach( $order->get_items( 'pw_gift_card' ) as $line ) {

	    $cards[] = array(
		'card_number' => $line->get_card_number()
	    );

	    if ($is_edit) {
		$pw_gift_cards_purchasing->deactivate_gift_cards_from_order($order->get_id(), $order, "order_id: {$order->get_id()} phone orders edit");
		$pw_gift_cards_redeeming->credit_gift_cards($order->get_id(), $order, "order_id: {$order->get_id()} phone orders edit");
	    }
	}

	$result['cart']['gift_card'] = array(
	    'cards' => $cards,
	);

	return $result;
    }

}