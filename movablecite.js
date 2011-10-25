/**
 * (C) 2011 Ahmed Maawy.
 * Date: 10/23/11
 * Time: 10:50 AM
 */

// URL pointing to the read script
var server_read_url = "http://localhost/movablecite/readxml.php"
// URL pointing to the write script
var server_write_url = "http://localhost/movablecite/writexml.php"

// Data types

function Citation_Element(cite, url, current_cite) {
    this.cite = cite;
    this.url = url;
    this.current_cite = current_cite;
}

function Blockquote_Element(cite, block_text) {
    this.cite = cite;
    this.block_text = block_text;
}

// Utility functions

function fetch_content_from_url(url) {
    var response = "";
    
    $.get(url, function(result) {
        response = result;
    });

    return response;
}

function trim_string(text_to_trim) {
    return $.trim(text_to_trim);
}

function read_from_file() {
    return fetch_content_from_url(server_read_url);
}

// Sends to server

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

// Processes XML sent from server

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

// Main function

function bodyLoaded() {
    var all_elements = document.getElementsByTagName("*");
    var num_elements = all_elements.length;
    var element_loop;

    this.citations = new Array();

    this.blockquote_elements = new Array();

    // Find all blockquote elements

    for(element_loop = 0; element_loop < num_elements; element_loop++) {

        if(all_elements[element_loop].nodeName == "BLOCKQUOTE") {
            this.blockquote_elements.push(
                new Blockquote_Element(trim_string(all_elements[element_loop].getAttribute("cite")),
                    trim_string(all_elements[element_loop].textContent)));
        }
    }

    this.blockquote_count = this.blockquote_elements.length;

    for(element_loop = 0; element_loop < this.blockquote_count; element_loop ++) {
        this.citations.push(new Citation_Element(blockquote_elements[element_loop].block_text,
                    blockquote_elements[element_loop].cite,
                    blockquote_elements[element_loop].block_text));
    }

    if(this.citations.length > 0) {
        write_XML(this.citations);
    }
}

bodyLoaded();