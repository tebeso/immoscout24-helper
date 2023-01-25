<?php

include('Immoscout24Helper.php');

try {
    $immoscout24Helper = new Immoscout24Helper();
    $immoscout24Helper->run();
} catch (Throwable $e) {
    echo $e->getMessage();
}