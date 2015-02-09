<?php
/**
 * Plugin Name: WooCommerce Custom Sales Report
 * Plugin URI: http://www.wpquestions.com/question/showLoggedIn/id/10596
 * Author: Reigel Gallarde
 * Author URI: http://reigelgallarde.me
 * Version: 0.1
 * Tested up to: 4.0
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
 
 // Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) exit;

if (!function_exists('is_plugin_active'))
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	
add_action( 'wp_ajax_reports', 'reports_callback' );

function reports_callback() {
	// turn off error reporting for this ajax request...
	error_reporting( error_reporting() & ~E_NOTICE );

		$report = new My_Reports();
		$report->output_report();
	
	wp_die(); // this is required to terminate immediately and return a proper response
}

include_once( plugin_dir_path('woocommerce/woocommerce.php').'includes/admin/reports/class-wc-admin-report.php' );
include_once( plugin_dir_path('woocommerce/woocommerce.php').'includes/admin/reports/class-wc-report-sales-by-date.php' );
class My_Reports extends WC_Report_Sales_By_Date {
	
	public function output_report() {

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = '7day';
		}

		$this->calculate_current_range( $current_range );

		if ( $legends = $this->get_chart_legend() ) : 
			preg_match("/<span[^>]*class=\"amount\">(.*?)<\\/span>/si", $legends[0]['title'], $match);
			$sales = $match[1]; 
			preg_match("/<strong>(.*?)<\\/strong>/si", $legends[2]['title'], $match);
			$orders = $match[1];
			$currency = get_woocommerce_currency();
			echo json_encode(array('orders'=>$orders,'sales'=>$sales,'currency'=>$currency));			
		endif;
	}
}	
	
} else {
	add_action( 'admin_notices', 'woocommerce_not_active' );
}
if (!function_exists('woocommerce_not_active')) {
	function woocommerce_not_active() {
		$message = sprintf(
			__( '%sWooCommerce Ultimate Vendor.%s This version requires WooCommerce 2.1 or newer. Please %sinstall WooCommerce version 2.1 or newer%s', 'woorei' ),
			'<strong>',
			'</strong>',
			'<a href="' . admin_url( 'plugin-install.php' ) . '?tab=search&s=woocommerce">',
			'&nbsp;&raquo;</a>' 
		);
		echo sprintf( '<div class="error"><p>%s</p></div>', $message );
	}
}
