<?php

namespace Modern_Tribe\Support_Team\Happy_Converter;

class Venue {
	private $args;

	private $required = [
		'Venue',
	];

	private $status = 'publish';

	private $avoid_duplicate = true;

	public function venue( string $venue ): Venue {
		$this->args['Venue'] = $venue;

		return $this;
	}

	public function address( string $address ): Venue {
		$this->args['Address'] = $address;

		return $this;
	}

	public function city( string $city ): Venue {
		$this->args['City'] = $city;

		return $this;
	}

	public function country( string $country ) : Venue {
		$this->args['Country'] = $country;
		return $this;
	}

	public function province( string $city ): Venue {
		$this->args['Province'] = $city;

		return $this;
	}

	public function state( string $state ): Venue {
		$this->args['State'] = $state;

		return $this;
	}

	public function state_province( string $state_province ): Venue {
		$this->args['StateProvince'] = $state_province;

		return $this;
	}

	public function zip( string $zip ): Venue {
		$this->args['Zip'] = $zip;

		return $this;
	}

	public function phone( string $phone ): Venue {
		$this->args['Phone'] = $phone;

		return $this;
	}

	public function status( string $status ): Venue {
		$this->status = $status;

		return $this;
	}

	public function avoid_duplicate( bool $avoid ): Venue {
		$this->avoid_duplicate = $avoid;

		return $this;
	}

	public function show_map( bool $show ): Venue {
		$this->args['ShowMap'] = $show;
		return $this;
	}

	public function coordinates( float $lat, float $lng ) : Venue {
		if ( $lat !== 0 && $lng !== 0 ) {
			$this->args['Lat'] = $lat;
			$this->args['Lng'] = $lng;
		}
		return $this;
	}

	public function create(): int {
		$this->validate();

		return \Tribe__Events__Venue::instance()->create( $this->args, $this->status, $this->avoid_duplicate );
	}

	private function validate() {
		foreach ( $this->required as $required ) {
			if ( empty( $this->args[ $required ] ) ) {
				throw new \LogicException( "The {$required} is a required parameter to create a venue." );
			}
		}
	}
}