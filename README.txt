Mendeley API Client
===================

About
-----

You can use this client to easily query the Mendeley API (http://dev.mendeley.com/docs/) from your PHP code.
It accepts URLs from "user specific resources", handles the Authentication via OAuth and returns a PHP object with its results. The authentication is cached.

Usage
-----

1. Download the OAuth library from http://code.google.com/p/oauth/
2. Copy Configuration.sample.php to Configuration.php and change settings

Use the library like this:

    <?php
    require_once 'path/to/mendeleyapi/Mendeley.php';
    $mendeley = new Mendeley();

    // GET request to look up things
    $result = $mendeley->get('sharedcollections/12345'); // $result is now a PHP object with all documents of the shared collection with this id
    $result = $mendeley->getCollection('sharedcollections/12345'); // similar to above, only that $result->documents now contains also all document info

    // POST request to change things, in this case to add a document to a group (collection)
    $doc = new MendeleyDoc();
    $doc->title = 'Example Title';
    $doc->url = 'http://www.example.org/';
    $doc->group_id = 504091;

    try {
      $result = $mendeley->post('documents/', $doc->toParams());
    } catch(Exception $e) {
      echo $e->getMessage();
    }

    // DELETE
    try {
      $mendeley->delete('documents/12345');
    } catch(Exception $e) {
      echo $e->getMessage();
    }
    ?>

Testing
-------

Runs with Simpletest. Point your browser to _webroot_/mendeleyapi/tests/all_tests.php

Versioning
----------

Semantic versioning: http://semver.org/

© 2010 Jakob Stoeck
