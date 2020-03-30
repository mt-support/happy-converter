<?php

namespace Modern_Tribe\Support_Team\Happy_Converter;

use Modern_Tribe\Support_Team\Happy_Converter\Sources\Timely\Recurrence;

class Event {
	private $args;

	private $required = [
		'EventStartDate',
		'EventEndDate',
	];

	public function __construct( array $defaults = [] ) {
		$this->args = array_merge(
			[
				'post_type'     => \Tribe__Events__Main::POSTTYPE,
				'post_title'    => '',
				'post_content'  => '',
				'post_status'   => 'publish',
				'EventTimezone' => 'UTC',
				'EventAllDay'   => false,
			],
			$defaults
		);
	}

	public function start( \DateTimeInterface $date ): Event {
		$this->args['EventStartDate']     = $date->format( 'Y-m-d' );
		$this->args['EventStartHour']     = $date->format( 'h' );
		$this->args['EventStartMinute']   = $date->format( 'i' );
		$this->args['EventStartMeridian'] = $date->format( 'a' );

		return $this;
	}

	public function end( \DateTimeInterface $date ): Event {
		$this->args['EventEndDate']     = $date->format( 'Y-m-d' );
		$this->args['EventEndHour']     = $date->format( 'h' );
		$this->args['EventEndMinute']   = $date->format( 'i' );
		$this->args['EventEndMeridian'] = $date->format( 'a' );

		return $this;
	}

	public function start_date( string $date ): Event {
		$this->args['EventStartDate'] = $date;

		return $this;
	}

	public function start_hour( int $hour ): Event {
		$this->args['EventStartHour'] = $hour;

		return $this;
	}

	public function start_minute( int $minute ): Event {
		$this->args['EventStartMinute'] = $minute;

		return $this;
	}

	public function end_date( string $date ): Event {
		$this->args['EventEndDate'] = $date;

		return $this;
	}

	public function end_hour( int $hour ): Event {
		$this->args['EventEndHour'] = $hour;

		return $this;
	}

	public function end_minute( int $minute ): Event {
		$this->args['EventEndMinute'] = $minute;

		return $this;
	}

	public function title( string $title ): Event {
		$this->args['post_title'] = $title;

		return $this;
	}

	public function content( string $description ): Event {
		$this->args['post_content'] = $description;

		return $this;
	}

	public function excerpt( string $excerpt ) {
		$this->args['post_excerpt'] = $excerpt;

		return $this;
	}

	public function status( string $status ): Event {
		$this->args['post_status'] = $status;

		return $this;
	}

	public function is_all_day( bool $all_day ): Event {
		$this->args['EventAllDay'] = $all_day;

		return $this;
	}

	public function time_zone( string $timezone ): Event {
		$this->args['EventTimezone'] = $timezone;

		return $this;
	}

	public function featured_image( int $image_id ): Event {
		$this->args['FeaturedImage'] = $image_id;

		return $this;
	}

	public function comment_status( string $status ): Event {
		$this->args['comment_status'] = $status;

		return $this;
	}

	public function ping_status( string $status ): Event {
		$this->args['ping_status'] = $status;

		return $this;
	}

	public function is_free(): Event {
		return $this->cost( '0' );
	}

	public function cost( string $cost ): Event {
		$this->args['EventCost'] = $cost;

		return $this;
	}

	public function currency_symbol( string $symbol ): Event {
		$this->args['EventCurrencySymbol'] = $symbol;

		return $this;
	}

	public function currency_position( string $position ): Event {
		$this->args['EventCurrencyPosition'] = $position;

		return $this;
	}

	public function show_map(): Event {
		$this->args['EventShowMap'] = true;

		return $this;
	}

	public function show_map_link(): Event {
		$this->args['EventShowMapLink'] = true;

		return $this;
	}

	public function website( string $url ): Event {
		$this->args['EventURL'] = $url;

		return $this;
	}

	public function recurrence( Recurrence $recurrence ) : Event {
		$this->args['recurrence'] = $recurrence->create();

		return $this;
	}

	public function venue( \WP_Post $venue = null ): Event {
		if ( $venue && tribe_is_venue( $venue->ID ) ) {
			$this->args['EventVenueID'] = $venue->ID;
		}

		return $this;
	}

	public function organizers( array $organizers = [] ): Event {
		$organizers = array_filter( $organizers, static function ( $organizer ) {
			return $organizer instanceof \WP_Post && tribe_is_organizer( $organizer->ID );
		} );

		$this->args['Organizer']['OrganizerID'] = array_map( static function ( \WP_Post $organizer ) {
			return $organizer->ID;
		}, $organizers );

		return $this;
	}

	public function get_args() : array {
		return $this->args;
	}

	public function create() {
		$this->validate();

		return tribe_create_event( $this->args );
	}

	public function update( \WP_Post $post ) {
		$this->validate();

		return tribe_update_event( $post->ID, $this->args );
	}

	private function validate() {
		foreach ( $this->required as $required ) {
			if ( empty( $this->args[ $required ] ) ) {
				throw new \LogicException( "The {$required} is a required parameter to create an event." );
			}
		}
	}
}