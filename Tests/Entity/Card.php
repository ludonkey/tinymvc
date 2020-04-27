<?php

namespace ludk\Tests\Entity;

use ludk\Utils\Serializer;

class Card
{
    public int $id;
    public string $title;
    public string $text;
    public Image $image;
    use Serializer;
}