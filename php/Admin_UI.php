<?php
namespace Modern_Tribe\Support_Team\Happy_Converter;

use Modern_Tribe\Support_Team\Happy_Converter\Sources\Data_Source;
use Modern_Tribe\Support_Team\Happy_Converter\Utilities\View;

class Admin_UI {
	private $hook_suffix;

	public function setup() {
		add_action( 'admin_menu', [ $this, 'add_menu_entry' ] );
		add_action( 'admin_head-tools_page_tec-happy-converter', [ $this, 'assets' ] );
		add_action( 'wp_ajax_tec_hc.run_converter', [ $this, 'ajax_request' ] );
	}

	public function add_menu_entry() {
		$this->hook_suffix = add_submenu_page(
			'tools.php',
			_x( 'TEC Happy Converter', 'admin menu: page title', 'tec-happy-converter' ),
			_x( 'TEC Happy Converter', 'admin menu: menu title', 'tec-happy-converter' ),
			'manage_options',
			'tec-happy-converter',
			[ $this, 'screen' ]
		);
	}

	public function assets() {
		wp_enqueue_style(
			'tec_hc.admin-styles',
			main()->plugin_url . '/css/admin.css'
		);

		wp_enqueue_script(
			'tec_hc.admin-scripts',
			main()->plugin_url . '/js/admin.js',
			[ 'jquery' ]
		);

		wp_localize_script(
			'tec_hc.admin-scripts',
			'tecHappyConverter', [
				'complete' => esc_html_x( 'Job complete!', 'Conversion process', 'tec-happy-converter' ),
				'failed'   => esc_html_x( 'Job failed or timed out.', 'Conversion process', 'tec-happy-converter' ),
				'run'      => esc_html_x( 'Convert', 'Convert button', 'tec-happy-converter' ),
				'stop'     => esc_html_x( 'Stop', 'Convert button', 'tec-happy-converter' ),
				'check'    => wp_create_nonce( 'convert-events' ),
		] );
	}

	public function screen() {
		View::print( 'admin', [
			'converters' => main()->sources_manager->get_converters(),
		] );
	}

	public function ajax_request( $passthru, array $data = null ) {
		if ( null === $data && ! empty( $_POST ) ) {
			$data = $_POST;
		}

		if (
			null === $data
			|| empty( $data['check'] )
			|| empty( $data['id'] )
			|| ! wp_verify_nonce( $data['check'], 'convert-events' )
			|| ! current_user_can( 'manage_options' )
		) {
			wp_send_json_error( [ 'message' => 'Invalid request' ] );
			return;
		}

		$converter_id = filter_var( $data['id'], FILTER_SANITIZE_STRING );
		$converter = main()->sources_manager->get_converter_by_id( $converter_id );

		if ( ! $converter ) {
			wp_send_json_error( [ 'message' => 'Invalid converter' ] );
			return;
		}

		$this->process_batch( $converter );

		$total       = $converter->count_all_data_nodes();
		$converted   = $converter->count_converted_data_nodes();
		$unconverted = $converter->count_unconverted_data_nodes();

		wp_send_json_success( [
			'continue' => $unconverted > 0,
			'counts' => [
				'total'       => $total,
				'converted'   => $converted,
				'unconverted' => $unconverted,
			],
		] );
	}

	private function process_batch( Data_Source $converter ) {
		$count = 0;

		// Foreach is simply an easy way to make use of the generator, we don't need or
		// use the $event_converted var.
		foreach ( $converter->process() as $event_converted ) {
			// Bail after 5 loops.
			if ( ++$count >= 5 ) {
				break;
			}
		}
	}
}