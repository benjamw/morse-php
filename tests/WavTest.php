<?php
namespace Morse;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class WavTest extends \PHPUnit_Framework_TestCase {
    protected function setUp() {
        if (!extension_loaded('fileinfo')) {
            $this->markTestSkipped('The finfo extension is not available');
        }
    }

    public function testCanGenerateValidWav() {
        $wav = new Wav();
        $output = $wav->generate('Espen');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($output);

        $this->assertSame('audio/x-wav', $mime);
    }

	/**
	 * @throws \Exception
	 */
	public function testCanSetCwSpeed() {
        $wav = (new Wav())->setCwSpeed(10);
        $output = $wav->generate('Espen');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($output);

        $this->assertSame('audio/x-wav', $mime);
    }

	/**
	 * @throws \Exception
	 */
	public function testThrowsIfInvalidCwSpeedSet() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Speed must be numeric');
        (new Wav())->setCwSpeed('foo');
    }

	/**
	 * @throws \Exception
	 */
	public function testCanSetSampleRate() {
        $wav = (new Wav())->setSampleRate(8000);
        $output = $wav->generate('Espen');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($output);

        $this->assertSame('audio/x-wav', $mime);
    }

	/**
	 * @throws \Exception
	 */
	public function testThrowsIfInvalidSampleRateSet() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sample rate must be numeric');
        (new Wav())->setSampleRate('foo');
    }

	/**
	 * @throws \Exception
	 */
	public function testCanSetFrequency() {
        $wav = (new Wav())->setFrequency(8000);
        $output = $wav->generate('Espen');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($output);

        $this->assertSame('audio/x-wav', $mime);
    }

	/**
	 * @throws \Exception
	 */
	public function testThrowsIfInvalidFrequencySet() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Frequency must be numeric');
        (new Wav())->setFrequency('foo');
    }

	/**
	 * @throws \Exception
	 */
	public function testCanGenerateLongerValidWav() {
        $wav = new Wav();
        $output = $wav->setFrequency(100)->generate($this->getLoremIpsum());

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($output);

        $this->assertSame('audio/x-wav', $mime);
    }

    protected function getLoremIpsum() {
        return (
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. ' .
            'Duis sed dignissim arcu. Etiam non euismod nulla. ' .
            'Cras non sagittis velit. Donec et imperdiet ipsum. ' .
            'Fusce vel enim ut neque pellentesque congue. ' .
            'Nunc posuere vitae justo eu dignissim. ' .
            'Sed eget nunc sed massa auctor posuere. ' .
            'Etiam condimentum ullamcorper tellus, et tempor nisl aliquet a.' .
            'Nulla finibus ut nulla eu rhoncus.'
        );
    }
}
