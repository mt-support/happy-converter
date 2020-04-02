<?php
/**
 * Plugin name: The Events Calendar | Happy Converter
 * Description: Utility that helps covert from alternative calendar plugins to The Events Calendar. <strong>&#9888;
 * This plugin is in beta.</strong> Version:     0.2.0-beta Author:      Modern Tribe, Inc License:     GPL-3.0
 *
 *     The Events Calendar | Happy Converter
 *     Copyright (C) 2020 Modern Tribe Inc
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Modern_Tribe\Support_Team\Happy_Converter;

use Composer\Autoload\ClassLoader;

/**
 * Creates and supplies the project autoloader.
 *
 * @return ClassLoader
 */
function autoload(): ClassLoader {
	static $autoloader;

	if ( empty( $autoloader ) ) {
		$autoloader = require __DIR__ . '/vendor/autoload.php';
	}

	return $autoloader;
}

/**
 * Sets up and returns the main plugin instance.
 *
 * @return Main
 */
function main(): Main {
	static $main_plugin_instance;

	if ( empty( $main_plugin_instance ) ) {
		$main_plugin_instance = new Main( __FILE__, plugin_dir_url( __FILE__ ) );
		$main_plugin_instance->setup();
	}

	return $main_plugin_instance;
}

add_action( 'tribe_plugins_loaded', __NAMESPACE__ . '\\autoload' );
add_action( 'tribe_plugins_loaded', __NAMESPACE__ . '\\main' );