<?php
namespace Modern_Tribe\Support_Team\Happy_Converter;

use Modern_Tribe\Support_Team\Happy_Converter\Utilities\Object_Manager;

/**
 * @property-read Admin_UI $admin_ui;
 * @property-read string $plugin_dir;
 * @property-read string $plugin_file;
 * @property-read string $plugin_url;
 * @property-read Sources\Manager $sources_manager
 */
class Main {
	use Object_Manager {
		setup as object_setup;
	}

	private $plugin_dir;
	private $plugin_file;
	private $plugin_url;

	protected $public_objects = [
		'admin_ui'        => Admin_UI::class,
		'sources_manager' => Sources\Manager::class,
	];

	public function __construct( string $plugin_file = '', string $plugin_url = '' ) {
		$this->plugin_dir  = dirname( $plugin_file );
		$this->plugin_file = $plugin_file;
		$this->plugin_url  = $plugin_url;
	}

	public function setup() {
		$this->object_setup();
		$this->public_objects['plugin_dir']  = $this->plugin_dir;
		$this->public_objects['plugin_file'] = $this->plugin_file;
		$this->public_objects['plugin_url']  = $this->plugin_url;
	}
}