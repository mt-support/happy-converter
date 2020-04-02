<?php

namespace Modern_Tribe\Support_Team\Happy_Converter\Sources\Timely;

use Generator;
use Modern_Tribe\Support_Team\Happy_Converter\Event;
use Modern_Tribe\Support_Team\Happy_Converter\Organizer;
use Modern_Tribe\Support_Team\Happy_Converter\Sources\Data_Source;
use Modern_Tribe\Support_Team\Happy_Converter\Venue;
use wpdb;

class Converter implements Data_Source {
	const CONVERSION_MASTER = 'tec_hc.timely_master_id';
	const CONVERSION_INSTANCE = 'tec_hc.timely_instance_id';
	const PROGRESS_RECORD = 'tec_hc.timely_progress';

	/**
	 * @var WPDb $wpdb
	 */
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function wpdb(): \WPDb {
		return $this->wpdb;
	}

	public function is_active(): bool {
		return function_exists( 'ai1ec_initiate_constants' );
	}

	public function get_id(): string {
		return 'all-in-one-events-calendar';
	}

	public function get_name(): string {
		return 'All-in-One Event Calendar by Time.ly';
	}

	public function data_is_available(): bool {
		return $this->count_all_data_nodes() > 0;
	}

	public function unconverted_data_exists(): bool {
		return ( $this->count_all_data_nodes()-$this->count_converted_data_nodes() ) > 0;
	}

	public function count_all_data_nodes(): int {
		return (int) $this->wpdb()->get_var(
			"SELECT COUNT(*) FROM {$this->events_table_name()}"
		);
	}

	public function count_converted_data_nodes(): int {
		$sql   = "SELECT COUNT(*) FROM {$this->wpdb()->postmeta} where meta_key = %s";
		$total = $this->wpdb->get_var( $this->wpdb()->prepare( $sql, self::CONVERSION_MASTER ) );

		return (int) $total+(int) get_transient( 'tec.hc.timely.invalid' );
	}

	public function count_unconverted_data_nodes(): int {
		// Prevent to have negative overflows.
		return max( 0, $this->count_all_data_nodes()-$this->count_converted_data_nodes() );
	}

	public function process(): Generator {
		while ( true ) {
			$stored_offset = (int) get_transient( self::PROGRESS_RECORD );
			$offset        = ( $stored_offset > 0 ) ? $stored_offset : 0;

			$event_data = $this->wpdb->get_row( "
				SELECT *
				FROM   {$this->events_table_name()}
				LIMIT  {$offset}, 1
			" );

			if ( ! $event_data ) {
				delete_transient( self::PROGRESS_RECORD );

				return;
			}

			$this->convert_event( $event_data );

			set_transient( self::PROGRESS_RECORD, $offset+1, HOUR_IN_SECONDS );
			yield;
		}
	}

	protected function events_table_name(): string {
		if ( $this->has_pro() ) {
			return "{$this->wpdb()->prefix}ai1ec_events";
		}

		return "{$this->wpdb()->prefix}ai1ec_event_instances";
	}

	protected function has_pro() {
		return class_exists( 'Tribe__Events__Pro__Main' );
	}

	protected function convert_event( $event_data ) {
		if ( empty( $event_data ) || empty( $event_data->post_id ) ) {
			return $this->failed();
		}

		$timely_post = get_post( $event_data->post_id );

		if ( ! $timely_post instanceof \WP_Post ) {
			return $this->failed();
		}

		$timely_event = $this->find_event( $timely_post->ID );

		if ( ! $timely_event ) {
			return $this->failed();
		}

		$timezone = \Tribe__Timezones::build_timezone_object( $timely_event->timezone_name );
		$start    = \DateTimeImmutable::createFromFormat( 'U', $event_data->start );
		$start    = $start->setTimezone( $timezone );

		$end = \DateTimeImmutable::createFromFormat( 'U', $event_data->end );
		$end = $end->setTimezone( $timezone );

		$boilerplate = ( new Event() )
			->title( $timely_post->post_title )
			->content( $timely_post->post_content )
			->excerpt( $timely_post->post_excerpt )
			->status( $timely_post->post_status )
			->comment_status( $timely_post->comment_status )
			->ping_status( $timely_post->ping_status )
			->time_zone( \Tribe__Timezones::get_valid_timezone( $timezone ) )
			->website( $timely_event->ticket_url )
			->start( $start )
			->end( $end );

		if ( has_post_thumbnail( $timely_post ) ) {
			$boilerplate->featured_image( get_post_thumbnail_id( $timely_post ) );
		}

		$cost = maybe_unserialize( $timely_event->cost );

		if ( ! empty( $cost ) ) {
			$boilerplate = $this->set_cost( $boilerplate, $cost );
		}

		if ( (int) $timely_event->show_map === 1 ) {
			$boilerplate->show_map()->show_map_link();
		}

		if ( (int) $timely_event->allday > 0 ) {
			$boilerplate = $this->all_day( $boilerplate, $start, $end );
		}

		$boilerplate = $this->save_venue( $boilerplate, $timely_event );
		$boilerplate = $this->save_organizer( $boilerplate, $timely_event );

		if ( $this->has_pro() ) {
			$recurrence = ( new Recurrence( $start, $end ) )
				->rules( (string) $timely_event->recurrence_rules )
				->exclusions( (string) $timely_event->exception_rules );
			$boilerplate->recurrence( $recurrence );
		}

		error_log( print_r( $boilerplate->get_args(), true ) );

		try {
			$event_id = $boilerplate->create();
		} catch ( \Exception $exception ) {
			return $this->failed();
		}

		if ( ! $event_id ) {
			return $this->failed();
		}

		update_post_meta( $event_id, self::CONVERSION_MASTER, $timely_post->ID );

		if ( ! $this->has_pro() ) {
			update_post_meta( $event_id, self::CONVERSION_INSTANCE, $event_data->id );
		}

		$event = tribe_get_event( $event_id );

		$this->save_taxonomies( $timely_post, $event, 'events_categories', \Tribe__Events__Main::TAXONOMY );
		$this->save_taxonomies( $timely_post, $event, 'events_tags', 'post_tag' );

		return $event;
	}

	public function find_event( int $post_id ) {
		$sql = "SELECT *
		FROM   {$this->wpdb->prefix}ai1ec_events
		where post_id = %d";

		return $this->wpdb->get_row(
			$this->wpdb->prepare( $sql, $post_id )
		);
	}

	protected function set_cost( Event $boilerplate, array $cost ): Event {
		if ( isset( $cost['is_free'] ) && (int) $cost['is_free'] === 1 ) {
			$boilerplate->is_free();
		} else if ( ! empty( $cost['cost'] ) ) {
			$boilerplate->cost( $cost['cost'] );
		}

		return $boilerplate->currency_symbol( '' )->currency_position( 'prefix' );
	}

	protected function all_day( Event $boilerplate, \DateTimeInterface $start, \DateTimeInterface $end ): Event {
		$boilerplate->is_all_day( true );
		// Remove 1 day as all day events here are set to the next day.
		if ( $end > $start ) {
			$end = $end->sub( new \DateInterval( 'P1D' ) );
		}
		$end = $end->setTime( 23, 59, 59 );

		return $boilerplate->end( $end );
	}

	public function save_venue( Event $boilerplate, $timely_event ) {
		try {
			$venue_id = ( new Venue() )
				->venue( $timely_event->venue )
				->city( $timely_event->city )
				->address( $timely_event->address )
				->province( $timely_event->province )
				->country( $timely_event->country )
				->zip( $timely_event->postal_code )
				->coordinates( $timely_event->latitude, $timely_event->longitude )
				->show_map( (int) $timely_event->show_map === 1 )
				->create();

			$boilerplate->venue( get_post( $venue_id ) );
		} catch ( \LogicException $exception ) {
			do_action( 'tribe_log', 'debug', __CLASS__ . 'invalid.venue', [
				'timely'    => $timely_event,
				'message'   => $exception->getMessage(),
				'exception' => $exception,
			] );
		} finally {
			return $boilerplate;
		}
	}

	public function save_organizer( Event $boilerplate, $timely_event ) {
		try {
			$organizer_id = ( new Organizer() )
				->organizer( $timely_event->contact_name )
				->phone( $timely_event->contact_phone )
				->email( $timely_event->contact_email )
				->website( $timely_event->contact_url )
				->create();

			$boilerplate->organizers( [ get_post( $organizer_id ) ] );

		} catch ( \LogicException $exception ) {
			do_action( 'tribe_log', 'debug', __CLASS__ . 'invalid.organization', [
				'timely'    => $timely_event,
				'message'   => $exception->getMessage(),
				'exception' => $exception,
			] );
		} finally {
			return $boilerplate;
		}
	}

	public function save_taxonomies( \WP_Post $timely, \WP_Post $event, $source_tax, $target_tax ) {
		$categories = wp_get_post_terms( $timely->ID, $source_tax );

		if ( is_wp_error( $categories ) ) {
			return;
		}

		$categories = array_filter( $categories, static function ( $category ) {
			return $category instanceof \WP_Term;
		} );

		$terms = [];
		/** @var \WP_Term $category */
		foreach ( $categories as $category ) {
			$result = $this->find_or_create_term( $category, $source_tax, $target_tax );
			if ( $result ) {
				$terms[] = $result;
			}
		}

		wp_set_object_terms( $event->ID, $terms, $target_tax, false );
	}

	public function find_or_create_term( \WP_Term $category, string $source_tax, string $target_tax ) {
		$result = get_term_by( 'slug', $category->slug, $target_tax );

		if ( $result instanceof \WP_Term ) {
			return $result->term_id;
		}

		$result = wp_insert_term( $category->name, $target_tax, [
			'slug'        => $category->slug,
			'description' => $category->description,
			'parent'      => $this->find_or_create_term_parent( $category, $source_tax, $target_tax ),
		] );

		if ( empty( $result ) || is_wp_error( $result ) || empty( $result['term_id'] ) ) {
			return 0;
		}

		return $result['term_id'];
	}

	protected function find_or_create_term_parent( \WP_Term $category, string $source_tax, string $target_tax ) {
		if ( ! $category->parent ) {
			return 0;
		}
		$parent_term = get_term_by( 'id', $category->parent, $source_tax );
		if ( $parent_term instanceof \WP_Term ) {
			return $this->find_or_create_term( $parent_term, $source_tax, $target_tax );
		}

		return 0;
	}

	protected function failed( string $message = '', array $data = [] ): \WP_Error {
		$count = (int) get_transient( 'tec.hc.timely.invalid' );
		set_transient( 'tec.hc.timely.invalid', $count+1 );

		return new \WP_Error( 'import-failed', $message, $data );
	}
}