<?php
class MendeleyDocTest extends UnitTestCase {
	function testToJson() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 123;

		$doc = new MendeleyDoc();
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->group_id = $groupId;

		$json = $doc->toJson();
		$doc2 = json_decode($json);

		$this->assertTrue($doc2->title === $title);
		$this->assertTrue($doc2->url === $url);
		$this->assertTrue($doc2->tags === $tags);
		$this->assertTrue($doc2->group_id === $groupId);
	}

	function testToParams() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 123;

		$doc = new MendeleyDoc();
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->group_id = $groupId;

		$doc = $doc->toParams();

		$this->assertTrue(isset($doc['document']));
		$this->assertEqual($doc['document']['title'], $title);
		$this->assertEqual($doc['document']['url'], $url);
		$this->assertEqual($doc['document']['tags'], $tags);
		$this->assertEqual($doc['document']['group_id'], $groupId);
	}
}