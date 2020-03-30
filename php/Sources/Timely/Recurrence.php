<?php

namespace Modern_Tribe\Support_Team\Happy_Converter\Sources\Timely;

use Modern_Tribe\Support_Team\Happy_Converter\Converter\RRule;

class Recurrence {
	/** @var array $recurrence */
	private $recurrence;
	/**
	 * @var \DateTimeInterface
	 */
	private $start;
	/**
	 * @var \DateTimeInterface
	 */
	private $end;

	public function __construct( \DateTimeInterface $start, \DateTimeInterface $end ) {
		$this->recurrence = [
			'rules'      => [],
			'exclusions' => [],
		];
		$this->start      = $start;
		$this->end        = $end;
	}

	public function rules( string $rrules = '' ): Recurrence {
		$this->recurrence['rules'] = array_map( [ $this, 'populate' ], ( new RRule( $rrules ) )->parse() );

		return $this;
	}

	public function exclusions( string $exclusions = '' ): Recurrence {
		$this->recurrence['exclusions'] = array_map( [ $this, 'populate' ], ( new RRule( $exclusions ) )->parse() );

		return $this;
	}

	public function populate( $rules ) {
		$format                  = 'Y-m-d H:i:s';
		$rules['EventStartDate'] = $this->start->format( $format );
		$rules['EventEndDate']   = $this->end->format( $format );

		return $rules;
	}

	public function create(): array {
		return $this->recurrence;
	}
}