Mendeley API Client
===================

About
-----

You can use this client to easily query the Mendeley API (http://dev.mendeley.com/docs/) from your PHP code.
It accepts URLs from "user specific resources", handles the Authentication via OAuth and returns a PHP object with its results. The authentication is cached.

Usage
-----

1. Download the OAuth library from http://code.google.com/p/oauth/
2. Change settings in mendeleyapi/Configuration.php

Use the library like this:

    <?php 
    require_once 'path/to/mendeleyapi/Mendeley.php';
    $mendeley = new Mendeley();
    
    // GET request to look up things
    $request = $mendeley->get('sharedcollections/12345'); // $request is now a PHP object with all documents of the shared collection with this id
    
    // POST request to change things, in this case to add a document to a group (collection)
    $doc = new MendeleyDoc();
    $doc->title = 'Example Title';
    $doc->url = 'http://www.example.org/';
    $doc->tags = array('a', 'b');
    $doc->group_id = 504091;
    
    $result = $mendeley->post('documents/', $doc->toParams()));
    ?>

Versioning
----------

Semantic versioning: http://semver.org/

© 2010 Jakob Stoeck
