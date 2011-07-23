<?php
require_once('simpletest/autorun.php');
require_once('../Mendeley.php');
require_once('../MendeleyBiblioDoc.php');
require_once('../Configuration.php');

class AllTests extends TestSuite {
	function AllTests() {
		$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

		$this->TestSuite('All tests');
		$this->addFile($dir . 'MendeleyCacheTest.php');
		$this->addFile($dir . 'MendeleyDocTest.php');
		$this->addFile($dir . 'MendeleyBiblioDocTest.php');
		$this->addFile($dir . 'MendeleyUtilTest.php');
		$this->addFile($dir . 'MendeleyTest.php');
		// $this->addFile($dir . 'MendeleyInfo.php'); // activate to output Mendeley infos
	}
}
