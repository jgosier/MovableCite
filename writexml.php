<?php
/**
 * (C) 2011 Ahmed Maawy
 * Date: 10/23/11
 * Time: 10:36 PM
 */

include_once("movablecite.php");

function get_first_strings($string, $num_strings) {
    $strings = explode(" ", $string);
    $result_string = $strings[0];

    for($string_loop = 1; $string_loop < $num_strings; $string_loop ++) {
        $result_string.=" ".$strings[$string_loop];
    }

    return $result_string;
}

function get_last_strings($string, $num_strings) {
    $strings = explode(" ", $string);
    $string_count = count($strings);

    $result_string = $strings[$num_strings];

    for($string_loop = ($string_count - 1); $string_loop < ($string_count - 1) - $num_strings; $string_loop ++) {
        $result_string = $strings[$string_loop]." ".$result_string;
    }

    return $result_string;
}

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

// URL verification phase

$current_post_array_index = 0;

foreach($post_array as $citation_element) {
    $website_content = file_get_contents($citation_element->url);

    $first_words = get_first_strings($citation_element->cite, DEFAULT_CITATAION_SEARCH_WORDS);
    $last_words = get_last_strings($citation_element->cite, DEFAULT_CITATAION_SEARCH_WORDS);

    $first_words_pos = 0;
    $last_words_pos = 0;

    do {
        $first_words_pos = strrpos($website_content, $first_words, $first_words_pos);
        $last_words_pos = strrpos($website_content, $last_words, $first_words_pos);
    }
    while($first_words_pos >= $last_words_pos);

    $citation = substr($website_content, $first_words_pos, $last_words_pos);

    if($citation_element->current_cite != $citation) {
        $post_array[$current_post_array_index]->current_cite = $citation;
        $post_array[$current_post_array_index]->modified = true;
    }

    $current_post_array_index ++;
}

foreach($xml->children() as $xml_child) {
    $current_post_array_index = 0;

    foreach($post_array as $citation) {

        $current_element = new Citation_Element();
        $current_element->modified = false;
        $current_element->added = false;

        foreach($xml_child->children() as $xml_citation_child) {
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

        $current_post_array_index ++;
    }
}

// Update elements that need to update in the XML

$xml_string = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><citations>";

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

$xml_string.="</citations>";

file_put_contents(CITATAIONS_FILE, $xml_string);