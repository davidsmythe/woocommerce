<?php
/**
 * Customer Note Order Email
 *
 * Customer note emails are sent when you add a note to an order.
 *
 * @class 		WC_Email_Customer_Note
 * @version		1.7.0
 * @package		WooCommerce/Classes/Emails
 * @author 		WooThemes
 * @extends 	WC_Email
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Email_Customer_Note extends WC_Email {

	var $customer_note;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		$this->id 				= 'customer_note';
		$this->title 			= __( 'Customer note', 'woocommerce' );
		$this->description		= __( 'Customer note emails are sent when you add a note to an order.', 'woocommerce' );

		$this->template_html 	= 'emails/customer-note.php';
		$this->template_plain 	= 'emails/plain/customer-note.php';

		$this->subject 			= __( 'Note added to your {blogname} order from {order_date}', 'woocommerce');
		$this->heading      	= __( 'A note has been added to your order', 'woocommerce');

		// Triggers
		add_action( 'woocommerce_new_customer_note_notification', array( &$this, 'trigger' ) );

		// Call parent constuctor
		parent::__construct();
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $args ) {
		global $woocommerce;

		if ( $args ) {

			$defaults = array(
				'order_id' 		=> '',
				'customer_note'	=> ''
			);

			$args = wp_parse_args( $args, $defaults );

			extract( $args );

			$this->object 		= new WC_Order( $order_id );
			$this->recipient	= $this->object->billing_email;
			$this->customer_note = $customer_note;

			$this->find[] = '{order_date}';
			$this->replace[] = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );

			$this->find[] = '{order_number}';
			$this->replace[] = $this->object->get_order_number();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() )
			return;

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		woocommerce_get_template( $this->template_html, array(
			'order' 		=> $this->object,
			'email_heading' => $this->get_heading(),
			'customer_note' => $this->customer_note
		) );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		woocommerce_get_template( $this->template_plain, array(
			'order' 		=> $this->object,
			'email_heading' => $this->get_heading(),
			'customer_note' => $this->customer_note
		) );
		return ob_get_clean();
	}
}