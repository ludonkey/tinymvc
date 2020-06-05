<?php

namespace ludk\Controller;

use ludk\Http\Kernel;
use ludk\Http\Response;
use ludk\Persistence\ORM;

abstract class AbstractController
{
    private $globalVariables;
    private $orm;

    public function getOrm($resourcesDirPath = null): ORM
    {
        if (empty($this->orm)) {
            if (empty($resourcesDirPath)) {
                $resourcesDirPath = Kernel::getProjectDir() . 'Resources';
            }
            $this->orm = new ORM($resourcesDirPath);
        }
        return $this->orm;
    }

    public function setGlobalVariables(array $globalVariables)
    {
        $this->globalVariables = $globalVariables;
    }

    protected function render(string $view, array $data = []): Response
    {
        $response = new Response();
        if ($this->viewExists($view)) {
            $htmlContent = $this->renderView($view, $data);
            $response->setStatusCode(200);
            $response->setContent($htmlContent);
        } else {
            $response->setStatusCode(500);
            $response->setContent("ERROR: The view <strong>$view</strong> doesn't exist.");
        }
        return $response;
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        $redirectingResponse = new Response();
        $redirectingResponse->setStatusCode($status);
        $redirectingResponse->setHeaders(array("Location" => $url));
        return $redirectingResponse;
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): Response
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    protected function generateUrl(string $route, array $parameters = []): string
    {
        $fullUrl = '';
        $route = Kernel::getCurrent()->getRouteFromName($route);
        if (null != $route) {
            $fullUrl = $route->generate($parameters);
        }
        return $fullUrl;
    }

    private function viewExists(string $view): bool
    {
        return file_exists($this->getViewFullpath($view));
    }

    protected function getViewFullpath(string $view): string
    {
        $templatesPath = Kernel::getProjectDir() . 'templates' . DIRECTORY_SEPARATOR;
        return $templatesPath . $view;
    }

    public function renderView(string $view, array $data)
    {
        foreach ($this->globalVariables as $key => $value) {
            ${$key} = $value;
        }
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }
        ob_start();
        $phpCodeFromView = file_get_contents($this->getViewFullpath($view));

        // include
        $callback = new MyIncludeCallback($data, $this);
        $phpCodeFromView = preg_replace_callback(
            '/{%(\s*)include(\s*)(\S+)(\s*)%}/',
            array($callback, 'callback'),
            $phpCodeFromView
        );
        // echo variable
        $phpCodeFromView = preg_replace(
            '/{{(\s*)([\w\->]*)(\s*)}}/',
            '<?= $$2 ?>',
            $phpCodeFromView,
            -1,
            $varReplacementCount
        );
        // echo url function
        $phpCodeFromView = preg_replace('/{{(\s*)url(.*)(\s*)}}/', '<?= $this->generateUrl$2 ?>', $phpCodeFromView);
        // echo function
        $phpCodeFromView = preg_replace('/{{(\s*)(.*)(\s*)}}/', '<?= $2 ?>', $phpCodeFromView);

        eval('?>' . $phpCodeFromView . '<?php ');
        $res =  ob_get_clean();
        $pos = strpos($res, "AbstractController");
        if ($pos === false) {
            return $res;
        } else {
            die($res);
        }
    }
}

class MyIncludeCallback
{
    private $data;
    private $controller;

    function __construct($data, &$controller)
    {
        $this->data = $data;
        $this->controller = $controller;
    }

    public function callback($matches)
    {
        $viewName = $matches[3];
        $viewName = str_replace("'", '', $viewName);
        $viewName = str_replace('"', '', $viewName);
        return $this->controller->renderView($viewName, $this->data);
    }
}
