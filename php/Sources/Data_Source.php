<?php
namespace Modern_Tribe\Support_Team\Happy_Converter\Sources;

use Generator;

interface Data_Source {
	public function get_id(): string;
	public function get_name(): string;

	public function data_is_available(): bool;
	public function unconverted_data_exists(): bool;

	public function count_all_data_nodes(): int;
	public function count_converted_data_nodes(): int;
	public function count_unconverted_data_nodes(): int;

	public function process(): Generator;
}