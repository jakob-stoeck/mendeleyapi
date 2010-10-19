<?php
class Configuration {
	public static function getPathToOauth() {
		$pathToOauth = 'oauth/OAuth.php'; // change the path to your OAuth library installation from http://code.google.com/p/oauth/ or add it to your PHP include path

		if(file_exists($pathToOauth)) {
			return $pathToOauth;
		} else {
			throw new Exception(sprintf('OAuth library not found at: %s. Please install it or change the path in %s', $pathToOauth, __FILE__));
		}
	}

	public static function getConsumer($type = 'default') {
		$consumer['default'] = array(
			'key' => 'CHANGE_ME',
			'secret' => 'CHANGE_ME'
		);

		$consumer['test'] = array(
			'key' => '',
			'secret' => ''
		);

		return array('key' => $consumer[$type]['key'], 'secret' => $consumer[$type]['secret']);
	}
}
