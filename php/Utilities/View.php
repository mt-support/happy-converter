<?php
namespace Modern_Tribe\Support_Team\Happy_Converter\Utilities;

use function Modern_Tribe\Support_Team\Happy_Converter\main;

class View {
	private $path = '';
	private $vars = [];

	public static function print( string $path, array $vars = [] ) {
		print (string) ( new self( $path, $vars ) );
	}

	public function __construct( string $path, array $vars = [] ) {
		$this->path = $path;
		$this->vars = $vars;
	}

	public function __toString(): string {
		$path = main()->plugin_dir . '/views/' . $this->path . '.php';

		if ( ! file_exists( $path ) ) {
			return '';
		}

		ob_start();
		extract( $this->vars );
		include $path;
		return ob_get_clean();
	}
}