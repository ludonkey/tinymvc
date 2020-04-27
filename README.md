# [[ Tiny MVC ]]

## Description

It's a light weight MVC implementation in **PHP**.

I created this for my students, first step to learn **MVC** before to use Symfony.

## Getting Started

Your front controller should be something like this **public/index.php**

```php
<?php

use ludk\Http\Kernel;
use ludk\Http\Request;

require '../vendor/autoload.php';

$kernel = new Kernel();
$request = new Request($_GET, $_POST, $_SERVER, $_COOKIE);
$response = $kernel->handle($request);
$response->send();
```

## Routing

By default, the **Kernel** will try to load the routes from **config/routes.yaml** but you can set another file when creating the Kernel object.
His role is to reroute the **Request** to the right **Controller** and the **right method** of this controller.

Example:

```yaml
homepage:
    path: /
    controller: Controller\HomeController:display

search:
    path: /search
    controller: Controller\HomeController:search

remove:
    path: /remove
    controller: Controller\HomeController:remove

create:
    path: /create
    controller: Controller\HomeController:create

update:
    path: /update
    controller: Controller\HomeController:update
```

## Controller

Your controllers have to extends **AbstractController**.
The **methods** take the **Request as parameter** and have to **return a Response**.

```php
<?php

namespace Controller;

use Entity\Card;
use ludk\Http\Request;
use ludk\Http\Response;
use ludk\Controller\AbstractController;

class HomeController extends AbstractController
{

    public function display(Request $request): Response
    {
        $cardRepository = $this->getOrm()->getRepository(Card::class);
        $cards = $cardRepository->findAll();
        $data = array(
            "myText" => "Hello everybody !",
            "cards" => $cards
        );

        return $this->render('home/main.php', $data);
    }
```

## Model

To make things easier, there is no database here, all the **data come from json files**.
They are **loaded in memory into the user session** so you can add data, delete, update but they will be restored as the original ones when a new session will start.

TODO...