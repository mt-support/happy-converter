<?php
namespace Modern_Tribe\Support_Team\Happy_Converter\Utilities;

trait Object_Manager {
	public function setup() {
		if ( ! property_exists( $this, 'public_objects' ) || ! is_array( $this->public_objects ) ) {
			return;
		}

		foreach ( $this->public_objects as $name => $class ) {
			if ( is_object( $class ) ) {
				continue;
			}

			if ( class_exists( $class ) ) {
				$this->public_objects[ $name ] = $this->setup_object( $class );
			}
		}
	}

	private function setup_object( $classname ) {
		$object = new $classname();

		if ( method_exists( $object, 'setup' ) ) {
			$object->setup();
		}

		return $object;
	}

	public function __get( $key ) {
		if ( isset( $this->public_objects[ $key ] ) ) {
			return $this->public_objects[ $key ];
		}
	}
}