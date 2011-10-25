<?php
/**
 * (C) 2011 Ahmed Maawy
 * Date: 10/23/11
 * Time: 10:36 PM
 */

include_once("movablecite.php");

// Get first X number of strings

function get_first_strings($string, $num_strings) {
    $strings = explode(" ", $string);
    $result_string = $strings[0];

    for($string_loop = 1; $string_loop < $num_strings; $string_loop ++) {
        $result_string.=" ".$strings[$string_loop];
    }

    return $result_string;
}

// Get last X number of strings

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

    // Remove HTML tags from the content

    $website_content = strip_tags($website_content);

    // Find the first x and last x words from citation

    $first_words = get_first_strings($citation_element->cite, DEFAULT_CITATAION_SEARCH_WORDS);
    $last_words = get_last_strings($citation_element->cite, DEFAULT_CITATAION_SEARCH_WORDS);

    $first_words_pos = 0;
    $last_words_pos = 0;

    do {
        $first_words_pos = strrpos($website_content, $first_words, $first_words_pos);
        $last_words_pos = strrpos($website_content, $last_words, $first_words_pos);

        if($first_words_pos === false || $last_words_pos === false) {
            break;
        }
    }
    while($first_words_pos >= $last_words_pos);

    $citation = substr($website_content, $first_words_pos, $last_words_pos);

    if($first_words_pos === false || $last_words_pos === false) {
        $post_array[$current_post_array_index]->current_cite = "";
    }
    else if($citation_element->current_cite != $citation) {
        $post_array[$current_post_array_index]->current_cite = $citation;
        $post_array[$current_post_array_index]->modified = true;
    }

    $current_post_array_index ++;
}

$xml_string = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><citations>";

foreach($post_array as $citation_element) {

    $cite = $citation_element->cite;
    $url = $citation_element->url;
    $current_cite = $citation_element->current_cite;

    $xml_string.="<citation><cite>$cite</cite><url>$url</url><currentcite>$current_cite</currentcite></citation>";
}

$xml_string.="</citations>";

file_put_contents(CITATAIONS_FILE, $xml_string);