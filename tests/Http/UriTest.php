<?php
namespace ConductorTests\Http;

use Conductor\Http\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    private Uri $uri;

    public function setUp()
    {
        $this->uri = new Uri();
    }

    public function test()
    {

    }

    public function testGetAuthorityDoesNotIncludePortIfSchemeIsStandard()
    {
        $scheme = 'https';
        $port = 443;
        $host = 'example.com';

        $authority = $this->uri->withScheme($scheme)->withPort($port)->withHost($host)->getAuthority();

        $this->assertStringNotContainsString(':443', $authority);
    }

    public function testSchemeAndAuthorityAreReturned()
    {
        $scheme = 'http';
        $host = 'example.com';
        $expected = 'http://example.com';

        $uri = $this->uri->withScheme($scheme)->withHost($host)->__toString();

        $this->assertEquals($expected, $uri);
    }

    public function testGetAuthorityReturnsUserDataWithHostIfBothSet()
    {
        $username = 'foo';
        $password = 'bar';
        $url = 'example.com';
        $result = $this->uri->withHost($url)->withUserInfo($username, $password)->getAuthority();

        $this->assertEquals($username . ':' . $password . '@' . $url . ':80', $result);
    }

    public function testGetAuthorityReturnsHostWithoutUserData()
    {
        $url = 'example.com';
        $result = $this->uri->withHost($url)->getAuthority();

        $this->assertEquals($url . ':80', $result);
    }

    public function testGetAuthorityReturnsUserDataIfOnlySet()
    {
        $username = 'foo';
        $password = 'bar';
        $result = $this->uri->withUserInfo($username, $password)->getAuthority();

        $expected = $username . ':' . $password;

        $this->assertEquals($expected, $result);
    }

    public function testSchemeIsReturnedIfOnlySchemeIsSet()
    {
        $scheme = 'http';
        $uri = $this->uri->withScheme($scheme);
        $this->assertEquals($scheme . ':', $uri->__toString());
    }

    public function testGetAuthorityReturnsCorrectFormat()
    {
        $username = 'foo';
        $password = 'bar';
        $host = 'example.com';
        $port = 443;

        $uri = $this->uri->withUserInfo($username, $password)->withHost($host)->withPort($port);

        $this->assertEquals($username . ':' . $password . '@' . $host . ':' . $port, $uri->getAuthority());
    }

    public function testUsernameAndPasswordAreConcatenated()
    {
        $username = 'foo';
        $password = 'bar';
        $uri = $this->uri->withUserInfo($username, $password);

        $this->assertEquals($username . ':' . $password, $uri->getUserInfo());
    }
}
