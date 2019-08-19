<?php
namespace ConductorTests\Http;

use Conductor\Exception\InvalidArgumentException;
use Conductor\Http\Message;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class MessageTest extends TestCase
{
    private $message;

    public function setUp()
    {
        $this->message = new Message();
    }

    public function testInvalidArgumentExceptionIsThrownWhenAnInvalidProtocolVersionIsProvided()
    {
        $this->expectException(InvalidArgumentException::class);

        new Message('Invalid.Version');
    }

    public function testHasHeaderReturnsTrueIfTheMessageHasASpecificHeader()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertTrue($message->hasHeader('Foo'));
    }

    public function testHasHeaderReturnsFalseIfTheMessageDoesNotHaveASpecificHeader()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertFalse($message->hasHeader('Bar'));
    }

    public function testGetHeaderReturnsTheHeaderValues()
    {
        $message = new Message('1.1', ['Content-Length' => 123]);

        $this->assertEquals([123], $message->getHeader('Content-Length'));
    }

    public function testGetHeaderReturnsTheHeaderValuesWhenThereAreMultipleValues()
    {
        $message = new Message('1.1', ['Content-Encoding' => 'gzip, deflate']);

        $this->assertEquals(['gzip', 'deflate'], $message->getHeader('Content-Encoding'));
    }

    public function testGetHeaderReturnsTheHeaderValuesWhenTheCaseMisMatches()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertEquals(['Bar'], $message->getHeader('foo'));
    }

    public function testGetHeaderReturnsAnEmptyArrayWhenTheHeaderIsnPresent()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertEquals([], $message->getHeader('bar'));
    }

    public function testGetHeaderLineReturnsEmptyStringIfTheHeaderIsNotPresent()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertEmpty($message->getHeaderLine('bar'));
    }

    public function testGetHeaderLineReturnsACommaSeparatedListOfHeaderValues()
    {
        $message = new Message('1.1', ['Content-Encoding' => 'gzip, deflate']);

        $this->assertEquals('gzip,deflate', $message->getHeaderLine('Content-encoding'));
    }

    public function testWithHeaderVersionReturnsAnInstanceWithTheNewHeaderVersion()
    {
        $message = new Message('1.1');

        $this->assertEquals('2.0', $message->withProtocolVersion('2.0')->getProtocolVersion());
    }

    public function testWithHeaderReturnsANewInstanceWithTheNewHeader()
    {
        $message = new Message();

        $this->assertEquals(['Bar'], $message->withHeader('Foo', 'Bar')->getHeader('foo'));
    }

    public function testWithHeaderReturnsANewInstance()
    {
        $message = new Message();

        $this->assertNotEquals($message, $message->withHeader('Foo', 'Bar'));
    }

    public function testWithHeaderOverwritesAnExistingHeader()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertEquals(['Test'], $message->withHeader('Foo', 'Test')->getHeader('Foo'));
    }

    public function testWithHeaderOverwritesAnExistingHeaderInACaseInsensitiveManner()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertEquals(['Test'], $message->withHeader('foo', 'Test')->getHeader('Foo'));
    }

    public function testWithAddedHeaderReturnsANewInstance()
    {
        $message = new Message();

        $this->assertNotEquals($message, $message->withAddedHeader('Foo', 'Bar'));
    }

    public function testWithAddedHeaderReturnsANewInstanceWithTheAddedHeader()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertEquals(
            ['Test'],
            $message->withAddedHeader('Test', 'Test')->getHeader('Test')
        );
    }

    public function testWithoutHeaderReturnsANewInstance()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertNotEquals($message, $message->withoutHeader('Foo'));
    }

    public function testWithoutHeaderReturnsAnInstanceWithTheSpecifiedHeaderRemoved()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertFalse($message->withoutHeader('Foo')->hasHeader('Foo'));
    }

    public function testWithoutHeaderReturnsAnInstanceWithAllOtherHeadersIntact()
    {
        $message = new Message('1.1', ['Foo' => 'Bar']);

        $this->assertTrue($message->withoutHeader('Bar')->hasHeader('Foo'));
    }

    public function testWithBodyReturnsANewInstance()
    {
        $message = new Message();

        $this->assertNotEquals($message, $message->withBody($this->createMock(StreamInterface::class)));
    }

    public function testWithBodyReturnsANewInstanceWithTheSpecifiedBody()
    {
        $message = new Message();

        $body = $this->createMock(StreamInterface::class);

        $this->assertEquals($body, $message->withBody($body)->getBody());
    }

    public function testGetHeaderReturnsASingleStringForUserAgent()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) ' .
            'Chrome/76.0.3809.100 Safari/537.36';

        $message = new Message('1.1', [
            'User-Agent' =>
            $userAgent
        ]);

        $this->assertEquals([$userAgent], $message->getHeader('User-Agent'));
    }

    public function testGetHeaderReturnsASingleStringForLowerCaseUserAgentWhenProvidedInUpperCase()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) ' .
            'Chrome/76.0.3809.100 Safari/537.36';

        $message = new Message('1.1', [
            'user-agent' =>
                $userAgent
        ]);

        $this->assertEquals([$userAgent], $message->getHeader('User-Agent'));
    }

}
