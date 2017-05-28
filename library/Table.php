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
class Table implements \ArrayAccess {
    /**
     * An array of predefined codes
     *
     * @var array
     */
    private $predefinedCodes;

    /**
     * A reverse copy of the table (morse => character)
     *
     * @var array
     */
    private $reversedTable;

    /**
     * A table of predefined morse code mappings
     *
     * @var array
     */
    private $table = [
        'a' => '01',
        'b' => '1000',
        'c' => '1010',
        'd' => '100',
        'e' => '0',
        'f' => '0010',
        'g' => '110',
        'h' => '0000',
        'i' => '00',
        'j' => '0111',
        'k' => '101',
        'l' => '0100',
        'm' => '11',
        'n' => '10',
        'o' => '111',
        'p' => '0110',
        'q' => '1101',
        'r' => '010',
        's' => '000',
        't' => '1',
        'u' => '001',
        'v' => '0001',
        'w' => '011',
        'x' => '1001',
        'y' => '1011',
        'z' => '1100',

        '0' => '11111',
        '1' => '01111',
        '2' => '00111',
        '3' => '00011',
        '4' => '00001',
        '5' => '00000',
        '6' => '10000',
        '7' => '11000',
        '8' => '11100',
        '9' => '11110',

        '.' => '010101',
        ',' => '110011',
        '?' => '001100',
        "'" => '011110',
        '!' => '101011',
        '/' => '10010',
        '(' => '10110',
        ')' => '101101',
        '&' => '01000',
        ':' => '111000',
        ';' => '101010',
        ' ' => '10001',
        '+' => '01010',
        '-' => '100001',
        '_' => '001101',
        '"' => '010010',
        '$' => '0001001',
        '@' => '011010',
    ];

    private $dash = '-';

    /**
     * Constructs a new instance of the table
     */
    public function __construct($dash_char = '-') {
        $this->dash = $dash_char;
        $this->predefinedCodes = array_keys($this->table);
        $this->reversedTable = array_flip($this->table);
    }

    /**
     * Returns whether the given offset (character) exists
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($this->table[$offset]);
    }

    /**
     * Get the morse code for the given offset (character)
     *
     * @param mixed $offset
     * @return string
     */
    public function offsetGet($offset) {
        return $this->table[$offset];
    }

    /**
     * Add a morse code mapping for the given offset (character)
     *
     * @param mixed $offset
     * @param string $value
     */
    public function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            throw new \Exception('Can\'t override predefined character');
        } else if (!preg_match('#^[01]+$#', $value)) {
            throw new \Exception('Value must be a string of zeroes and ones (0/1)');
        } else if (isset($this->reversedTable[$value])) {
            throw new \Exception('There is already a character with value ' . $value);
        }

        $this->table[$offset] = $value;
        $this->reversedTable[$value] = $offset;
    }

    /**
     * Remove a morse code mapping for the given offset (character)
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        if (in_array($offset, $this->predefinedCodes, true)) {
            throw new \Exception('Can\'t unset a predefined morse code');
        }

        unset($this->table[$offset]);
    }

    /**
     * Get morse code (dit/dah) for a given character
     *
     * @param  string $offset
     * @return string
     */
    public function getMorse($character) {
        return strtr($this->offsetGet($character), '01', '.'.$this->dash);
    }

    /**
     * Get character for given morse code
     *
     * @param  string $morse
     * @return string
     */
    public function getCharacter($morse) {
        $key = strtr($morse, '.'.$this->dash, '01');
        return isset($this->reversedTable[$key]) ? $this->reversedTable[$key] : false;
    }
}
