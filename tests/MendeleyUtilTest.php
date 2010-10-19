<?php
class MendeleyUtilTest extends UnitTestCase {
	function testPostCurl() {
		$url = 'http://www.example.org/';
		$method = 'POST';
		$headers = array();
		$postVals = array();
		
		$response = MendeleyUtil::runCurl($url, $method, $headers, $postVals);
		
		$this->assertTrue(!empty($response));
	}
}