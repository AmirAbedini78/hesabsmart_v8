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

trait ManualRTLDirection
{
    /**
     * Recursively processes HTML tags and applies the fixDirection function to their text content.
     *
     * @param  string  $html  Input HTML string.
     * @return string Processed HTML string.
     */
    protected function manualRTLDirection($html): array|string|null
    {
        // Pattern to match HTML tags and their content.
        $pattern = '/(<(\w+)(?:[^>]*)>)(.*?)(<\/\2>)/s';

        // Replace callback to process the inner content of the tags.
        $processedHtml = preg_replace_callback($pattern, function ($matches) {
            // $matches[1]: Opening tag
            // $matches[2]: Tag name
            // $matches[3]: Inner content
            // $matches[4]: Closing tag

            // If the inner content contains other tags, process recursively.
            if (preg_match('/<\w+[^>]*>/', $matches[3])) {
                $processedContent = $this->manualRTLDirection($matches[3]);
            } else {
                // If only plain text, apply the repl function.
                $processedContent = $this->fixDirection($matches[3]);
            }

            // Return the processed content wrapped in the original tags.
            return $matches[1].$processedContent.$matches[4];
        }, $html);

        return $processedHtml;
    }

    /**
     * Adjusts text direction manually for Right-to-Left (RTL) languages.
     *
     * This function processes a given text by grouping and reordering words
     * based on their script (Arabic/Hebrew or others), ensuring proper display
     * in RTL contexts.
     *
     * @param  string  $text  The input text to be processed.
     * @return string The text adjusted for RTL direction.
     */
    private function fixDirection($text): string
    {
        // Split the text into words and reverse the order.
        $words = array_reverse(explode(' ', $text));

        // Initialize buffers for English and Arabic/Hebrew words.
        $enBuffer = '';
        $faBuffer = '';
        $newTextWords = [];

        // Iterate through each word.
        foreach ($words as $word) {
            // Check if the word contains Arabic or Hebrew characters.
            if (preg_match('/[\p{Arabic}\p{Hebrew}]/u', $word) && ! $this->isArabicDiacriticalMark($word)) {
                // Append the word to the Arabic/Hebrew buffer with a preceding space.
                $faBuffer .= " {$word}";

                // If the English buffer is not empty, add it to the result and reset it.
                if ($enBuffer) {
                    $newTextWords[] = $enBuffer;
                    $enBuffer = '';
                }
            } else {
                // Append the word to the English buffer with a preceding space.
                $enBuffer .= " {$word}";

                // If the Arabic/Hebrew buffer is not empty, add it to the result and reset it.
                if ($faBuffer) {
                    $newTextWords[] = $faBuffer;
                    $faBuffer = '';
                }
            }
        }

        // Add any remaining words in the buffers to the result.
        if ($faBuffer) {
            $newTextWords[] = $faBuffer;
            $faBuffer = '';
        }

        if ($enBuffer) {
            $newTextWords[] = $enBuffer;
            $enBuffer = '';
        }

        // Reverse the words within each buffer segment and clean up extra spaces.
        $newTextWords = array_map(
            fn ($text) => implode(' ', array_reverse(explode(' ', trim($text)))),
            $newTextWords
        );

        // Join all segments into a single string separated by spaces and add a newline at the end.
        return implode(' ', $newTextWords).PHP_EOL;
    }

    /**
     * Check if a character is an Arabic diacritical mark (harakat) using regex
     *
     * @param  string  $char  The character to check
     * @return bool True if the character is an Arabic diacritical mark, false otherwise
     */
    private function isArabicDiacriticalMark($char): bool|int
    {
        // Define the regex pattern for Arabic diacritical marks
        $pattern = '/[\x{064B}-\x{0652}]/u';

        // Check if the character matches the pattern
        return preg_match($pattern, $char);
    }

    public function isYahAtEndAndAttached($word): bool
    {
        if (! mb_check_encoding($word, 'UTF-8')) {
            return false;
        }

        $yah = "\u{06CC}";

        $nonAttachableLetters = ['ز', 'ر', 'د', 'و', 'ذ', 'ء'];

        if (mb_substr($word, -1) === $yah) {
            $previousChar = mb_substr($word, -2, 1);
            if (in_array($previousChar, $nonAttachableLetters, true)) {
                return false;
            }

            return true;
        }

        return false;
    }
}
