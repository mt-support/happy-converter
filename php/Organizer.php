<?php

namespace Modern_Tribe\Support_Team\Happy_Converter;

class Organizer {
	private $args;

	private $required = [
		'Organizer',
	];

	private $status = 'publish';

	private $avoid_duplicate = true;

	public function organizer( string $organizer ): Organizer {
		$this->args['Organizer'] = $organizer;

		return $this;
	}

	public function description( string $description ): Organizer {
		$this->args['Description'] = $description;

		return $this;
	}

	public function email( string $email ): Organizer {
		$this->args['Email'] = $email;

		return $this;
	}

	public function phone( string $phone ): Organizer {
		$this->args['Phone'] = $phone;

		return $this;
	}

	public function website( string $website ): Organizer {
		$this->args['Website'] = $website;

		return $this;
	}

	public function status( string $status ): Organizer {
		$this->status = $status;

		return $this;
	}

	public function avoid_duplicate( bool $avoid ): Organizer {
		$this->avoid_duplicate = $avoid;

		return $this;
	}

	public function create() {
		$this->validate();

		return \Tribe__Events__Organizer::instance()->create( $this->args, $this->status, $this->avoid_duplicate );
	}

	private function validate() {
		foreach ( $this->required as $required ) {
			if ( empty( $this->args[ $required ] ) ) {
				throw new \LogicException( "The {$required} is a required parameter to create a venue." );
			}
		}
	}
}