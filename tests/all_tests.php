<?php
require_once('simpletest/autorun.php');
require_once('../Mendeley.php');
require_once('../Configuration.php');

class AllTests extends TestSuite {
	function AllTests() {
		$dir = dirname(__FILE__) . '/'; // simpletest didn't include my files with only the filename, so I add the right path myself
		
		$this->TestSuite('All tests');
		$this->addFile($dir . 'MendeleyCacheTest.php');
		$this->addFile($dir . 'MendeleyDocTest.php');
		$this->addFile($dir . 'MendeleyUtilTest.php');
		$this->addFile($dir . 'MendeleyTest.php');
	}
}
?>