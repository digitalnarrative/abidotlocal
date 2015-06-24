<?php


/* 

This code is not currently in use with the UserPress plugin. The code works fine however it WordPress simply redirects cat to Cat.

The purpose of this code is to allow URIs with uppercase letters (much like Wikipedia/MediaWiki).

This is especially helpful when dealing with multiple pages with the same title/slug (e.g. Cats vs cats -- the musical vs the animal).

SOURCE: 
http://wordpress.stackexchange.com/questions/5029/how-can-i-make-capital-letter-upper-case-permalinks 


*/


// First we need to remove the "sanitize_title" function

remove_filter( 'sanitize_title', 'sanitize_title_with_dashes' );


// Now we will reinstate the function with a few modifications

add_filter( 'sanitize_title', 'userpress_sanitize_title_with_dashes' );
function userpress_sanitize_title_with_dashes($title) {
    $title = strip_tags($title);
    // Preserve escaped octets.
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    // Remove percent signs that are not part of an octet.
    $title = str_replace('%', '', $title);
    // Restore octets.
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

    $title = remove_accents($title);
    if (seems_utf8($title)) {
        //if (function_exists('mb_strtolower')) {
        //    $title = mb_strtolower($title, 'UTF-8');
        //}
        $title = utf8_uri_encode($title, 200);
    }

    //$title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title); // kill entities
    $title = str_replace('.', '-', $title);
    // Keep upper-case chars too!
    $title = preg_replace('/[^%a-zA-Z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');

    return $title;
}
?>