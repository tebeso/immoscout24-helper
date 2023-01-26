<?php

namespace Src;

use DOMDocument;
use DOMXPath;
use JsonException;

/**
 * Get your favorites from the ajax response on the favorites page of immoscout24.ch and copy the content to favorites.json
 * Then start this script (call index.php) and wait. Afterwards, a CSV file is generated (places.csv).
 * Go to https://www.google.com/maps/d/ -> create new map -> import generated CSV
 */
class Crawler
{
    /**
     * @var string
     */
    protected string $url = 'https://www.immoscout24.ch/en/d/flat-rent-bern/';

    /**
     * @var string
     */
    protected string $inputFilename = 'favorites.json';

    /**
     * @var string
     */
    protected string $outputFilename = 'places.csv';

    /**
     * @var string
     */
    protected string $noLongerAvail = 'Property no longer available';

    /**
     * @var array
     */
    protected array $favorites = [];

    /**
     * @throws JsonException
     */
    public function __construct()
    {
        $file = file_get_contents($this->getInputFilename());
        $json = json_decode($file, false, 512, JSON_THROW_ON_ERROR);

        $this->setFavorites($json->favourites);
    }


    /**
     * @return void
     */
    public function run(): void
    {
        $csv = $this->createCsv();

        foreach ($this->getFavorites() as $favorite) {
            $flat = new Flat();

            $url            = $this->getUrl() . $favorite->propertyId;
            $crawledContent = $this->getContentByUrl($url);

            $flat->setUrl($url);

            if ($this->getInformation($crawledContent, $flat)) {
                fputcsv($csv, $flat->returnArray());
            }
        }
    }


    /**
     * @param string $crawledContent
     * @param Flat   $flat
     *
     * @return bool
     */
    public function getInformation(string $crawledContent, Flat $flat): bool
    {
        $doc = new DOMDocument();
        $doc->loadHTML($crawledContent);

        $xpath = new DOMXPath($doc);

        if ($xpath->evaluate('//h1')->item(0)->textContent === $this->getNoLongerAvail()) {
            return false;
        }

        $flat->setLocation(
            $this->normalize(
                $doc->saveHTML($xpath->evaluate('//article/p')->item(0))
            )
        );
        $flat->setPrice($xpath->evaluate('//article/div/h2')->item(0)->textContent);
        $flat->setTitle($xpath->evaluate('//article/h2')->item(0)->textContent);

        return true;
    }


    /**
     * @return false|resource
     */
    protected function createCsv()
    {
        $outputFilename = $this->getOutputFilename();

        if (file_exists($outputFilename)) {
            unlink($outputFilename);
        }

        $fp = fopen($outputFilename, 'wb');
        fputcsv($fp, ['Address', 'Price', 'Rooms', 'Url']);

        return $fp;
    }


    /**
     * @param string $string
     *
     * @return string
     */
    protected function normalize(string $string): string
    {
        return strip_tags(str_replace('<br>', ' ', $string));
    }


    /**
     * @param string $url
     *
     * @return bool|string
     */
    public function getContentByUrl(string $url): bool | string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:7.0.1) Gecko/20100101 Firefox/7.0.12011-10-16 20:23:00']);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getFavorites(): array
    {
        return $this->favorites;
    }

    /**
     * @param array $favorites
     *
     * @return Crawler
     */
    public function setFavorites(array $favorites): Crawler
    {
        $this->favorites = $favorites;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputFilename(): string
    {
        return $this->outputFilename;
    }

    /**
     * @return string
     */
    public function getInputFilename(): string
    {
        return $this->inputFilename;
    }

    /**
     * @return string
     */
    public function getNoLongerAvail(): string
    {
        return $this->noLongerAvail;
    }
}