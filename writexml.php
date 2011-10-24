<?php
/**
 * (C) 2011 Ahmed Maawy
 * Date: 10/23/11
 * Time: 10:36 PM
 */

include_once("movablecite.php");

$xml = simplexml_load_file(CITATAIONS_FILE);

// Posted elements via HTTP
$post_array = array();
$num_objects = $_POST['num_objects'];

// An array list of elements to add
$addition_array = array();

// An array list of elements to update
$update_array = array();

for($my_array_loop = 0; $my_array_loop < $num_objects; $my_array_loop ++) {
    $citation_element = new Citation_Element($_POST["cite_$my_array_loop"],
        $_POST["url_$my_array_loop"],
        $_POST["current_cite_$my_array_loop"]);

    array_push($post_array, $citation_element);
}

foreach($xml->children() as $xml_child) {
    foreach($post_array as $citation) {

        $current_element = new Citation_Element();

        foreach($xml_child as $xml_citation_child) {
            switch($xml_citation_child->getName()) {
                case "cite":
                    $current_element->cite = $xml_citation_child;
                    break;
                case "url":
                    $current_element->url = $xml_citation_child;
                    break;
                case "currentcite":
                    $current_element->current_cite = $xml_citation_child;
                    break;
            }
        }

        // Check to see if it exists - update or add new XML element?
        
        if(($current_element->cite == $citation->cite) && ($current_element->url == $citation->url) &&
                ($current_element->current_cite == $citation->current_cite)) {
            // Do nothing
        }
        else if(($current_element->cite == $citation->cite) && ($current_element->url == $citation->url) &&
                ($current_element->current_cite != $citation->current_cite)) {
            // Update
            array_push($update_array, $current_element);
        }
        else {
            array_push($addition_array, $current_element);
        }
    }
}

// Update elements that need to update in the XML

$xml_string = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";

foreach($xml as $xml_child) {
    foreach($update_array as $update_array_element) {
        $cite = "";
        $url = "";
        $current_cite = "";

        foreach($xml_child as $xml_cite_child) {
            if($xml_cite_child->getName() == "cite") {
                if($xml_cite_child == $update_array_element->cite) {
                    $cite = $update_array_element->cite;
                }
                else {
                    $cite = $xml_cite_child;
                }
            }
            else if($xml_cite_child->getName() == "url") {
                if($xml_cite_child == $update_array_element->url) {
                    $url = $update_array_element->url;
                }
                else {
                    $url = $xml_cite_child;
                }
            }
            else if($xml_cite_child->getName() == "currentcite") {
                $current_cite = $xml_cite_child;
            }
        }

        $xml_string.="<citation><cite>$cite</cite><url>$url</url><currentcite>$current_cite</currentcite></citation>";
    }
}

foreach($addition_array as $addition_element) {
    $xml_string.="<citation><cite>".$addition_element->cite."</cite><url>".$addition_element->url."</url><currentcite>".$addition_element->current_cite."</currentcite></citation>";
}

file_put_contents(CITATAIONS_FILE, $xml_string);