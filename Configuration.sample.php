<?php
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
