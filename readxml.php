<?php
/**
 * Copyright 2011 Jon Gosier & Ahmed Maawy
 * Coded by Ahmed Maawy
 * Date: 10/23/11
 * Time: 10:50 AM
 */

include_once("movablecite.php");

$xml = simplexml_load_file(CITATAIONS_FILE);
header("Content-type: text/xml");
echo($xml->asXML());