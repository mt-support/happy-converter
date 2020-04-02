<?php

namespace Modern_Tribe\Support_Team\Happy_Converter\Sources;

use Modern_Tribe\Support_Team\Happy_Converter\Sources\Timely\Converter;
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
		'ai1ec' => Converter::class
	];

	public function setup() {
		$this->object_setup();
		$this->converters();
	}

	private function converters() {
		$converters = array_filter(
			$this->public_objects,
			static function ( $converter ) {
				return $converter instanceof Data_Source && $converter->is_active();
			}
		);

		foreach ( $converters as $converter ) {
			$this->converters[ $converter->get_id() ] = $converter;
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
