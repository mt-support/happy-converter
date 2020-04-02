<?php
namespace Modern_Tribe\Support_Team\Happy_Converter\Sources\Sugar_Calendar;

use Generator;
use Modern_Tribe\Support_Team\Happy_Converter\Event;
use Modern_Tribe\Support_Team\Happy_Converter\Sources\Data_Source;

class Converter implements Data_Source {
	const CONVERSION_MARKER = 'tec_hc.sugar_src_event';
	const PROGRESS_RECORD = 'tec_hc.sugar_progress';

	private $sugar_events_table = '';

	public function __construct() {
		global $wpdb;
		$this->sugar_events_table = $wpdb->prefix . 'sc_events';
	}

	public function is_active() : bool {
		return function_exists( 'sugar_calendar' );
	}

	public function get_id(): string {
		return 'sugar-calendar';
	}

	public function get_name(): string {
		return 'Sugar Calendar';
	}

	public function data_is_available(): bool {
		return $this->count_all_data_nodes() > 0;
	}

	public function unconverted_data_exists(): bool {
		return ( $this->count_all_data_nodes() - $this->count_converted_data_nodes() ) > 0;
	}

	public function count_all_data_nodes(): int {
		global $wpdb;

		return (int) $wpdb->get_var( "
			SELECT COUNT(*)
			FROM   {$this->sugar_events_table}
		" );
	}

	public function count_converted_data_nodes(): int {
		global $wpdb;
		$meta_key = self::CONVERSION_MARKER;

		return (int) $wpdb->get_var( "
			SELECT COUNT( DISTINCT( source_event.ID ) )

			FROM {$this->sugar_events_table} AS source_event

			JOIN {$wpdb->postmeta} AS tec_eventmeta
				 ON tec_eventmeta.meta_value = source_event.id

			JOIN {$wpdb->posts} AS tec_event
				 ON tec_event.ID = tec_eventmeta.post_id

			WHERE tec_eventmeta.meta_key = '{$meta_key}'
		" );
	}

	public function count_unconverted_data_nodes(): int {
		return $this->count_all_data_nodes() - $this->count_converted_data_nodes();
	}

	public function process(): Generator {
		global $wpdb;

		while ( true ) {
			$stored_offset = (int) get_transient(  self::PROGRESS_RECORD );
			$offset = ( $stored_offset > 0 ) ? $stored_offset : 0;

			$event_data = $wpdb->get_row( "
				SELECT *
				FROM   {$this->sugar_events_table}
				LIMIT  {$offset}, 1
			" );

			if ( ! $event_data ) {
				delete_transient( self::PROGRESS_RECORD );
				return;
			}

			$this->convert_event( $event_data );
			set_transient( self::PROGRESS_RECORD, $offset + 1, HOUR_IN_SECONDS );
			yield;
		}
	}

	private function convert_event( $event_data ) {
		$event_id = ( new Event() )
			->title( $event_data->title )
			->content( $event_data->content )
			->status( $event_data->status )
			->time_zone( $event_data->start_tz )
			->start_date( substr( $event_data->start, 0, 10 ) )
			->start_hour( (int) substr( $event_data->start, 11, 2 ) )
			->start_minute( (int) substr( $event_data->start, 14, 2 ) )
			->end_date( substr( $event_data->end, 0, 10 ) )
			->end_hour( (int) substr( $event_data->end, 11, 2 ) )
			->end_minute( (int) substr( $event_data->end, 14, 2 ) )
			->create();

		if ( ! $event_id ) {
			return false;
		}

		update_post_meta( $event_id, self::CONVERSION_MARKER, $event_data->id );

		return true;
	}
}
