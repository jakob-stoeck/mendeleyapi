<?php
class MendeleyBiblioDoc extends MendeleyDoc {
	/**
	 * Contributor types
	 */
	const BIBLIO_AUTHOR = 1;
	const BIBLIO_SECONDARY_AUTHOR = 2;
	const BIBLIO_TERTIARY_AUTHOR = 3;
	const BIBLIO_SUBSIDIARY_AUTHOR = 4;
	const BIBLIO_CORPORATE_AUTHOR = 5;
	const BIBLIO_SERIES_EDITOR = 10;
	const BIBLIO_PERFORMERS = 11;
	const BIBLIO_SPONSOR = 12;
	const BIBLIO_TRANSLATOR = 13;
	const BIBLIO_EDITOR = 14;
	const BIBLIO_COUNSEL = 15;
	const BIBLIO_SERIES_DIRECTOR = 16;
	const BIBLIO_PRODUCER = 17;
	const BIBLIO_DEPARTMENT = 18;
	const BIBLIO_ISSUING_ORGANIZATION = 19;
	const BIBLIO_INTERNATIONAL_AUTHOR = 20;
	const BIBLIO_RECIPIENT = 21;
	const BIBLIO_ADVISOR = 22;

	/**
	 * Document types
	 */
	const BIBLIO_BOOK = 100;
	const BIBLIO_BOOK_CHAPTER = 101;
	const BIBLIO_JOURNAL_ARTICLE = 102;
	const BIBLIO_CONFERENCE_PAPER = 103;
	const BIBLIO_CONFERENCE_PROCEEDINGS = 104;
	const BIBLIO_NEWSPAPER_ARTICLE = 105;
	const BIBLIO_MAGAZINE_ARTICLE = 106;
	const BIBLIO_WEB_ARTICLE = 107;
	const BIBLIO_THESIS = 108;
	const BIBLIO_REPORT = 109;
	const BIBLIO_FILM = 110;
	const BIBLIO_BROADCAST = 111;
	const BIBLIO_ARTWORK = 112;
	const BIBLIO_SOFTWARE = 113;
	const BIBLIO_AUDIOVISUAL = 114;
	const BIBLIO_HEARING = 115;
	const BIBLIO_CASE = 116;
	const BIBLIO_BILL = 117;
	const BIBLIO_STATUTE = 118;
	const BIBLIO_PATENT = 119;
	const BIBLIO_PERSONAL = 120;
	const BIBLIO_MANUSCRIPT = 121;
	const BIBLIO_MAP = 122;
	const BIBLIO_CHART = 123;
	const BIBLIO_UNPUBLISHED = 124;
	const BIBLIO_DATABASE = 125;
	const BIBLIO_GOVERNMENT_REPORT = 126;
	const BIBLIO_CLASSICAL = 127;
	const BIBLIO_LEGAL_RULING = 128;
	const BIBLIO_MISCELLANEOUS = 129;
	const BIBLIO_MISCELLANEOUS_SECTION = 130;

	/**
	 * Returns a map biblio keys => mendeley keys
	 * 
	 * @param boolean $flip
	 * 	set true to get mendeley => biblio
	 * @return array
	 */
	private static function map($flip = false) {
		// MendeleyDoc properties I haven't mapped so far:
		// public $city;
		// public $country;
		// public $discipline;
		// public $editors;
		// public $genre;
		// public $group_id;
		// public $identifiers;
		// public $institution;
		// public $pmid;
		// public $publication_outlet;
		// public $tags;

		$biblioToMendeleyFields = array_filter(array(
			'biblio_abst_e' => 'abstract',
			'biblio_abst_f' => null,
			'biblio_accession_number' => null,
			'biblio_access_date' => null,
			'biblio_alternate_title' => null,
			'biblio_authors' => 'authors', // biblio sends a $node->biblio_contributors which includes authors, editors, @see contributor types
			'biblio_auth_address' => null,
			'biblio_call_number' => null,
			'biblio_citekey' => null,
			'biblio_coins' => null,
			'biblio_corp_authors' => null,
			'biblio_custom1' => null,
			'biblio_custom2' => null,
			'biblio_custom3' => null,
			'biblio_custom4' => null,
			'biblio_custom5' => null,
			'biblio_custom6' => null,
			'biblio_custom7' => null,
			'biblio_date' => null,
			'biblio_doi' => 'doi',
			'biblio_edition' => 'edition',
			'biblio_isbn' => 'isbn',
			'biblio_issn' => 'issn',
			'biblio_issue' => 'issue',
			'biblio_keywords' => 'keywords',
			'biblio_label' => null,
			'biblio_lang' => null,
			'biblio_notes' => 'notes',
			'biblio_number' => null,
			'biblio_number_of_volumes' => null,
			'biblio_original_publication' => null,
			'biblio_other_author_affiliations' => null,
			'biblio_other_number' => null,
			'biblio_pages' => 'pages',
			'biblio_place_published' => null,
			'biblio_publisher' => 'publisher',
			'biblio_refereed' => null,
			'biblio_remote_db_name' => null,
			'biblio_remote_db_provider' => null,
			'biblio_reprint_edition' => null,
			'biblio_research_notes' => null,
			'biblio_secondary_authors' => null,
			'biblio_secondary_title' => null,
			'biblio_section' => null,
			'biblio_short_title' => null,
			'biblio_subsidiary_authors' => null,
			'biblio_tertiary_authors' => null,
			'biblio_tertiary_title' => null,
			'biblio_translated_title' => null,
			'biblio_type' => 'type', // needs callback $this->typeToBiblio()
			'biblio_type_of_work' => null,
			'biblio_url' => 'url',
			'biblio_volume' => 'volume',
			'biblio_year' => 'year',
			'title' => 'title',
		));

		if($flip) {
			$biblioToMendeleyFields = array_flip($biblioToMendeleyFields);
		}

		return $biblioToMendeleyFields;
	}

	/**
	 * Returns array usable by biblio
	 * 
	 * You can build a MendeleyBiblioDoc from the MendeleyAPI and send it to biblio to save
	 * 
	 * @return array
	 */
	public function toBiblio() {
		$map = self::map();

		foreach($map as $biblioKey => &$mendeley) {
			$mendeley = $this->$mendeley;
		}

		$map['biblio_type'] = self::mendeleyToBiblioType($this->type);

		return array_filter($map);
	}

	/**
	 * Constructs a MendeleyBiblioDoc from a Biblio node
	 * 
	 * Can be sent to Mendeley to post a Document which was created in Biblio
	 * 
	 * @return MendeleyBiblioDoc
	 */
	public static function constructWithNode($node) {
		$that = new MendeleyBiblioDoc();

		$mendeleyKeys = array_keys(get_object_vars($that));
		$map = self::map(true);

		foreach($mendeleyKeys as $m) {
			if(isset($map[$m])) {
				$biblioKey = $map[$m];
			} 

			if(isset($node->$biblioKey)) {
				$that->$m = $node->$biblioKey;
			}
		}

		$that->type = self::biblioToMendeleyType($node->$map['type']);

		foreach($node->biblio_contributors as $biblioContribKey => $contribs) {
			foreach($contribs as $key => &$values) {
				$values = $values['name'];
			}

			switch($biblioContribKey) {
				case self::BIBLIO_AUTHOR:
					$that->authors = $contribs;
				break;

				case self::BIBLIO_EDITOR:
					$that->editors = $contribs;
				break;
			}
		}

		return $that;
	}

	/**
	 * @todo more types
	 */
	public static function mendeleyToBiblioType($mendeleyType) {
		switch($mendeleyType) {
			case 'Magazine Article':
				return self::BIBLIO_MAGAZINE_ARTICLE;
			break;

			default:
				return self::BIBLIO_MISCELLANEOUS;
		}
	}

	/**
	 * @todo more types
	 * @return int
	 * 	biblio type id
	 */
	public static function biblioToMendeleyType($biblioType) {
		$biblioToMendeley = array(
			self::BIBLIO_MAGAZINE_ARTICLE => 'Magazine Article',
		);

		if(isset($biblioToMendeley[$biblioType])) {
			return $biblioToMendeley[$biblioType];
		} else {
			return 'misc';
		}
	}
}