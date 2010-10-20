<?php
class MendeleyUtilTest extends UnitTestCase {
	function testArrayKeysRecursive() {
		$mendeley = new Mendeley();
		$itemsPerPage = 2;
		$collection = $mendeley->getCollection('sharedcollections/164791', array('items' => $itemsPerPage));
		$this->assertEqual(count($collection->documents), $itemsPerPage);

		$info = MendeleyUtil::arrayKeysRecursive($collection->documents);
		var_dump($info);
		$this->assertTrue(count($info) > 5);
	}

	function testPostCurl() {
		$url = 'http://www.example.org/';
		$method = 'POST';
		$headers = array();
		$postVals = array();
		
		$response = MendeleyUtil::runCurl($url, $method, $headers, $postVals);
		
		$this->assertTrue(!empty($response));
	}
}
