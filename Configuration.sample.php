<?php
/**
 *   Mendeley API Client
 *
 *   Copyright (C) 2010  Jakob Stoeck
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, write to the Free Software Foundation, Inc.,
 *   51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

class Configuration {
	/**
	 * change the path to your OAuth library installation from http://code.google.com/p/oauth/ or add it to your PHP include path
	 */
	const PATH_TO_OAUTH = 'oauth/OAuth.php'; // change this

	public static function getPathToOauth() {
		if(file_exists(self::PATH_TO_OAUTH)) {
			return self::PATH_TO_OAUTH;
		} else {
			throw new Exception(sprintf('OAuth library not found at: %s. Please install it or change the path in %s', self::PATH_TO_OAUTH, __FILE__));
		}
	}

	public static function getConsumer($type = 'default') {
		$consumer['default'] = array(
			'key' => 'CHANGE_ME', // change this
			'secret' => 'CHANGE_ME' // change this
		);

		$consumer['test'] = array(
			'key' => '',
			'secret' => ''
		);

		return array('key' => $consumer[$type]['key'], 'secret' => $consumer[$type]['secret']);
	}
}
