<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Documents\Concerns;

use Illuminate\Support\Facades\File;

trait Utf8Glyphs
{
    /** @var array<string> */
    private $numeralHindu = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    /** @var array<string> */
    private $numeralPersian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    /** @var array<string> */
    private $numeral = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /**
     * Convert Persian string into glyph joining in UTF-8
     * hexadecimals stream (take care of whole the document including English
     * sections as well as numbers and arcs etc...)
     *
     * @param  string  $text  Persian string
     * @param  int  $max_chars  Max number of chars you can fit in one line
     * @param  bool  $hindo  If true use Hindo digits else use Persian digits
     * @param  bool  $forcertl  If true forces RTL in the bidi algorithm
     * @return string Persian glyph joining in UTF-8 hexadecimals stream (take
     *                care of whole document including English sections as well
     *                as numbers and arcs etc...)
     */
    public function utf8Glyphs($text, $maxChars = 150, $hindo = false, $forceRtl = false): string
    {
        $lines = [];
        $pairs = [];

        $harakat = ['َ', 'ً', 'ُ', 'ٌ', 'ِ', 'ٍ'];
        foreach ($harakat as $haraka) {
            $pairs["ّ$haraka"] = "{$haraka}ّ";
        }

        $text = strtr($text, $pairs);

        // process by line required for bidi in RTL case
        $userLines = explode("\n", $text);

        foreach ($userLines as $line) {
            // wrape long lines for bidi in RTL case
            while (mb_strlen($line) > $maxChars) {
                // find the last space before hit the max line length
                $last = mb_strrpos(mb_substr($line, 0, $maxChars), ' ');

                // add it as a new line in the lines array
                $lines[] = mb_substr($line, 0, $last);

                // the rest of the line will be our new line now to iterate
                $line = mb_substr($line, $last + 1, mb_strlen($line) - $last);
            }

            $lines[] = $line;
        }

        $outLines = [];

        foreach ($lines as $str) {
            // identify Persian fragments in the line for glyphs
            $p = $this->identify($str);

            // check if current line has any Persian fragment
            if (count($p) > 0) {
                // rtl if the current line starts by Persian or the whole text is forced to be rtl
                if ($forceRtl == true || $p[0] == 0) {
                    $rtl = true;
                } else {
                    $rtl = false;
                }

                // block structure to save processed fragments
                $block = [];

                // if line does not start by Persian, then save first non-Persian fragment in block structure
                if ($p[0] != 0) {
                    $block[] = substr($str, 0, $p[0]);
                }

                // get the last Persian fragment identifier
                $max = count($p);

                // if the bidi logic is rtl
                if ($rtl == true) {
                    // check the start for each Persian fragment
                    for ($i = 0; $i < $max; $i += 2) {
                        // alter start position to include the prev. close bracket if exist
                        $p[$i] = strlen(preg_replace('/\)\s*$/', '', substr($str, 0, $p[$i])));
                    }
                }

                // for each Persian fragment
                for ($i = 0; $i < $max; $i += 2) {
                    // do glyphs pre-processing and save the result in the block structure
                    $block[] = $this->glyphsPreConvert(substr($str, $p[$i], $p[$i + 1] - $p[$i]));

                    // if we still have another Persian fragment
                    if ($i + 2 < $max) {
                        // get the in-between non-Persian fragment as is and save it in the block structure
                        $block[] = substr($str, $p[$i + 1], $p[$i + 2] - $p[$i + 1]);
                    } elseif ($p[$i + 1] != strlen($str)) {
                        // else, the whole fragment starts after the last Persian fragment
                        // until the end of the string will be save as is (non-Persian) in the block structure
                        $block[] = substr($str, $p[$i + 1], strlen($str) - $p[$i + 1]);
                    }
                }

                // if the logic is rtl, then reverse the blocks order before concatenate
                if ($rtl == true) {
                    $block = array_reverse($block);
                }

                // concatenate the whole string blocks
                $str = implode('', $block);
            }

            // add the processed string to the output lines array
            $outLines[] = $str;
        }

        // concatenate the whole text lines using \n
        $output = implode("\n", $outLines);

        // convert to Hindu numerals if requested
        if ($hindo == true) {
            $output = strtr($output, array_combine($this->numeralPersian, $this->numeralHindu));
        }

        return $output;
    }

    /**
     * Identify Persian text in a given UTF-8 multi language string
     *
     * @param  string  $str  UTF-8 multi language string
     * @param  bool  $html  If True, then ignore the HTML tags (default is TRUE)
     * @return array<int> Offset of the beginning and end of each Arabic segment in
     *                    sequence in the given UTF-8 multi language string
     */
    private function identify($str, $html = true): array
    {
        $minUtfDecCode = 1568;
        $maxUtfDecCode = 64509;

        $prob = false;
        $flag = false;
        $htmlFlag = false;
        $ref = [];
        $max = strlen($str);

        $ascii = unpack('C*', $str);

        $i = -1;
        while (++$i < $max) {
            $cDec = $ascii[$i + 1];

            if ($html == true) {
                if ($cDec == 60 && $ascii[$i + 2] != 32) {
                    $htmlFlag = true;
                } elseif ($htmlFlag == true && $cDec == 62) {
                    $htmlFlag = false;
                } elseif ($htmlFlag == true) {
                    continue;
                }
            }

            // ignore ! " # $ % & ' ( ) * + , - . / 0 1 2 3 4 5 6 7 8 9 :
            // If it come in the Persian context
            if ($cDec >= 33 && $cDec <= 58) {
                continue;
            }

            if (! $prob && ($cDec == 216 || $cDec == 217 || $cDec == 218)) {
                $prob = true;

                continue;
            }

            if ($i > 0) {
                $pDec = $ascii[$i];
            } else {
                $pDec = null;
            }

            if ($prob) {
                $utfDecCode = ($pDec << 8) + $cDec;

                if ($utfDecCode >= $minUtfDecCode && $utfDecCode <= $maxUtfDecCode) {
                    if (! $flag) {
                        $flag = true;
                        // include the previous open bracket ( if it is exists
                        $sp = strlen(rtrim(substr($str, 0, $i - 1))) - 1;
                        if ($str[$sp] == '(') {
                            $ref[] = $sp;
                        } else {
                            $ref[] = $i - 1;
                        }
                    }
                } else {
                    if ($flag) {
                        $flag = false;
                        $ref[] = $i - 1;
                    }
                }

                $prob = false;

                continue;
            }

            if ($flag && ! preg_match("/^\s$/", $str[$i])) {
                $flag = false;
                // tag out the trailer spaces
                $sp = $i - strlen(rtrim(substr($str, 0, $i)));
                $ref[] = $i - $sp;
            }
        }

        if ($flag) {
            $ref[] = $i;
        }

        return $ref;
    }

    /**
     * Convert Persian string into glyph joining in UTF-8 hexadecimals stream
     *
     * @param  string  $str  Persian string in UTF-8 charset
     * @return string Persian glyph joining in UTF-8 hexadecimals stream
     */
    private function glyphsPreConvert($str): string
    {
        $glyphs = File::getRequire(module_path('documents', 'config/glyphs.php'));

        $glyphsVowel = 'ًٌٍَُِّْ';

        $crntChar = null;
        $prevChar = null;
        $nextChar = null;
        $output = '';
        $number = '';
        $chars = [];

        $open_range = ')]>}';
        $close_range = '([<{';

        $_temp = mb_strlen($str);

        // split the given string to an array of chars
        for ($i = 0; $i < $_temp; $i++) {
            $chars[] = mb_substr($str, $i, 1);
        }

        $max = count($chars);

        // scan the array of chars backward to flip the sequence of Persian chars in the string
        for ($i = $max - 1; $i >= 0; $i--) {
            $crntChar = $chars[$i];

            // by default assume the letter form is isolated
            $form = 0;

            // set the prevChar by ignore tashkeel (max of two harakat), let it be space if we process the last char
            if ($i > 0) {
                $prevChar = $chars[$i - 1];
                if (mb_strpos($glyphsVowel, $prevChar) !== false && $i > 1) {
                    $prevChar = $chars[$i - 2];

                    if (mb_strpos($glyphsVowel, $prevChar) !== false && $i > 2) {
                        $prevChar = $chars[$i - 3];
                    }
                }
            } else {
                $prevChar = ' ';
            }

            // if it is a digit, then keep adding it to the number in the correct order from left to right
            // once finish, push it to the output array as a whole number then reset the number value to empty
            if (is_numeric($crntChar)) {
                $number = $crntChar.$number;

                continue;
            } elseif (strlen($number) > 0) {
                $output .= $number;
                $number = '';
            }

            // handle the case of open and close brackets (flip them)
            if (mb_strpos($open_range.$close_range, $crntChar) !== false) {
                $output .= ($close_range.$open_range)[mb_strpos($open_range.$close_range, $crntChar)];

                continue;
            }

            // if it is an English char, then show it as it is
            if (ord($crntChar) < 128) {
                $output .= $crntChar;
                $nextChar = $crntChar;

                continue;
            }

            // if the current char is LAM followed by ALEF, use ALEF-LAM character, then step to the next char
            if (
                $crntChar == 'ل' && isset($nextChar)
                && (mb_strpos('آأإا', $nextChar) !== false)
            ) {
                $output = substr($output, 0, strlen($output) - 8);
                if (isset($glyphs[$prevChar]['prevLink']) && $glyphs[$prevChar]['prevLink'] == true) {
                    $output .= '&#x'.$glyphs[$crntChar.$nextChar][1].';';
                } else {
                    $output .= '&#x'.$glyphs[$crntChar.$nextChar][0].';';
                }
                if ($prevChar == 'ل') {
                    $tmp_form = (isset($glyphs[$chars[$i - 2]]['prevLink']) &&
                        $glyphs[$chars[$i - 2]]['prevLink'] == true) ? 3 : 2;
                    $output .= '&#x'.$glyphs[$prevChar][$tmp_form].';';
                    $i--;
                }

                continue;
            }

            // handle the case of HARAKAT
            if (mb_strpos($glyphsVowel, $crntChar) !== false) {
                if ($crntChar == 'ّ') {
                    if (isset($chars[$i - 1]) && mb_strpos($glyphsVowel, $chars[$i - 1]) !== false) {
                        // check if the SHADDA & HARAKA in the middle of connected letters (form 3)
                        if (
                            ($prevChar && $glyphs[$prevChar]['prevLink'] == true) &&
                            ($nextChar && $glyphs[$nextChar]['nextLink'] == true)
                        ) {
                            $form = 3;
                        }

                        // handle the case of HARAKAT after SHADDA
                        $output .= match ($chars[$i - 1]) {
                            'ً' => '&#x0651;&#x064B;',
                            'ٌ' => '&#xFC5E;',
                            'ٍ' => '&#xFC5F;',
                            'َ' => ($form == 3) ? '&#xFCF2;' : '&#xFC60;',
                            'ُ' => ($form == 3) ? '&#xFCF3;' : '&#xFC61;',
                            'ِ' => ($form == 3) ? '&#xFCF4;' : '&#xFC62;',
                            default => '',
                        };
                    } else {
                        $output .= '&#x0651;';
                    }
                    // else show HARAKAT if it is not combined with SHADDA (which processed above)
                } elseif (! isset($chars[$i + 1]) || $chars[$i + 1] != 'ّ') {
                    switch ($crntChar) {
                        case 'ً':
                            $output .= '&#x064B;';
                            break;
                        case 'ٌ':
                            $output .= '&#x064C;';
                            break;
                        case 'ٍ':
                            $output .= '&#x064D;';
                            break;
                        case 'َ':
                            $output .= '&#x064E;';
                            break;
                        case 'ُ':
                            $output .= '&#x064F;';
                            break;
                        case 'ِ':
                            $output .= '&#x0650;';
                            break;
                        case 'ْ':
                            $output .= '&#x0652;';
                            break;
                    }
                }

                continue;
            }

            // check if it should connect to the prev char, then adjust the form value accordingly
            if ($prevChar && isset($glyphs[$prevChar]) && $glyphs[$prevChar]['prevLink'] == true) {
                $form++;
            }

            // check if it should connect to the next char, the adjust the form value accordingly
            if ($nextChar && isset($glyphs[$nextChar]) && $glyphs[$nextChar]['nextLink'] == true) {
                $form += 2;
            }

            if (isset($glyphs[$crntChar][$form])) {
                // add the current char UTF-8 code to the output string
                $output .= '&#x'.$glyphs[$crntChar][$form].';';
            }

            // next char will be the current one before loop (we are going backword to manage right-to-left presenting)
            $nextChar = $crntChar;
        }

        // from Persian Presentation Forms-B, Range: FE70-FEFF,
        // file "UFE70.pdf" (in reversed order)
        // into Persian Presentation Forms-A, Range: FB50-FDFF, file "UFB50.pdf"
        // Example: $output = strtr($output, ['&#xFEA0;&#xFEDF;' => '&#xFCC9;']);
        // Lam Jeem
        $output = $this->glyphsDecodeEntities($output, $exclude = ['&']);

        return $output;
    }

    /**
     * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes.
     * Double-escaped entities will only be decoded once
     * ("&amp;lt;" becomes "&lt;", not "<").
     *
     * @param  string  $text  The text to decode entities in.
     * @param  array<string>  $exclude  An array of characters which should not be decoded.
     *                                  For example, ['<', '&', '"']. This affects
     *                                  both named and numerical entities.
     * @return string
     */
    private function glyphsDecodeEntities($text, $exclude = []): array|string|null
    {
        // Get all named HTML entities.
        $table = array_flip(get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8'));

        // Add apostrophe (XML)
        $table['&apos;'] = "'";

        $newtable = array_diff($table, $exclude);

        // Use a regexp to select all entities in one pass, to avoid decoding double-escaped entities twice.
        $text = preg_replace_callback('/&(#x?)?([A-Fa-f0-9]+);/u', function ($matches) use ($newtable, $exclude) {
            return $this->glyphsDecodeEntities2($matches[1], $matches[2], $matches[0], $newtable, $exclude);
        }, $text);

        return $text;
    }

    /**
     * Helper function for decodeEntities
     *
     * @param  string  $prefix  Prefix
     * @param  string  $codepoint  Codepoint
     * @param  string  $original  Original
     * @param  array<string>  $table  Store named entities in a table
     * @param  array<string>  $exclude  An array of characters which should not be decoded
     */
    private function glyphsDecodeEntities2($prefix, $codepoint, $original, &$table, &$exclude): string
    {
        // Named entity
        if (! $prefix) {
            return isset($table[$original]) ? $table[$original] : $original;
        }

        // Hexadecimal numerical entity
        if ($prefix == '#x') {
            $codepoint = base_convert($codepoint, 16, 10);
        }

        $str = '';

        // Encode codepoint as UTF-8 bytes
        if ($codepoint < 0x80) {
            $str = chr((int) $codepoint);
        } elseif ($codepoint < 0x800) {
            $str = chr(0xC0 | ((int) $codepoint >> 6)).chr(0x80 | ((int) $codepoint & 0x3F));
        } elseif ($codepoint < 0x10000) {
            $str = chr(0xE0 | ((int) $codepoint >> 12)).chr(0x80 | (((int) $codepoint >> 6) & 0x3F)).
                chr(0x80 | ((int) $codepoint & 0x3F));
        } elseif ($codepoint < 0x200000) {
            $str = chr(0xF0 | ((int) $codepoint >> 18)).chr(0x80 | (((int) $codepoint >> 12) & 0x3F)).
                chr(0x80 | (((int) $codepoint >> 6) & 0x3F)).chr(0x80 | ((int) $codepoint & 0x3F));
        }

        // Check for excluded characters
        return in_array($str, $exclude, true) ? $original : $str;
    }
}
