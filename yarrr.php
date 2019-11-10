<?php

/**
 * Plugin Name: Yarrr
 * Plugin URI:  https://github.com/noonjinx/yarrr
 * Description: Translates wordpress content to pirate speak
 * Version:     1.0.0
 * Author:      Jon Nixon
 * Author URI:  https://github.com/noonjinx/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Yarrr - Reformats Wordpress content into pirate speak.
 *
 * Replaces key words with piratic equivalent and randomly adds piratey phrases.
 *
 * @since 1.0.0
 */

class Yarrr
{
    // List of piratey phrases to add to content.

    private static $phrases = array(
        "Yarrr",
        "Arrr",
        "Arrgh",
        "Garrr",
        "Shiver my timbers",
        "Splice the mainbrace",
        "Fifteen men on a dead man's chest",
        "Yo-ho-ho, and a bottle of rum",
        "Drink and the devil",
        "Devil take you",
        "Damn your eyes",
        "Flop like a fish",
        "Fever and leaches",
        "Dance the hempen jig",
        "All hands on deck",
        "Avast you",
        "Sink me",
        "Blast my binnacle",
        "Swash my buckle",
        "Powder my monkey",
        "Batten down the hatches",
        "Blow the man down",
        "Feed the fish",
        "Roger my jolly",
        "Wag my scally",
        "Scuttle my poop",
        "There she blows",
        "Son of a biscuit eater",
        "Three sheets to the wind",
        "Woggle my horn",
        "Dead men tell no tales",
        "Slap my dungbie",
        "Swab the deck"
    );

    // Simple translations, eg. "am" is translated to "be"

    private static $simple_translations = array(
        array("am",       "be"),
        array("are",      "be"),
        array("is",       "be"),
        array("do",       "does"),
        array("was",      "were"),
        array("your",     "yer"),
        array("yours",    "yers"),
        array("you're",   "yer"),
        array("you",      "ye"),
        array("for",      "fer"),
        array("my",       "me"),
        array("it's",     "it be"),
        array("that's",   "that be"),
        array("what's",   "what be"),
        array("they're'", "they be"),
        array("of",       "o'"),
        array("and",      "an'"),
        array("yes",      "aye"),
        array("hello",    "ahoy"),
        array("hi",       "ahoy"),
        array("the",      "thur"),
        array("there",    "thar"),
        array("their",    "thar"),
        array("he",       "hee"),  # "H" will be removed later, so "He" becomes "Hee" becomes "'Ee"
    );

    // Prefixes, eg. Remove leading "H" so "Hold Hands" becomes "'Old Ands"

    private static $prefixes = array(
        array("h", "'"),
    );

    // Suffixes, eg. Remove training "ing" so "Looking" becomes "Lookin'"

    private static $suffixes = array(
        array("ing", "in'"),
    );

    /**
     * Adds random piratic phrases to text.
     *
     * If the text contains full stops then it is broken into seperate strings on those full stops.
     * Adds a randomly selected piratic phrase to the start of randomly selected strings. Reassembles
     * the text string and returns it.
     * 
     * @since 1.0.0
     *
     * @param string $text that is to be modified.
     * @return string with randomly added piratic phrases.
     */

    private static function addPhrases($text)
    {
        // Occasionally add random phrases to parts of the text 

        if (strpos($text, '.') !== false) {
            $parts = explode('.', $text);
            foreach ($parts as $idx => $part) {
                // Don't add to empty strings an donly add occasionally
                if (strlen(trim($part)) > 0 && !rand(0, 4)) {
                    $parts[$idx] = self::$phrases[rand(0, count(self::$phrases) - 1)] . ". $part";
                    if ($idx > 0) {
                        $parts[$idx] = " " . $parts[$idx];
                    }
                }
            }
            $text = implode('.', $parts);
        }

        return $text;
    }


    /**
     * Translates text.
     *
     * Swaps keywords in the input text for piratey equivalents.
     * 
     * @since 1.0.0
     *
     * @param string $text containing text to be reformatted.
     * @return string containing reformatted text.
     */

    private static function translate($text)
    {
        // Process simple translations

        foreach (self::$simple_translations as $translation) {

            // Translate upper first, eg. "Am" becomes "Be"

            $source = ucfirst(strtolower($translation[0]));           # eg. "Am"
            $target = ucfirst(strtolower($translation[1]));           # eg. "Be"
            $text = preg_replace("/\b${source}\b/", $target, $text);  # eg. Change "Am" to "Be"

            # Translate upercase, eg. "AM" becomes "BE"

            $source = strtoupper($translation[0]);                    # eg. "AM"
            $target = strtoupper($translation[1]);                    # eg. "BE"
            $text = preg_replace("/\b${source}\b/", $target, $text);  # eg. Change "AM" to "BE"

            # Translate lowercase, eg. "am" becomes "be". Uses /i to be a catchall

            $source = strtolower($translation[0]);                    # eg. "am"
            $target = strtolower($translation[1]);                    # eg. "be"
            $text = preg_replace("/\b${source}\b/i", $target, $text); # eg. Change "am" to "be"

        }

        // Remove prefixes, moving case to next letter, eg. "Hello" becomes "Ello"

        foreach (self::$prefixes as $prefix) {

            // Translate uppercase, eg. "Hello" becomes "Ello"

            $source = strtoupper($prefix[0]);                 # eg. "H"
            $target = strtoupper($prefix[1]);                 # eg. "'"
            $text = preg_replace_callback(
                "/\b${source}(\w)/",                          # eg. "H(e)"
                function ($matches) use ($target) {
                    return strtoupper($target . $matches[1]); # eg. "E"
                },
                $text
            );

            // Translate lowercase, eg. "hello" becomes "ello". Uses /i as catchall

            $source = strtolower($prefix[0]);                 # eg. "h"
            $target = strtolower($prefix[1]);                 # eg. "'"
            $text = preg_replace_callback(
                "/\b${source}(\w)/i",                         # eg. "h(e)"
                function ($matches) use ($target) {
                    return strtolower($target . $matches[1]); # eg. "e"
                },
                $text
            );
        }

        // Remove suffixes, eg. "looking" becomes "lookin'"

        foreach (self::$suffixes as $suffix) {

            // Translate uppercase, eg. "LOOKING" becomes "LOOKIN'"

            $source = strtoupper($suffix[0]);       # eg. "ING"
            $target = strtoupper($suffix[1]);       # eg. "IN'"
            $text = preg_replace_callback(
                "/${source}\b/",                    # eg. "ING"
                function ($matches) use ($target) {
                    return strtoupper($target);     # eg. "IN'"
                },
                $text
            );

            // Translate lowercase, eg. "looking" becomes "lookin'"

            $source = strtolower($suffix[0]);       # eg. "ing"
            $target = strtolower($suffix[1]);       # eg. "in'"
            $text = preg_replace_callback(
                "/${source}\b/",                    # eg. "ing"
                function ($matches) use ($target) {
                    return strtolower($target);     # eg. "in'"
                },
                $text
            );
        }

        return $text;
    }

    /**
     * Parses a DOMnode, reformats any text it contains.
     *
     * Recursive function. Identifies childNodes and reprocesses each one. Extracts the
     * text from the node being processed and calls other functions to reformat it.
     * Replaces the text in the node being processed.
     * 
     * @since 1.0.0
     *
     * @param DomNode containing HTML wordpress content.
     */

    private static function parseNode(DOMNode $node)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode->hasChildNodes()) {
                self::parseNode($childNode);
            } elseif ($text = $childNode->textContent) {
                $text = self::addPhrases($text);
                $text = self::translate($text);
                $childNode->textContent = $text;
            }
        }
    }

    /**
     * Acts as a filter aallowing wordpress content to be reformatted.
     *
     * Passes content to parser and prints refornatted text.
     *
     * @since 1.0.0
     *
     * @param string $content HTML wordpress content.
     * @return string Reformatted HTML wordpress content.
     */

    public static function translateFilter($content)
    {
        $doc = new DOMDocument();
        $doc->loadHTML($content);
        self::parseNode($doc);
        print $doc->saveHTML();
    }
}

// Yarrr::translateFilter is a static function so no need to instantiate class

add_filter('the_content', 'Yarrr::translateFilter');
