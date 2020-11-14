<?php
namespace Conductor\Http;

use Conductor\Exception\InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    /**
     * Header parts that are not comma separated
     */
    const NOT_COMMA_SEPARATED = ['User-Agent'];

    /**
     * @var string HTTP Protocol version in use
     */
    protected string $protocolVersion;
    /**
     * @var HeaderCollection Headers in use
     */
    protected HeaderCollection $headers;
    /**
     * @var StreamInterface|null Body of the request
     */
    protected ?StreamInterface $body;

    /**
     * @param string $protocolVersion
     * @param array $headerLines
     * @param StreamInterface|null $body
     * @throws InvalidArgumentException
     */
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

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version) : MessageInterface
    {
        return new static(
            $version,
            $this->headers,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers->toArray();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
        return $this->headers->findByName($name)->count() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) return [];

        $headers = $this->headers->findByName($name);

        return $headers->first();
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) return '';

        $header = $this->headers->findByName($name);

        return implode(',', $header->first());
    }

    public function withHeader($name, $value)
    {
        $headers = clone $this->headers;
        $parts = $this->getHeaderparts($name, $value);
        $headers->offsetSet($name, $parts);

        return new static(
            $this->protocolVersion,
            $headers,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $headers = clone $this->headers;

        if (!$this->hasHeader($name)) {
            $parts = $this->getHeaderParts($name, $value);
            $headers->offsetSet($name, $parts);
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        return new static(
            $this->protocolVersion,
            $this->headers,
            $body
        );
    }

    /**
     * @param $protocolVersion
     * @return bool
     */
    protected function isValidProtocolVersion($protocolVersion) : bool
    {
        return preg_match('/([0-9])\.([0-9])/', $protocolVersion);
    }

    /**
     * @param array $headerLines
     * @return array
     */
    protected function parseHeaderLines(array $headerLines)
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

    /**
     * @param $headerName
     * @param $headerLine
     * @return array|false|string[]
     */
    protected function getHeaderParts($headerName, $headerLine)
    {
        $isHeaderInCommaExclusionList = preg_grep('/' . preg_quote($headerName) . '/i', self::NOT_COMMA_SEPARATED);
        if (!$isHeaderInCommaExclusionList) {
            return explode(',', $headerLine);
        }

        return [$headerLine];
    }
}
