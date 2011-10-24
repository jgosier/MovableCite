<?php
/**
 * (C) 2011 Ahmed Maawy
 * Date: 10/24/11
 * Time: 9:54 AM
 */
 
define("CITATAIONS_FILE", "citations.xml");

class Citation_Element {
    public $cite;
    public $url;
    public $current_cite;

    public function __construct($cite = "", $url = "", $current_cite = "") {
        $this->cite = $cite;
        $this->url = $url;
        $this->current_cite = $current_cite;
    }
}