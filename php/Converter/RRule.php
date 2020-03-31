<?php

namespace Modern_Tribe\Support_Team\Happy_Converter\Converter;

/**
 * Class RRule
 *
 * Convert RRULes into PRO valid meta data used to create a series of recurrence events.
 *
 * @package Modern_Tribe\Support_Team\Happy_Converter\Converter
 */
class RRule {
	/**
	 * Result of the meta to be used for the recurrence.
	 *
	 * @var array
	 */
	private $meta = [];
	/**
	 * An array with the arguments of RRule.
	 *
	 * @var array
	 */
	private $args = [];

	/**
	 * Representation of days for PRO
	 *
	 * @var array Holds code for weekdays
	 */
	private $days = [
		'MO' => '1',
		'TU' => '2',
		'WE' => '3',
		'TH' => '4',
		'FR' => '5',
		'SA' => '6',
		'SU' => '7',
	];

	/**
	 * Hold a reference to the values to represent ordinal numbers from PRO.
	 *
	 * @var array Represent ordinal numbers
	 */
	private $ordinal = [
		'-1' => 'Last',
		'1'  => 'First',
		'2'  => 'Second',
		'3'  => 'Third',
		'4'  => 'Fourth',
		'5'  => 'Fifth',
	];

	/**
	 * RRule constructor.
	 *
	 * @param $rrule a RRULE to be converted into PRO meta rules.
	 */
	public function __construct( $rrule ) {
		$this->parse_args( (array) explode( ';', trim( $rrule ) ) );
	}

	/**
	 * Parse the Rules using the {key}={value} string mechanism to split the RRUle
	 *
	 * @param $rules
	 */
	protected function parse_args( $rules ) {
		foreach ( $rules as $rule ) {
			$values = (array) explode( '=', $rule );
			if ( count( $values ) < 2 ) {
				continue;
			}
			$key                = reset( $values );
			$value              = end( $values );
			$this->args[ $key ] = $value;
		}
	}

	/**
	 * Parse the RRUle into a PRO rule instead.
	 *
	 * @return array
	 */
	public function parse(): array {
		$frequency = $this->args['FREQ'] ?? '';

		switch ( $frequency ) {
			case 'DAILY':
				$this->daily();
				break;
			case 'WEEKLY':
				$this->weekly();
				break;
			case 'MONTHLY':
				$this->monthly();
				break;
			case 'YEARLY':
				$this->yearly();
				break;
			default:
				$this
					->custom( $this->args['RDATE'] ?? '' )
					->custom( $this->args['EXDATE'] ?? '' );
				break;
		}


		return $this->meta;
	}

	protected function daily() {
		$rule = [
			'type'   => 'Custom',
			'custom' => [
				// 6 is the max value used from TEC so we need to match what is possible on TEC here.
				'interval'  => $this->interval(),
				'same-time' => 'yes',
				'type'      => 'Daily',
			],
		];

		$this->meta[] = array_merge( $rule, $this->limit() );
	}

	protected function weekly() {

		if ( empty( $this->args['BYday'] ) ) {
			return;
		}

		$days     = [];
		$day_keys = (array) explode( ',', $this->args['BYday'] );

		foreach ( $day_keys as $key ) {
			if ( isset( $this->days[ $key ] ) ) {
				if ( $this->days[ $key ] === '7' ) {
					array_unshift( $days, $this->days[ $key ] );
				} else {
					$days[] = $this->days[ $key ];
				}
			}
		}

		$rule = [
			'type'   => 'Custom',
			'custom' => [
				'interval'  => $this->interval(),
				'week'      => [
					'day' => $days,
				],
				'same-time' => 'yes',
				'type'      => 'Weekly',
			],
		];

		$this->meta[] = array_merge( $rule, $this->limit() );
	}

	protected function monthly() {

		$rule = [
			'type'   => 'Custom',
			'custom' => [
				'interval'  => $this->interval(),
				'month'     => [],
				'same-time' => 'yes',
				'type'      => 'Monthly',
			],
		];

		if ( isset( $this->args['BYMONTHDAY'] ) ) {
			$this->by_month_day( $rule );

			return;
		}

		if ( isset( $this->args['BYday'] ) ) {
			$this->by_day( $rule );

			return;
		}
	}

	protected function by_month_day( $rule ) {
		$days = (array) explode( ',', $this->args['BYMONTHDAY'] );

		foreach ( $days as $day ) {
			$rule['custom']['month'] = [
				'same-day' => 'no',
				'number'   => (int) $day,
			];
			$this->meta[]            = array_merge( $rule, $this->limit() );
		}
	}

	protected function by_day( $rule ) {

		preg_match( '/MO|TU|WE|TH|FR|SA|SU/s', $this->args['BYday'], $matches, PREG_OFFSET_CAPTURE, 0 );

		if ( empty( $matches ) ) {
			return;
		}

		$result = reset( $matches );

		if ( empty( $result ) || ! is_array( $result ) ) {
			return;
		}

		$key = reset( $result );

		$number = str_replace( $key, '', $this->args['BYday'] );

		$rule['custom']['month'] = [
			'number'   => $this->ordinal[ $number ] ?? $this->ordinal['1'],
			'same-day' => 'no',
			'day'      => $this->days[ $key ] ?? '1',
		];

		$this->meta[] = array_merge( $rule, $this->limit() );
	}

	protected function yearly() {
		$rule = [
			'type'   => 'Custom',
			'custom' => [
				'interval'  => $this->interval(),
				'year'      => [
					'month'    => $this->args['BYMONTH'] ?? '',
					'same-day' => 'yes',
				],
				'same-time' => 'yes',
				'type'      => 'Yearly',
			],
		];

		$this->meta[] = array_merge( $rule, $this->limit() );
	}

	/**
	 *  Return an interval value, 6 is the max value as interval from TEC so we need to make sure new fields
	 * does not pass this limit when using an interval, as 6 is the max value.
	 *
	 * @return int
	 */
	protected function interval(): int {
		if ( empty( $this->args['INTERVAL'] ) ) {
			return 1;
		}

		return min( (int) $this->args['INTERVAL'], 6 );
	}

	protected function limit(): array {
		$limit = [
			'end-type' => 'Never',
		];

		if ( isset( $this->args['COUNT'] ) ) {
			$limit['end-type']  = 'After';
			$limit['end-count'] = (int) $this->args['COUNT'];

			return $limit;
		}

		if ( isset( $this->args['UNTIL'] ) ) {
			$limit['end-type'] = 'On';

			try {
				$end = new \DateTime( $this->args['UNTIL'] );
			} catch ( \Exception $e ) {
				$end = new \DateTime();
			}

			$limit['end'] = $end->format( 'Y-m-d' );
		}

		return $limit;
	}

	/**
	 * For every custom rule it creates repeated "once" rules happening on specific days to mimic the same
	 * behavior from RRULE into PRO.
	 *
	 * @param $value
	 *
	 * @return RRule
	 */
	protected function custom( $value ): RRule {
		if ( empty( $value ) ) {
			return $this;
		}

		$dates = (array) explode( ',', $value );

		foreach ( $dates as $date ) {
			try {
				$custom_date  = new \DateTime( $date );
				$this->meta[] = [
					'type'   => 'Custom',
					'custom' => [
						'date'      => [
							'date' => $custom_date->format( 'Y-m-d' ),
						],
						'same-time' => 'yes',
						'type'      => 'Date',
						'interval'  => 1,
					],
				];
			} catch ( \Exception $e ) {
				continue;
			}
		}

		return $this;
	}
}