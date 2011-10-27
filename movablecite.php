<?php
/**
* MovableCite keeps your web citations in sync.
*    Copyright (C) 2011 Jonathan D. Gosier
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see http://gnu.org/licenses.
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