<?php

use Src\Crawler;

require_once(__DIR__ . '/vendor/autoload.php');

try {
    $crawler = new Crawler();
    $crawler->run();

    echo 'Done';
} catch (Throwable $e) {
    echo $e->getMessage();
}
