<?php
require_once 'Configuration.php';
require_once 'Mendeley.php';

function getAccessToken() {
	$mendeley = new Mendeley();
	if($mendeley->getAccessToken()) {
		echo 'Success! You now have your access token. Please be sure that all tests are working: <a href="' . 'tests/all_tests.php">run tests</a>. (You need simpletest installed to run them)';
	}
}

getAccessToken();