<?php
/**
 * Copyright 2011 Jon Gosier & Ahmed Maawy
 * Coded by Ahmed Maawy
 * Date: 10/23/11
 * Time: 10:50 AM
 */
 
define("CITATAIONS_FILE", "citations.xml");
define("DEFAULT_CITATAION_SEARCH_WORDS", 3);

class Citation_Element {
    public $cite;
    public $url;
    public $current_cite;
    public $modified;
    public $added;

    public $first_words;
    public $last_words;

    public $citation_found;

    public function __construct($cite = "", $url = "", $current_cite = "") {
        $this->cite = $cite;
        $this->url = $url;
        $this->current_cite = $current_cite;
        $this->modified = false;
        $this->added = true;
    }
}