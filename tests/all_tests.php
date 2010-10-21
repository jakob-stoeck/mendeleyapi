<?php
require_once('simpletest/autorun.php');
require_once('../Mendeley.php');
require_once('../MendeleyBiblioDoc.php');
require_once('../Configuration.php');

class AllTests extends TestSuite {
	function AllTests() {
		$dir = dirname(__FILE__) . '/';

		$this->TestSuite('All tests');
		$this->addFile($dir . 'MendeleyCacheTest.php');
		$this->addFile($dir . 'MendeleyDocTest.php');
		$this->addFile($dir . 'MendeleyBiblioDocTest.php');
		$this->addFile($dir . 'MendeleyUtilTest.php');
		$this->addFile($dir . 'MendeleyTest.php');
	}
}
