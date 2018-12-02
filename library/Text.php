<?php
namespace Morse;

/**
 * Morse code table
 *
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) Espen Hovlandsdal
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/rexxars/morse-php
 */
class Text {
    /**
     * Array of morse code mappings
     *
     * @var array
     */
    protected $table;

    /**
     * Character that will be used in place when encountering invalid characters
     *
     * @var string
     */
    protected $invalidCharacterReplacement = '#';

    /**
     * Separator to put in between words
     *
     * @var string
     */
    protected $wordSeparator = '  ';

    protected $upperCaseModificator = '+';

    private $is_case_sense = false;

    private $upperMod = false;

    /**
     * @param array $table Optional morse code table to use
     */
    public function __construct($table = null, $is_case_sense = false) {
        $this->is_case_sense = $is_case_sense;
        $this->table = $table ? $table : new Table();
    }

    /**
     * Set the replacement that will be used when encountering invalid characters
     *
     * @param string $replacement
     * @return Text
     */
    public function setInvalidCharacterReplacement($replacement) {
        $this->invalidCharacterReplacement = $replacement;

        return $this;
    }

    /**
     * Set the character/string to separate words with
     *
     * @param string $separator
     * @return Text
     */
    public function setWordSeparator($separator) {
        $this->wordSeparator = $separator;

        return $this;
    }

    /**
     * Translate the given text to morse code
     *
     * @param string $text
     * @return string
     */
    public function toMorse($text) {
        if (!$this->is_case_sense) {
            $text = strtolower($text);
        }

        $words = preg_split('#\s+#', $text);
        $morse = array_map([$this, 'morseWord'], $words);
        return implode($this->wordSeparator, $morse);
    }

    /**
     * Translate the given morse code to text
     *
     * @param string $morse
     * @return string
     */
    public function fromMorse($morse) {
        $morse = str_replace($this->invalidCharacterReplacement . ' ', '', $morse);
        $words = explode($this->wordSeparator, $morse);
        $morse = array_map([$this, 'translateMorseWord'], $words);
        return implode(' ', $morse);
    }

    /**
     * Translate lowercase with modifers to upper
     *
     * @param array $characters
     * @return array
    */
    private function toUppercase($characters) {
        $cnt = count($characters);
        $result = array();
        $i = 0;
        while($i < $cnt) {
            $char = $characters[$i];
            if ($char == $this->upperCaseModificator) {
                $i++;
                $result[] = mb_strtoupper($characters[$i]);
            } else {
                $result[] = $char;
            }

            $i++;
        }
        return $result;
    }

    /**
     * Translate a "morse word" to text
     *
     * @param string $morse
     * @return string
     */
    private function translateMorseWord($morse) {
        $morseChars = explode(' ', $morse);
        $characters = array_map([$this, 'translateMorseCharacter'], $morseChars);
        if ($this->is_case_sense) {
            $characters = $this->toUppercase($characters);
        }

        return implode('', $characters);
    }

    /**
     * Get the character for a given morse code
     *
     * @param string $morse
     * @return string
     */
    private function translateMorseCharacter($morse) {
        return $this->table->getCharacter($morse);
    }

    /**
     * Return the morse code for this word
     *
     * @param string $word
     * @return string
     */
    private function morseWord($word) {
        $chars = $this->strSplit($word);
        $morse = array_map([$this, 'morseCharacter'], $chars);
        return implode(' ', $morse);
    }

    /**
     * Return the morse code for this character
     *
     * @param string $char
     * @return string
     */
    private function morseCharacter($char) {
        if ($this->is_case_sense && preg_match('/^[A-ZА-ЯЁ]$/', $char)) {
            $char = strtolower($char);
            if (!isset($this->table[$char])) {
                return $this->invalidCharacterReplacement;
            }

            return $this->table->getMorse($this->upperCaseModificator).' '.$this->table->getMorse($char);
        } else {
            if (!isset($this->table[$char])) {
                return $this->invalidCharacterReplacement;
            }

            return $this->table->getMorse($char);
        }
    }

    /**
     * Split a string into individual characters
     *
     * @param string $str
     * @param integer $l
     * @return array
     */
    private function strSplit($str, $l = 0) {
        return preg_split(
            '#(.{' . $l . '})#us',
            $str,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
    }
}
