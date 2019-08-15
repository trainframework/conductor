<?php
namespace Conductor\Http;

use Conductor\Exception\InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    const NOT_COMMA_SEPARATED = ['User-Agent'];

    protected $protocolVersion;
    protected $headers;
    protected $body;

    public function __construct(string $protocolVersion = '1.1', $headerLines = [], StreamInterface $body = null)
    {
        if (!$this->isValidProtocolVersion($protocolVersion)) {
            throw new InvalidArgumentException('Invalid protocol version');
        }

        $this->protocolVersion = $protocolVersion;
        $this->body = $body;

        if ($headerLines instanceof HeaderCollection) {
            $this->headers = $headerLines;
        } else {
            $this->headers = new HeaderCollection($this->parseHeaderLines($headerLines));
        }
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version) : MessageInterface
    {
        return new static(
            $version,
            $this->headers,
            $this->body
        );
    }

    public function getHeaders()
    {
        return $this->headers->toArray();
    }

    public function hasHeader($name)
    {
        return $this->headers->findByName($name)->count() > 0;
    }

    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) return [];

        $headers = $this->headers->findByName($name);

        return $headers->first();
    }

    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) return '';

        $header = $this->headers->findByName($name);

        return implode(',', $header->first());
    }

    public function withHeader($name, $value)
    {
        $headers = clone $this->headers;
        $headers->offsetSet($name, $value);

        return new static(
            $this->protocolVersion,
            $headers,
            $this->body
        );
    }

    public function withAddedHeader($name, $value)
    {
        $headers = clone $this->headers;

        if (!$this->hasHeader($name)) {
            $headers->offsetSet($name, $value);
        } else {
            $headerValues = $headers->offsetGet($name);
            $headerValues[] = $value;
            $headers->offsetSet($name, $headerValues);
        }

        return new static(
            $this->protocolVersion,
            $headers,
            $this->body
        );
    }

    public function withoutHeader($name)
    {
        $headers = clone $this->headers;
        $headers->remove($name);

        return new static(
            $this->protocolVersion,
            $headers,
            $this->body
        );
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        return new static(
            $this->protocolVersion,
            $this->headers,
            $body
        );
    }

    protected function isValidProtocolVersion($protocolVersion) : bool
    {
        return preg_match('/([0-9])\.([0-9])/', $protocolVersion);
    }

    public function parseHeaderLines(array $headerLines)
    {
        $headers = [];
        foreach ($headerLines as $headerName => $headerLine) {
            $headerParts = $this->getHeaderParts($headerName, $headerLine);
            foreach ($headerParts as $headerPart) {
                $headers[$headerName][] = trim($headerPart);
            }
        }
        return $headers;
    }

    public function getHeaderParts($headerName, $headerLine)
    {
        if (!in_array($headerName, self::NOT_COMMA_SEPARATED)) {
            return explode(',', $headerLine);
        }

        return [$headerLine];
    }
}
