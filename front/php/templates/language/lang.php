<?php

// ###################################
// ## Languages
// ###################################

$defaultLang = "en_us";
$allLanguages = ["en_us", "es_es", "de_de", "fr_fr", "it_it", "ru_ru", "nb_no", "pl_pl", "pt_br", "tr_tr", "zh_cn", "cs_cz"];


global $db;

$result = $db->querySingle("SELECT Value FROM Settings WHERE Code_Name = 'UI_LANG'"); 
switch($result){    
  case 'Spanish': $pia_lang_selected = 'es_es'; break;
  case 'German': $pia_lang_selected = 'de_de'; break;
  case 'Norwegian': $pia_lang_selected = 'nb_no'; break;
  case 'Polish (pl_pl)': $pia_lang_selected = 'pl_pl'; break;
  case 'Portuguese (pt_br)': $pia_lang_selected = 'pt_br'; break;
  case 'Italian (it_it)': $pia_lang_selected = 'it_it'; break;
  case 'Russian': $pia_lang_selected = 'ru_ru'; break;
  case 'Turkish (tr_tr)': $pia_lang_selected = 'tr_tr'; break;
  case 'French': $pia_lang_selected = 'fr_fr'; break;
  case 'Chinese (zh_cn)': $pia_lang_selected = 'zh_cn'; break;
  case 'Czech (cs_cz)': $pia_lang_selected = 'cs_cz'; break;
  default: $pia_lang_selected = 'en_us'; break;
}

if (isset($pia_lang_selected) == FALSE or (strlen($pia_lang_selected) == 0)) {$pia_lang_selected = $defaultLang;}

require dirname(__FILE__).'/../skinUI.php';

$result = $db->query("SELECT * FROM Plugins_Language_Strings");
$strings = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $strings[$row['String_Key']] = $row['String_Value'];
}


function getLanguageDataFromJson()
{
    global $allLanguages;

    // Array to hold the language data from the JSON files
    $languageData = [];

    foreach ($allLanguages as $language) {
        // Load and parse the JSON data from .json files
        $jsonFilePath = dirname(__FILE__) . '/' . $language . '.json';

        if (file_exists($jsonFilePath)) {
            $data = json_decode(file_get_contents($jsonFilePath), true);

            // Adjusting for the changed JSON format
            $languageData[$language] = $data;
        } else {
            // Handle the case where the JSON file doesn't exist
            // For example, you might want to log an error message
            echo 'File not found: ' . $jsonFilePath;
        }
    }

    return $languageData;
}

// Merge the JSON data with the SQL data, giving priority to SQL data for overlapping keys
function mergeLanguageData($jsonLanguageData, $sqlLanguageData)
{
    // Loop through the JSON language data and check for overlapping keys
    foreach ($jsonLanguageData as $languageCode => $languageStrings) {
        foreach ($languageStrings as $key => $value) {
            // Check if the key exists in the SQL data, if yes, use the SQL value
            if (isset($sqlLanguageData[$key])) {
                $jsonLanguageData[$languageCode][$key] = $sqlLanguageData[$key];
            }
        }
    }

    return $jsonLanguageData;
}

function lang($key)
{
    global $pia_lang_selected, $strings;

    // Get the data from JSON files
    $languageData = getLanguageDataFromJson();

    // Get the data from SQL query
    $sqlLanguageData = $strings;

    // Merge JSON data with SQL data
    $mergedLanguageData = mergeLanguageData($languageData, $sqlLanguageData);

    // Check if the key exists in the selected language
    if (isset($mergedLanguageData[$pia_lang_selected][$key]) && $mergedLanguageData[$pia_lang_selected][$key] != '') {
        $result = $mergedLanguageData[$pia_lang_selected][$key];
    } else {
        // If key not found in selected language, use "en_us" as fallback
        if (isset($mergedLanguageData['en_us'][$key])) {
            $result = $mergedLanguageData['en_us'][$key];
        } else {
            // If key not found in "en_us" either, use a default string
            $result = "String Not found for key " . $key;
        }
    }

    // HTML encode the result before returning
    return str_replace("'", '&#39;', $result);
}


