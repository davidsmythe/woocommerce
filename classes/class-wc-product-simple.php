<?php
/**
 * Simple Product Class
 *
 * The default product type kinda product.
 *
 * @class 		WC_Product_Simple
 * @version		1.7.0
 * @package		WooCommerce/Classes/Products
 * @author 		WooThemes
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Product_Simple extends WC_Product {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $product
	 * @param array $args Contains arguments to set up this product
	 */
	function __construct( $product, $args ) {

		parent::__construct( $product );

		$this->product_type = 'simple';

		// Load data from custom fields
		$this->load_product_data( array(
			'sku'                   => '',
			'downloadable'          => 'no',
			'virtual'               => 'no',
			'price'                 => '',
			'visibility'            => 'hidden',
			'stock'                 => 0,
			'stock_status'          => 'instock',
			'backorders'            => 'no',
			'manage_stock'          => 'no',
			'sale_price'            => '',
			'regular_price'         => '',
			'weight'                => '',
			'length'                => '',
			'width'                 => '',
			'height'                => '',
			'tax_status'            => 'taxable',
			'tax_class'             => '',
			'upsell_ids'            => array(),
			'crosssell_ids'         => array(),
			'sale_price_dates_from' => '',
			'sale_price_dates_to'   => '',
			'featured'              => 'no',
			'sold_individually'     => 'no'
		) );

		$this->check_sale_price();
	}


    /**
     * Checks sale data to see if the product is due to go on sale/sale has expired, and updates the main price.
     *
     * @access public
     * @return bool
     */
    function check_sale_price() {

    	if ( $this->sale_price_dates_from && $this->sale_price_dates_from < current_time('timestamp') ) {

    		if ( $this->sale_price && $this->price !== $this->sale_price ) {

    			// Update price
    			$this->price = $this->sale_price;
    			update_post_meta( $this->id, '_price', $this->price );

    			// Grouped product prices and sale status are affected by children
    			$this->grouped_product_sync();
    		}

    	}

    	if ( $this->sale_price_dates_to && $this->sale_price_dates_to < current_time('timestamp') ) {

    		if ( $this->regular_price && $this->price !== $this->regular_price ) {

    			$this->price = $this->regular_price;
    			update_post_meta( $this->id, '_price', $this->price );

				// Sale has expired - clear the schedule boxes
				update_post_meta( $this->id, '_sale_price', '' );
				update_post_meta( $this->id, '_sale_price_dates_from', '' );
				update_post_meta( $this->id, '_sale_price_dates_to', '' );

				// Grouped product prices and sale status are affected by children
    			$this->grouped_product_sync();
			}

    	}
    }


	/**
	 * Sync grouped products with the childs lowest price (so they can be sorted by price accurately).
	 *
	 * @access public
	 * @return void
	 */
	function grouped_product_sync() {
		global $wpdb, $woocommerce;

		if ( ! $this->get_parent() ) return;

		$children_by_price = get_posts( array(
			'post_parent'    => $this->get_parent(),
			'orderby'        => 'meta_value_num',
			'order'          => 'asc',
			'meta_key'       => '_price',
			'posts_per_page' => 1,
			'post_type'      => 'product',
			'fields'         => 'ids'
		));
		if ( $children_by_price ) {
			foreach ( $children_by_price as $child ) {
				$child_price = get_post_meta( $child, '_price', true );
				update_post_meta( $post_parent, '_price', $child_price );
			}
		}

		$woocommerce->clear_product_transients( $this->id );
	}
}