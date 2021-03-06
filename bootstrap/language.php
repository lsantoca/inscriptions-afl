<?php
// This module computes the standard language from the Browser
// If this is not among the available languages
// then the default language is from config.ini

// // goal : define the constant LANGUAGE 
// WHICH IS MEANT TO BE THE LANGUAGE TO BE USED 


function first($array) {
    return $array[0];
}

// To be moved to readConfig ?
$availableLanguages = array_map(
        'first', $config['availableLanguages']
);

define('LANGUAGEDEFAULT',$config['defaultLanguage']);

function parseDefaultLanguage($http_accept, $deflang = LANGUAGEDEFAULT) {
    if (isset($http_accept) && strlen($http_accept) > 1) {
        # Split possible languages into array
        $x = explode(",", $http_accept);
        foreach ($x as $val) {
            #check for q-value and create associative array. No q-value means 1 by rule
            if (preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i", $val, $matches))
                $lang[$matches[1]] = (float) $matches[2];
            else
                $lang[$val] = 1.0;
        }

        #return default language (highest q-value)
        $qval = 0.0;
        foreach ($lang as $key => $value) {
            if ($value > $qval) {
                $qval = (float) $value;
                $deflang = $key;
            }
        }
    }
    return strtolower($deflang);
}

function setLanguage($lang) {
    global $availableLanguages;

    if (in_array($lang, $availableLanguages)) {
        define('LANGUAGE', $lang);
        return True;
    }

    return False;
}

function executeLanguage() {
    global $config;

    if (isset($_GET['lang']) &&
            setLanguage($_GET['lang'])
    ) {
        return;
    }

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        //echo $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\n";
        $lang = parseDefaultLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (setLanguage($lang)) {
            return;
        }
    }
    define('LANGUAGE', $config['defaultLanguage']);
}

executeLanguage();
