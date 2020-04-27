<?php

namespace ludk\Tests\Entity;

use ludk\Utils\Serializer;

class Image
{
    public int $id;
    public string $url;
    use Serializer;
}