<?php
namespace Conductor\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    const STANDARD_PORTS = [
        'http' => 80,
        'https' => 443
    ];

    /**
     * @var string $scheme URI Scheme
     */
    protected string $scheme;

    /**
     * @var string $host
     */
    protected string $host;

    /**
     * @var int @port
     */
    protected int $port;

    /**
     * @var string $path
     */
    protected string $path;

    /**
     * @var string $query
     */
    protected string $query;

    /**
     * @var string $fragment
     */
    protected string $fragment;

    /**
     * @var string $userInfo
     */
    protected string $userInfo;

    public function __construct(
        string $scheme = '',
        string $host = '',
        int $port = 80,
        string $path = '',
        string $query = '',
        string $fragment = '',
        string $userInfo = ''
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->userInfo = $userInfo;
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        $authority = '';

        $userInfo = $this->getUserInfo();
        if ($userInfo) {
            $authority .= $userInfo;
        }

        $host = $this->getHost();
        if ($host) {
            if ($userInfo) {
                $authority .= '@';
            }

            $authority .= $host;
            $port = $this->getPort();
            if ($port && !$this->isUsingStandardPort()) {
                $authority .= ':' . $port;
            }
        }

        return $authority;
    }

    /**
     * Determines if a standard port is being used for the current scheme
     *
     * @return bool
     */
    public function isUsingStandardPort(): bool
    {
        if (!isset(self::STANDARD_PORTS[$this->getScheme()])) {
            return false;
        }

        return (self::STANDARD_PORTS[$this->getScheme()] === $this->port);
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme): self
    {
        return new static(
            $scheme,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment,
            $this->userInfo
        );
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = $user;
        if ($password) {
            $userInfo .= ':' . $password;
        }

        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment,
            $userInfo
        );
    }

    /**
     * @inheritDoc
     */
    public function withHost($host): self
    {
        return new static(
            $this->scheme,
            $host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment,
            $this->userInfo
        );
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        return new static(
            $this->scheme,
            $this->host,
            $port,
            $this->path,
            $this->query,
            $this->fragment,
            $this->userInfo
        );
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $path,
            $this->query,
            $this->fragment,
            $this->userInfo
        );
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $this->path,
            $query,
            $this->fragment,
            $this->userInfo
        );
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $fragment,
            $this->userInfo
        );
    }

    public function __toString()
    {
        $url = '';

        if ($this->scheme) {
            $url .= $this->getScheme() . ':';
        }

        $authority = $this->getAuthority();
        if ($authority) {
            $url .= '//' . $authority;
        }

        $url .= $this->getPath();

        $query = $this->getQuery();
        if ($query) {
            $url .= '?' . $this->getQuery();
        }

        $fragment = $this->getFragment();
        if ($fragment) {
            $url .= $this->getFragment();
        }

        return $url;
    }
}
