<?php
namespace Modern_Tribe\Support_Team\Happy_Converter\Sources;

use Modern_Tribe\Support_Team\Happy_Converter\Utilities\Object_Manager;

/**
 * @property-read Sugar_Calendar\Converter $sugar_calendar
 */
class Manager {
	use Object_Manager {
		setup as object_setup;
	}

	private $converters = [];

	protected $public_objects = [
		'sugar_calendar' => Sugar_Calendar\Converter::class,
	];

	public function setup() {
		$this->object_setup();
		$this->converters();
	}

	private function converters() {
		$converters = (array) apply_filters( 'tec_happy_converter.available_converters', [
			$this->sugar_calendar,
		] );

		foreach ( $converters as $possible_converter ) {
			if ( $possible_converter instanceof Data_Source ) {
				$this->converters[ $possible_converter->get_id() ] = $possible_converter;
			}
		}
	}

	/**
	 * @return Data_Source[]
	 */
	public function get_converters(): array {
		return $this->converters;
	}

	/**
	 * @param string $id
	 *
	 * @return Data_Source|false
	 */
	public function get_converter_by_id( string $id ) {
		return $this->converters[ $id ] ?? false;
	}
}