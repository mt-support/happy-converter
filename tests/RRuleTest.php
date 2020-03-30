<?php

namespace Modern_Tribe\Support_Team\Happy_Converter\Converter;


class RRuleTest extends \PHPUnit\Framework\TestCase {
	/**
	 * It should not convert empty string
	 *
	 * @test
	 */
	public function it_should_not_convert_empty_string() {
		$meta = new RRule( '   ' );

		$this->assertEquals( [], $meta->parse() );
	}

	/**
	 * It should convert daily events
	 *
	 * @dataProvider provider_daily_events
	 *
	 * @test
	 */
	public function it_should_convert_daily_events( $daily_rule, $expected ) {
		$meta = new RRule( $daily_rule );

		$this->assertEquals( $expected, $meta->parse() );
	}

	public function provider_daily_events() {
		return [
			[
				// Every day interval of 44 after 10 events
				'FREQ=DAILY;INTERVAL=44;COUNT=10;',
				[
					[
						'type'      => 'Custom',
						'custom'    => [
							'interval'  => 6,
							'same-time' => 'yes',
							'type'      => 'Daily',
						],
						'end-type'  => 'After',
						'end-count' => 10,
					],
				],
			],
			[
				'FREQ=DAILY;COUNT=20;',
				[
					[
						'type'      => 'Custom',
						'custom'    => [
							'interval'  => 1,
							'same-time' => 'yes',
							'type'      => 'Daily',
						],
						'end-type'  => 'After',
						'end-count' => 20,
					],
				],
			],
			[
				'FREQ=DAILY;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 1,
							'same-time' => 'yes',
							'type'      => 'Daily',
						],
						'end-type' => 'Never',
					],
				],
			],
		];
	}

	/**
	 * It should convert weekly events
	 *
	 * @dataProvider provider_weekly_events
	 *
	 * @test
	 */
	public function it_should_convert_weekly_events( $weekly_rule, $expected ) {
		$meta = new RRule( $weekly_rule );

		$this->assertEquals( $expected, $meta->parse() );
	}

	public function provider_weekly_events() {
		return [
			[
				// Every week on Mo, We and Su until June 25
				'FREQ=WEEKLY;WKST=MO;BYday=MO,WE,SU;UNTIL=20200625T235959Z;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 1,
							'week'      => [
								'day' => [
									'7',
									'1',
									'3'
								],
							],
							'same-time' => 'yes',
							'type'      => 'Weekly',
						],
						'end-type' => 'On',
						'end'      => '2020-06-25',
					],
				],
			],
			[
				'FREQ=WEEKLY;INTERVAL=10;WKST=MO;BYday=TU,TH,FR,SA;COUNT=10;',
				[
					[
						'type'      => 'Custom',
						'custom'    => [
							'interval'  => 6,
							'week'      => [
								'day' => [
									'2',
									'4',
									'5',
									'6',
								],
							],
							'same-time' => 'yes',
							'type'      => 'Weekly',
						],
						'end-type'  => 'After',
						'end-count' => 10,
					],
				],
			],
		];
	}

	/**
	 * It should convert monthly events
	 *
	 * @dataProvider provider_monthly_events
	 *
	 * @test
	 */
	public function it_should_convert_monthly_events( $monthly_rule, $expected ) {
		$meta = new RRule( $monthly_rule );

		$this->assertEquals( $expected, $meta->parse() );
	}

	public function provider_monthly_events(): array {
		return [
			[
				// Every 6 months On the day of the month and never ends
				'FREQ=MONTHLY;INTERVAL=6;BYMONTHDAY=1,9,17,25;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 6,
							'month'     => [
								'same-day' => 'no',
								'number'   => 1,
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'Never',
					],
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 6,
							'month'     => [
								'same-day' => 'no',
								'number'   => 9,
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'Never',
					],
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 6,
							'month'     => [
								'same-day' => 'no',
								'number'   => 17,
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'Never',
					],
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 6,
							'month'     => [
								'same-day' => 'no',
								'number'   => 25,
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'Never',
					],
				],
			],
			[
				// Every month on the day of the event 2nd Friday after 30 times
				'FREQ=MONTHLY;BYday=2FR;COUNT=30;',
				[
					[
						'type'      => 'Custom',
						'custom'    => [
							'interval'  => 1,
							'month'     => [
								'same-day' => 'no',
								'number'   => 'Second',
								'day'      => '5',
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type'  => 'After',
						'end-count' => 30,
					],
				],
			],
			[
				// Every month on the first sunday until september 30 of 2020
				'FREQ=MONTHLY;BYday=1SU;UNTIL=20200930T235959Z;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 1,
							'month'     => [
								'same-day' => 'no',
								'number'   => 'First',
								'day'      => '7',
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'On',
						'end'      => '2020-09-30',
					],
				],
			],
			[
				// Every 2 months on the 5th Monday until september 30 of 2020
				'FREQ=MONTHLY;INTERVAL=2;BYday=5MO;UNTIL=20200930T235959Z;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 2,
							'month'     => [
								'same-day' => 'no',
								'number'   => 'Fifth',
								'day'      => '1',
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'On',
						'end'      => '2020-09-30',
					],
				],
			],
			[
				// Every 4 months on the last wednesday until  september 30 of 2020
				'FREQ=MONTHLY;INTERVAL=4;BYday=-1WE;UNTIL=20200930T235959Z;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 4,
							'month'     => [
								'same-day' => 'no',
								'number'   => 'Last',
								'day'      => '3',
							],
							'same-time' => 'yes',
							'type'      => 'Monthly',
						],
						'end-type' => 'On',
						'end'      => '2020-09-30',
					],
				],
			],
		];
	}

	/**h
	 * It should convert yearly events
	 *
	 * @dataProvider provider_yearly_events
	 *
	 * @test
	 */
	public function it_should_convert_yearly_events( $rrule, $expected ) {
		$meta = new RRule( $rrule );

		$this->assertEquals( $expected, $meta->parse() );
	}

	public function provider_yearly_events(): array {
		return [
			[
				'FREQ=YEARLY;INTERVAL=3;BYMONTH=1,3,4,7,8,12;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 3,
							'year'      => [
								'month'    => '1,3,4,7,8,12',
								'same-day' => 'yes',
							],
							'same-time' => 'yes',
							'type'      => 'Yearly',
						],
						'end-type' => 'Never',
					],
				],
			],
			[
				'FREQ=YEARLY;BYMONTH=1,3;COUNT=100;',
				[
					[
						'type'      => 'Custom',
						'custom'    => [
							'interval'  => 1,
							'year'      => [
								'month'    => '1,3',
								'same-day' => 'yes',
							],
							'same-time' => 'yes',
							'type'      => 'Yearly',
						],
						'end-type'  => 'After',
						'end-count' => 100,
					],
				],
			],
			[
				'FREQ=YEARLY;BYMONTH=1,3;',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 1,
							'year'      => [
								'month'    => '1,3',
								'same-day' => 'yes',
							],
							'same-time' => 'yes',
							'type'      => 'Yearly',
						],
						'end-type' => 'Never',
					],
				],
			],
			[
				'FREQ=YEARLY;BYMONTH=1,2,3,7,10;UNTIL=20200619T235959Z',
				[
					[
						'type'     => 'Custom',
						'custom'   => [
							'interval'  => 1,
							'year'      => [
								'month'    => '1,2,3,7,10',
								'same-day' => 'yes',
							],
							'same-time' => 'yes',
							'type'      => 'Yearly',
						],
						'end-type' => 'On',
						'end'      => '2020-06-19',
					],
				],
			],
		];
	}

	/**
	 * It should adjust a custom date into custom rules
	 *
	 * @test
	 */
	public function it_should_adjust_a_custom_date_into_custom_rules() {
		$rrule    = new RRule( 'RDATE=20200319T000000Z,20200321T000000Z,20200331T000000Z' );
		$expected = [
			[
				'type'   => 'Custom',
				'custom' => [
					'same-time' => 'yes',
					'date'      => [
						'date' => '2020-03-19',
					],
					'type'      => 'Date',
					'interval'  => 1,
				],
			],
			[
				'type'   => 'Custom',
				'custom' => [
					'same-time' => 'yes',
					'date'      => [
						'date' => '2020-03-21',
					],
					'type'      => 'Date',
					'interval'  => 1,
				],
			],
			[
				'type'   => 'Custom',
				'custom' => [
					'same-time' => 'yes',
					'date'      => [
						'date' => '2020-03-31',
					],
					'type'      => 'Date',
					'interval'  => 1,
				],
			],
		];
		$this->assertEquals( $expected, $rrule->parse() );
	}

	/**
	 * It should adjust exclusion rules into custom rules
	 *
	 * @test
	 */
	public function it_should_adjust_exclusion_rules_into_custom_rules() {
		$rrule    = new RRule( 'EXDATE=20200226T000000Z,20200218T000000Z,20200217T000000Z' );
		$expected = [
			[
				'type'   => 'Custom',
				'custom' => [
					'same-time' => 'yes',
					'date'      => [
						'date' => '2020-02-26',
					],
					'type'      => 'Date',
					'interval'  => 1,
				],
			],
			[
				'type'   => 'Custom',
				'custom' => [
					'same-time' => 'yes',
					'date'      => [
						'date' => '2020-02-18',
					],
					'type'      => 'Date',
					'interval'  => 1,
				],
			],
			[
				'type'   => 'Custom',
				'custom' => [
					'same-time' => 'yes',
					'date'      => [
						'date' => '2020-02-17',
					],
					'type'      => 'Date',
					'interval'  => 1,
				],
			],
		];
		$this->assertEquals( $expected, $rrule->parse() );
	}
}