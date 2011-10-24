<?php
/**
 * (C) 2011 Ahmed Maawy
 * Date: 10/23/11
 * Time: 10:36 PM
 */

include_once("movablecite.php");

$xml = simplexml_load_file(CITATAIONS_FILE);
header("Content-type: text/xml");
echo($xml->asXML());