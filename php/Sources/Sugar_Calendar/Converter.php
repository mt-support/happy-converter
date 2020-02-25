<?php
namespace Modern_Tribe\Support_Team\Happy_Converter\Sources\Sugar_Calendar;

use Generator;
use Modern_Tribe\Support_Team\Happy_Converter\Sources\Data_Source;

class Converter implements Data_Source {
	const CONVERSION_MARKER = 'tec_hc.sugar_src_event';
	const PROGRESS_RECORD = 'tec_hc.sugar_progress';

	private $sugar_events_table = '';

	public function __construct() {
		global $wpdb;
		$this->sugar_events_table = $wpdb->prefix . 'sc_events';
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
		$event_id = tribe_create_event( [
			'post_title'       => $event_data->title,
			'post_content'     => $event_data->content,
			'post_status'      => $event_data->status,
			'EventStartDate'   => substr( $event_data->start, 0, 10 ),
			'EventStartHour'   => (int) substr( $event_data->start, 11, 2 ),
			'EventStartMinute' => (int) substr( $event_data->start, 14, 2 ),
			'EventEndDate'     => substr( $event_data->end, 0, 10 ),
			'EventEndHour'     => (int) substr( $event_data->end, 11, 2 ),
			'EventEndMinute'   => (int) substr( $event_data->end, 14, 2 ),
			'EventTimezone'    => $event_data->start_tz,
		] );

		if ( ! $event_id ) {
			return false;
		}

		update_post_meta( $event_id, self::CONVERSION_MARKER, $event_data->id );
		return true;
	}
}