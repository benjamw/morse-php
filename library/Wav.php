<?php
namespace Morse;

use Exception;

/**
 * Based on code from the "Morse Code Generation from Text" article on CodeProject -
 * http://www.codeproject.com/Articles/85339/Morse-Code-Generation-from-Text
 *
 * @author Walt Fair Jr
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @license http://www.codeproject.com/info/cpol10.aspx The Code Project Open License (CPOL) 1.02
 * @link https://github.com/benjamw/morse-php
 */
class Wav {
    const DIT = '.';
    const DAH = '-';
    const SPC = '/';

    protected $sampleRate = 11050;
    protected $tau = 6.283185307;
    protected $cwSpeed = 30;
    protected $frequency = 700;
    protected $data = null;
    protected $timingCodes;

    private $phase  = 0;
    private $dPhase = 0;

    private $bytes = [];

	/**
	 * @var float
	 */
	private $toneTime;

	/**
	 * @var float
	 */
	private $sampleDt;

	public function __construct($table = null) {
        $this->timingCodes = $table ?: new Table();
    }

	/**
	 * @throws Exception
	 */
	public function setCwSpeed($speed) {
        if (!is_numeric($speed)) {
            throw new Exception('$Speed must be numeric');
        }

        $this->cwSpeed = $speed;
        return $this;
    }

	/**
	 * @throws Exception
	 */
	public function setSampleRate($rate) {
        if (!is_numeric($rate)) {
            throw new Exception('Sample rate must be numeric');
        }

        $this->sampleRate = $rate;
        return $this;
    }

	/**
	 * @throws Exception
	 */
	public function setFrequency($frequency) {
        if (!is_numeric($frequency)) {
            throw new Exception('Frequency must be numeric');
        }

        $this->frequency = $frequency;
        return $this;
    }

    public function generate($text) {
        $this->reset();

        $this->toneTime = 1.0 / $this->frequency; // sec per wavelength
        if ($this->cwSpeed < 15) {
            // use Farnsworth spacing
            $ditTime = 1.145 / 15.0;
            $charsPc = 122.5 / $this->cwSpeed - 31.0 / 6.0;
        } else {
            $ditTime = 1.145 / $this->cwSpeed;
            $charsPc = 3;
        }

        $wordsPc = floor(2 * $charsPc + 0.5);
        $this->sampleDt = 1.0 / $this->sampleRate;
        $this->phase = 0;
        $this->dPhase = 0;

        $this->oscReset();

        $dit = 0;
        while ($dit < $ditTime) {
            $x = $this->osc();
            // The dit and dah sound both rise during the first half dit-time
            if ($dit < (0.5 * $ditTime)) {
                $x = $x * sin((pi() / 2.0) * $dit / (0.5 * $ditTime));
                $this->bytes[self::DIT] .= chr(floor(120 * $x + 128));
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            } else if ($dit > (0.5 * $ditTime)) {
                // During the second half dit-time, the dit sound decays
                // but the dah sound stays constant
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
                $x = $x * sin((pi() / 2.0) * ($ditTime - $dit) / (0.5 * $ditTime));
                $this->bytes[self::DIT] .= chr(floor(120 * $x + 128));
            } else {
                $this->bytes[self::DIT] .= chr(floor(120 * $x + 128));
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            }
            $this->bytes[self::SPC] .= chr(128);
            $dit += $this->sampleDt;
        }

        // At this point the dit and space sound have been generated
        // During the next dit-time, the dah sound amplitude is constant
        $dit = 0;
        while ($dit < $ditTime) {
            $x = $this->osc();
            $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            $dit += $this->sampleDt;
        }

        // During the 3rd dit-time, the dah-sound has a constant amplitude
        // then decays during that last half dit-time
        $dit = 0;
        while ($dit < $ditTime) {
            $x = $this->osc();
            if ($dit > (0.5 * $ditTime)) {
                $x = $x * sin((pi() / 2.0) * ($ditTime - $dit) / (0.5 * $ditTime));
            }
            $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            $dit += $this->sampleDt;
        }

        // Convert the text to morse code string
        $text = strtoupper($text);
        $sound = '';
        for ($i = 0; $i < strlen($text); $i++) {
            if ($text[$i] == ' ') {
				$sound .= str_repeat($this->bytes[ self::SPC ], $wordsPc);
            } else if (isset($this->timingCodes[$text[$i]])) {
                $xChar = $this->timingCodes[$text[$i]];

                for ($k = 0; $k < strlen($xChar); $k++) {
                    if ($xChar[$k] == '0') {
                        $sound .= $this->bytes[self::DIT];
                    } else {
                        $sound .= $this->bytes[self::DAH];
                    }
                    $sound .= $this->bytes[self::SPC];
                }

                for ($j = 1; $j < $charsPc; $j++) {
                    $sound .= $this->bytes[self::SPC];
                }
            }
        }

        $n = strlen($sound);

        // Write out the WAVE file
        $x = $n + 32;
        $soundSize = '';

        for ($i = 0; $i < 4; $i++) {
            $soundSize .= chr($x % 256);
            $x = floor($x / 256);
        }

        $riffHeader = 'RIFF' . $soundSize . 'WAVE';
        $x = $this->sampleRate;
        $sampleRateString = '';

        for ($i = 0; $i < 4; $i++) {
            $sampleRateString .= chr($x % 256);
            $x = floor($x / 256);
        }

        $headerString = 'fmt ' . chr(16) . chr(0) . chr(0) . chr(0) . chr(1) . chr(0) . chr(1) . chr(0);
        $headerString .= $sampleRateString . $sampleRateString . chr(1) . chr(0) . chr(8) . chr(0);
        $x = $n;
        $sampleString = '';

        for ($i = 0; $i < 4; $i++) {
            $sampleString .= chr($x % 256);
            $x = floor($x / 256);
        }

        $sound = 'data' . $sampleString . $sound;
        $wav = $riffHeader . $headerString . $sound;

        return $wav;
    }

    private function osc() {
        $this->phase += $this->dPhase;
        if ($this->phase >= $this->tau) {
            $this->phase -= $this->tau;
        }
        return sin($this->phase);
    }

    private function oscReset() {
        $this->phase = 0;
        $this->dPhase = $this->tau * $this->sampleDt / $this->toneTime;
    }

    private function reset() {
        $this->bytes = [
            self::DIT => '',
            self::DAH => '',
            self::SPC => ''
        ];

        $this->data = null;
    }
}
