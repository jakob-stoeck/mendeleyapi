<?php
class MendeleyInfo extends UnitTestCase {
	private $mendeley;

	function __construct() {
		$this->mendeley = new Mendeley();
	}

	/**
	 * Used to get as many distinct publication types as possible. Should only be used manually.
	 * @depends testGetGroupDocuments
	 */
	public function getTypes() {
		$docs = array();
		foreach ($result->document_ids as $id) {
			$docs[] = $this->mendeley->get('documents/' . $id);
		}

		$types = array();
		foreach ($docs as $d) {
			$types[$d->type] = null;
		}

		echo var_dump($types);
	}

	/**
	 * This function shows the fields which are usable referring to the Mendeley API error message
	 * Right now the error message (i.e. fields you can use) is equal for all document types
	 */
	public function testGetDocumentTypeFields() {
		$doc = new MendeleyDoc();
		$doc->authors = array('a');
		$doc->keywords = array('a');
		$doc->tags = array('a');
		$doc->title = '1';
		$doc->website = '1';
		$doc->year = 2011;
		$doc->bogus = 1; // will throw error which hopefully explains which fields are allowed

		foreach (MendeleyDoc::getTypes() as $type) {
			$doc->type = $type;
			try {
				$result = $this->mendeley->post('documents/', $doc->toParams());
			}
			catch (Exception $e) {
				$fields[$type] = self::extractMendeleyTypes($e->getMessage());
			}
		}

		// get fields all types have in common
		$intersect = reset($fields);
		foreach ($fields as $f) {
			$intersect = array_intersect($intersect, $f);
		}
		var_export($intersect);

		// remove fields which are common by all types
		foreach ($fields as &$f) {
			$f = array_diff($f, $intersect);
		}
		var_export($fields);
	}

	/**
	 * @param string $message
	 * @return array
	 */
	private static function extractMendeleyTypes($message) {
		$fields = explode(', ', trim(substr($message, strrpos($message, ':') + 1)));
		return $fields;
	}
}
