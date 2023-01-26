<?php

namespace Src;

class Flat
{
    /**
     * @var string
     */
    protected string $url;

    /**
     * @var ?string
     */
    protected ?string $title;

    /**
     * @var ?string
     */
    protected ?string $price;

    /**
     * @var ?string
     */
    protected ?string $location;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Flat
     */
    public function setUrl(string $url): Flat
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param ?string $title
     *
     * @return Flat
     */
    public function setTitle(?string $title): Flat
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @param ?string $price
     *
     * @return Flat
     */
    public function setPrice(?string $price): Flat
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param ?string $location
     *
     * @return Flat
     */
    public function setLocation(?string $location): Flat
    {
        $this->location = $location;
        return $this;
    }


    /**
     * @return array
     */
    public function returnArray(): array
    {
        return [
            $this->getLocation(),
            $this->getPrice(),
            $this->getTitle(),
            $this->getUrl(),
        ];
    }
}