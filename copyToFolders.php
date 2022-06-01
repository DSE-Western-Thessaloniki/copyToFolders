<?php

/**
 * Μαζική δημιουργία φακέλων σύμφωνα με τα δεδομένα ενός αρχείου csv.
 * php version 7.4
 * 
 * @category Application
 * @package  CopyToFolders
 * @author   Theofilos Intzoglou <int.teo@gmail.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link     https://github.com/DSE-West-Thessaloniki/copyToFolders
 */


/**
 * Αναζήτηση ΑΦΜ στο όνομα φακέλου που βρίσκεται μέσα στο $path. Αν βρεθεί φάκελος
 * τον επιστρέφει μαζί με την διαδρομή αλλιώς επιστρέφει false.
 * 
 * @param $path string
 * @param $afm  string
 * 
 * @return string | false
 */
function findFolder($path, $afm) : string | false
{
    if ($dh = opendir($path)) {
        while (($file = readdir($dh)) !== false) {
            $test_folder = $path . '/' . $file;
            if (is_dir($test_folder) && strstr($test_folder, $afm)) {
                return $test_folder;
            }
        }
        closedir($dh);
    }

    return false;
}


$rest_index = null;
$options = getopt("i:o:", ["input:", "output:"], $rest_index);
$remaining_args = array_slice($argv, $rest_index);
if (!$options || $remaining_args || count($options) != 2) {
    echo "Usage: copyToFolders [Επιλογές]\n";
    echo "Επιλογές:\n";
    echo " -i,--input 'φάκελος' - Ο φάκελος που περιέχει τα αρχεία για αντιγραφή".PHP_EOL;
    echo " -o,--output 'φάκελος' - Ο προορισμός στον οποίο περιέχονται φάκελοι στους οποίους θα αντιγραφούν".PHP_EOL;
    echo "Παρατηρήσεις: Για την αντιστοίχιση χρησιμοποιείται το ΑΦΜ που πρέπει να υπάρχει στο όνομα τόσο των αρχείων".PHP_EOL;
    echo "όσο και των φακέλων προορισμού.".PHP_EOL.PHP_EOL;
    echo "Παράδειγμα: copyToFolders -i Files -o Archive".PHP_EOL;
    exit(0);
}

$src = $options['i'] ?? $options['input'];

$dst = $options['o'] ?? $options['output'];

foreach ([$src, $dst] as $dir) {
    if (!is_dir($dir)) {
        echo "Το {$dir} δεν είναι φάκελος!".PHP_EOL;
        exit(1);
    }
}

if ($dh = opendir($src)) {
    while (($file = readdir($dh)) !== false) {
        if (!is_dir($src.'/'.$file)) {
            if (preg_match('/\d{9}/', $file, $matches)) {
                $target = findFolder($dst, $matches[0]);
                if ($target === false) {
                    echo "Το αρχείο {$file} δεν έχει φάκελο για να διανεμηθεί.";
                } else {
                    copy($src.'/'.$file, $target.'/'.$file);
                }
            } else {
                echo "Το αρχείο {$file} δεν φαίνεται να έχει 9ψήφιο ΑΦΜ!".PHP_EOL;
            }
        }
    }
    closedir($dh);
}