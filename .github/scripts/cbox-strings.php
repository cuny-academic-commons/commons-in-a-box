#!/usr/bin/env php
<?php
/**
 * CBOX Strings Utility
 * 
 * Generates dummy translation files for sister packages.
 * This script extracts strings from sister packages and creates dummy PHP files
 * with the commons-in-a-box textdomain so they can be picked up by make-pot.
 * 
 * Based on the original cbox-strings WP-CLI command.
 */

// Check if we're running from the correct directory
if (!file_exists('loader.php')) {
    echo "Error: This script must be run from the commons-in-a-box plugin root directory.\n";
    exit(1);
}

// Load composer autoloader for Gettext library
if (!file_exists('vendor/autoload.php')) {
    echo "Error: Composer dependencies not found. Installing...\n";
    exec('composer require gettext/gettext --quiet 2>&1', $output, $return);
    if ($return !== 0) {
        echo "Error: Failed to install dependencies.\n";
        exit(1);
    }
}

require_once 'vendor/autoload.php';

use Gettext\Translations;
use Gettext\Extractors\PhpCode;

const TEXTDOMAIN = 'commons-in-a-box';

/**
 * Main execution function.
 */
function init($base_dir) {
    $strings_dir = $base_dir . '/languages/strings/';
    if (!file_exists($strings_dir)) {
        mkdir($strings_dir, 0755, true);
    }

    foreach (get_dirs($base_dir) as $dir) {
        // If our directory doesn't exist, skip!
        if (!file_exists($dir)) {
            echo "Skipping {$dir} (not found)\n";
            continue;
        }

        $textdomain = basename($dir);
        echo "Processing {$textdomain}...\n";

        // File header.
        $lines = [];
        $lines[] = '<?php';
        $lines[] = sprintf('/**
 * %1$s
 *
 * This is a dummy PHP file meant to be picked up by GlotPress for
 * translation purposes on wordpress.org.
 *
 * Apart from that, this file is not used or loaded anywhere.
 */
', $textdomain);

        $translations = new Translations();

        // Extract strings from all PHP files in the directory
        extract_from_directory($dir, $translations);

        // Reformat translations to use our 'commons-in-a-box' textdomain.
        foreach ($translations as $t) {
            $line = '';
            if ($t->hasExtractedComments()) {
                $lines[] = '';
                $lines[] = sprintf('/* %s */', $t->getExtractedComments()[0]);
            }

            if ($t->hasContext()) {
                if ($t->hasPlural()) {
                    $line = sprintf("_nx_noop( '%1\$s', '%2\$s', '%3\$s', '%4\$s' );", 
                        addcslashes($t->getOriginal(), "'"), 
                        addcslashes($t->getPlural(), "'"), 
                        addcslashes($t->getContext(), "'"), 
                        TEXTDOMAIN);
                } else {
                    $line = sprintf("_x( '%1\$s', '%2\$s', '%3\$s' );", 
                        addcslashes($t->getOriginal(), "'"), 
                        addcslashes($t->getContext(), "'"), 
                        TEXTDOMAIN);
                }
            } elseif ($t->hasPlural()) {
                $line = sprintf("_n_noop( '%1\$s', '%2\$s', '%3\$s' );", 
                    addcslashes($t->getOriginal(), "'"), 
                    addcslashes($t->getPlural(), "'"), 
                    TEXTDOMAIN);
            } else {
                $line = sprintf("__( '%1\$s', '%2\$s' );", 
                    addcslashes($t->getOriginal(), "'"), 
                    TEXTDOMAIN);
            }

            $lines[] = $line;

            if ($t->hasExtractedComments()) {
                $lines[] = '';
            }
        }

        // Delete older file.
        $file = $strings_dir . $textdomain . '.php';
        if (file_exists($file)) {
            unlink($file);
        }

        // Output time!
        file_put_contents($file, implode("\n", $lines));

        echo "Dummy translation file generated for {$textdomain} at {$file}.\n";
    }
}

/**
 * Extract translations from all PHP files in a directory.
 */
function extract_from_directory($dir, &$translations) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            try {
                PhpCode::fromFile($file->getPathname(), $translations);
            } catch (Exception $e) {
                echo "Warning: Failed to extract from {$file->getPathname()}: {$e->getMessage()}\n";
            }
        }
    }
}

/**
 * Returns list of plugin/theme filepaths meant for dummy translations.
 *
 * @return array
 */
function get_dirs($base_dir) {
    $plugins = [
        'cbox-openlab-core',
        'bp-event-organiser',
        'bp-group-announcements',
        'external-group-blogs',
    ];

    $themes = [
        'openlab-theme',
    ];

    $dirs = [];

    foreach ($plugins as $plugin) {
        $dirs[] = $base_dir . '/tmp/sister-packages/' . $plugin;
    }

    foreach ($themes as $theme) {
        $dirs[] = $base_dir . '/tmp/sister-packages/' . $theme;
    }

    return $dirs;
}

// Run the script
$base_dir = getcwd();
init($base_dir);
echo "String extraction complete!\n";
