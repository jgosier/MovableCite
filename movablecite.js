/**
 * (C) 2011 Ahmed Maawy.
 * Date: 10/23/11
 * Time: 10:50 AM
 */

// Default number of words to search for from the start to end of the string
var default_number_of_search_words = 2;
// URL pointing to the read script
var server_read_url = "http://localhost:8888/trials/readxml.php"
// URL pointing to the write script
var server_write_url = "http://localhost:8888/trials/writexml.php"

function Citation_Element(cite, url, current_cite) {
    this.cite = cite;
    this.url = url;
    this.current_cite = current_cite;
}

function Blockquote_Element(cite, block_text) {
    this.cite = cite;
    this.block_text = block_text;
}

function fetch_content_from_url(url) {
    var req;

    if(!window.ActiveXObject) {
        req = new XMLHttpRequest();
    }
    else if(ua.indexOf('msie 5') == -1) {
        req = new ActiveXObject('Msxml2.XMLHTTP');
    }
    else {
        req = new ActiveXObject('Microsoft.XMLHTTP');
    }

    req.open('GET', url, false);
    req.send(null);

    if(req.status == 200) {
        // Success

        return req.responseText;
    }
    else {
        // Fail
        
        return null;
    }
}

function trim_string(text_to_trim) {
    return text_to_trim.replace(/^\s+|\s+$/g, '');
}

function get_first_words(num_words, citation_text) {
    var result_string = "";
    var string_to_search = citation_text;

    for(var loop_index = 0; loop_index < num_words; loop_index ++) {
        var position_of_first_space = string_to_search.indexOf(" ");

        result_string = result_string + " " + trim_string(string_to_search.substr(0, position_of_first_space - 1));

        string_to_search = trim_string(string_to_search.substr(0, position_of_first_space - 1));
    }

    return result_string;
}

function get_last_words(num_words, citation_text) {
    var result_string = "";
    var string_to_search = citation_text;

    for(var loop_index = 0; loop_index < num_words; loop_index ++) {
        var position_of_first_space = string_to_search.lastIndexOf(" ");

        result_string = trim_string(string_to_search.substr(position_of_first_space, string_to_search.length - 1)) + " " + result_string;

        string_to_search = trim_string(string_to_search.substr(position_of_first_space, string_to_search.length - 1));
    }

    return result_string;
}

function read_from_file() {
    return fetch_content_from_url(server_read_url);
}

function write_XML(xml_objects) {
    var req;

    if(!window.ActiveXObject) {
        req = new XMLHttpRequest();
    }
    else if(ua.indexOf('msie 5') == -1) {
        req = new ActiveXObject('Msxml2.XMLHTTP');
    }
    else {
        req = new ActiveXObject('Microsoft.XMLHTTP');
    }

    req.open('POST', server_write_url, false);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Set the POST variables

    var num_elements = xml_objects.length;

    var post_string = "num_objects=" + num_elements;

    for(var element_loop = 0; element_loop < num_elements; element_loop ++) {
        post_string += "&cite_" + element_loop + "=" + xml_objects[element_loop].cite;
        post_string += "&url_" + element_loop + "=" + xml_objects[element_loop].url;
        post_string += "&current_cite_" + element_loop + "=" + xml_objects[element_loop].current_cite;
    }

    req.send(post_string);

    if(req.status == 200) {
        // Success

        return req.responseText;
    }
    else {
        // Fail

        return null;
    }
}

function read_XML() {
    var XML_String = read_from_file();

    this.citiations = new Array();
    
    if(window.DOMParser) {
        // Normal browsers

        parser = new DOMParser();
        xmlDoc = parser.parseFromString(XML_String, "text/xml");
    }
    else {
        // Internet explorer
        
        xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
        xmlDoc.async = "false";
        xmlDoc.loadXML(XML_String);
    }

    cite_tags = xmlDoc.getElementsByTagName("cite");
    cite_urls = xmlDoc.getElementsByTagName("url");
    cite_current = xmlDoc.getElementsByTagName("currentcite");

    this.num_citations = cite_tags.length;

    for(var cite_loop = 0; cite_loop < this.num_citations; cite_loop ++) {
        this.citiations.push(new Citation_Element(cite_tags[cite_loop].childNodes[0].nodeValue,
            cite_urls[cite_loop].childNodes[0].nodeValue, cite_current[cite_loop].childNodes[0].nodeValue));
    }
}

function bodyLoaded() {
    var all_elements = document.getElementsByTagName("*");
    var num_elements = all_elements.length;
    var element_loop;

    this.citations = new Array();

    this.blockquote_elements = new Array();

    for(element_loop = 0; element_loop < num_elements; element_loop++) {

        if(all_elements[element_loop].nodeName == "BLOCKQUOTE") {
            this.blockquote_elements.push(
                new Blockquote_Element(trim_string(all_elements[element_loop].getAttribute("cite")),
                    trim_string(all_elements[element_loop].textContent)));
        }
    }

    this.blockquote_count = this.blockquote_elements.length;

    for(element_loop = 0; element_loop < this.blockquote_count; element_loop ++) {
        var first_words = get_first_words(default_number_of_search_words, blockquote_elements[element_loop].block_text);
        var last_words = get_last_words(default_number_of_search_words, blockquote_elements[element_loop].block_text);

        var citation_site_response = fetch_content_from_url(blockquote_elements[element_loop].cite);

        if(citation_site_response != null) {
            var index_of_first_words = citation_site_response.indexOf(first_words);
            var index_of_last_words = citation_site_response.indexOf(last_words);

            var citation = citation_site_response.substr(index_of_first_words, index_of_last_words);

            if(blockquote_elements[element_loop].block_text != citation) {
                // Record citation
                this.citations.push(new Citation_Element(blockquote_elements[element_loop].block_text,
                    blockquote_elements[element_loop].cite, citation));
            }
        }
    }

    if(this.citations.length > 0) {
        write_XML(this.citations);
    }
}

bodyLoaded();