<?php

use ludk\Persistence\ORM;
use ludk\Tests\Entity\Card;

include "vendor/autoload.php";

$orm = new ORM(__DIR__ . '/Resources');
$allCards = $orm->getRepository(Card::class)->findAll();

var_dump($allCards);