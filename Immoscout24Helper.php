<?php

/**
 * https://www.google.com/maps/d/edit?hl=de&mid=1fdhmdVZvh_hwodpJM6_gfKu1j2adwuk&ll=47.54793160709045%2C8.979375164330271&z=11
 *
 * Favorites aus dem Ajax Return holen und in favorites.json speichern.
 */
class Immoscout24Helper
{
    /**
     * @var string
     */
    protected string $url = 'https://www.immoscout24.ch/en/d/flat-rent-bern/';

    /**
     * @var string
     */
    protected string $outputFilename = 'places.csv';

    /**
     * @var array
     */
    protected array $favorites = [];

    /**
     * @throws JsonException
     */
    public function __construct()
    {
        $file = file_get_contents('favorites.json');
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
            $url            = $this->getUrl() . $favorite->propertyId;
            $crawledContent = $this->getContentByUrl($url);
            $info           = $this->getInformation($crawledContent);

            $info[3] = $url;
            if (!str_contains($info[1], 'immo') && !str_contains($info[1], 'Immo') && strlen($info[1]) < 15 && !str_contains($info[2], 'immoscout24.ch')) {
                fputcsv($csv, array_slice($info, 0, 4));
            }
        }
    }


    /**
     * @param string $crawledContent
     *
     * @return string[]
     */
    public function getInformation(string $crawledContent): array
    {
        $doc = new DOMDocument();
        $doc->loadHTML($crawledContent);
        $text = $doc->childNodes->item(1)->textContent;

        return explode(' - ', trim(str_replace('\n', '', explode('|', $text)[0])));
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
     * @return Immoscout24Helper
     */
    public function setFavorites(array $favorites): Immoscout24Helper
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
}