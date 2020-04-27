<?php

namespace ludk\Http;

use ludk\Http\Route;
use ludk\Http\Request;
use ludk\Http\Response;
use Symfony\Component\Yaml\Yaml;

class Kernel
{
    protected $env;

    protected $routes;

    protected $globalVariables;

    public function __construct($routesFile = null)
    {
        session_start();
        if (empty($routesFile)) {
            $routesFile = Kernel::getProjectDir() . 'config' . DIRECTORY_SEPARATOR . 'routes.yaml';
        }
        $this->setEnv($_SERVER['APP_ENV'] ?? "");
        $this->setRoutes(Yaml::parseFile($routesFile));
        Kernel::$currentKernel = $this;
    }

    public function setEnv(string $env)
    {
        $this->env = $env;
        $gVariables = parse_ini_file(Kernel::getProjectDir() . '.env');
        if (!empty($env)) {
            $overrideVariables = parse_ini_file(Kernel::getProjectDir() . '.env.' . $env);
            $gVariables = array_merge($gVariables, $overrideVariables);
        }
        $this->globalVariables = $gVariables;
    }

    private function setRoutes(array $routesArray)
    {
        $routes = array();
        foreach ($routesArray as $routeName => $routeData) {
            $controllerToCall = explode(':', $routeData['controller']);
            $controllerClass = $controllerToCall[0];
            $controllerMethod = $controllerToCall[1];
            $newRoute = new Route();
            $newRoute->name = $routeName;
            $newRoute->path = $routeData['path'];
            $newRoute->controller = $controllerClass;
            $newRoute->function = $controllerMethod;
            $routes[$routeName] = $newRoute;
        }
        $this->routes = $routes;
    }

    public function getRouteFromRequest(Request $request): ?Route
    {
        $currentPath = $request->basePath;
        foreach ($this->routes as $oneRoute) {
            if ($oneRoute->path == $currentPath) {
                return $oneRoute;
            }
        }
        return null;
    }

    public function getRouteFromName(string $name): ?Route
    {
        if (array_key_exists($name, $this->routes)) {
            return $this->routes[$name];
        }
        return null;
    }

    public function handle(Request $request): Response
    {
        $response = null;
        $route = $this->getRouteFromRequest($request);
        if (!empty($route)) {
            $controllerClass = $route->controller;
            $controllerMethod = $route->function;
            $controller = new $controllerClass();
            $controller->setGlobalVariables($this->globalVariables);
            $response = $controller->$controllerMethod($request);
        } else {
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent("PAGE NOT FOUND: The path <strong>$request->basePath</strong> doesn't managed.");
        }
        return $response;
    }

    protected static $currentKernel;

    public static function getCurrent(): Kernel
    {
        return Kernel::$currentKernel;
    }

    public static function getProjectDir(): string
    {
        return join(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..')) . DIRECTORY_SEPARATOR;
    }
}