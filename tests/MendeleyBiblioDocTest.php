<?php
class MendeleyBiblioDocTest extends UnitTestCase {
	function testToBiblio() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 123;
	
		$doc = new MendeleyBiblioDoc();
		$doc->authors = 'Jakob Stoeck';
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->group_id = $groupId;
		$doc->type = 'Magazine Article';
	
		$biblio = $doc->toBiblio();
	
		$this->assertEqual($title, $doc->title);
		$this->assertEqual($biblio['title'], $doc->title);
		$this->assertEqual($biblio['biblio_url'], $doc->url);
		$this->assertEqual($biblio['biblio_type'], 106);
		$this->assertTrue(is_numeric($biblio['biblio_type']) && $biblio['biblio_type'] > 0);
	}
	
	function testConstructWithNode() {
		$node = self::nodeFactory();
		$doc = MendeleyBiblioDoc::constructWithNode($node);

		$this->assertEqual($node->title, $doc->title);
		$this->assertEqual($node->biblio_type, MendeleyBiblioDoc::mendeleyToBiblioType($doc->type));
		// $this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR], $doc->authors); // TODO add 'name' => array key
		$this->assertEqual($node->biblio_abst_e, $doc->abstract);
	}
	
	function testUploadToMendeley() {
		$node = self::nodeFactory();
		$biblioDoc = MendeleyBiblioDoc::constructWithNode($node);

		$mendeley = new Mendeley();
		$response = $mendeley->post('documents', $biblioDoc->toParams());
		$this->assertTrue(isset($response->document_id) && is_numeric($response->document_id));

		$doc = $mendeley->get('documents/' . $response->document_id);
	
		$this->assertEqual($node->title, $doc->title);
		$this->assertEqual($node->biblio_type, MendeleyBiblioDoc::mendeleyToBiblioType($doc->type));
		// $this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR], $doc->authors); // mendeley puts a space before the author name after upload. Strange.
		$this->assertEqual($node->biblio_abst_e, $doc->abstract);
	}

	static function nodeFactory() {
		$node = new StdClass();
	  $node->uid = '1';
	  $node->created = 1287598385;
	  $node->type = 'biblio';
	  $node->language = 'de';
	  $node->changed = 1287598385;
	  $node->biblio_type = '106';
	  $node->title = 'Example Node Title';
	  $node->biblio_tertiary_title = '';
	  $node->biblio_section = '';
	  $node->biblio_publisher = '';
		$node->biblio_contributors = array(1 => array(array('name' => 'Jakob Stoeck'), array('name' => 'Hans im Glück')));
		$node->biblio_keywords = array('a', 'b', 'c');
		$node->biblio_abst_e = 'Hier steh ich nun ich armer Tor';
		$node->biblio_mendeley_doc_id = null;

		return $node;
	}
}
