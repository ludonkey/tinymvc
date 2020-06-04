<?php

namespace ludk\Http;

class Session
{
    public function start()
    {
        session_start();
    }

    public function getId()
    {
        return session_id();
    }

    public function setId(string $id)
    {
        session_id($id);
    }

    public function getName()
    {
        return session_name();
    }

    public function setName(string $name)
    {
        session_name($name);
    }

    public function invalidate()
    {
        session_unset();
    }

    public function has(string $name)
    {
        return isset($_SESSION[$name]);
    }

    public function get(string $name, $default = null)
    {
        return $_SESSION[$name] ?? $default;
    }

    public function set(string $name, $value)
    {
        $_SESSION[$name]  = $value;
    }

    public function all()
    {
        return $_SESSION;
    }

    public function replace(array $attributes)
    {
        session_unset();
        foreach ($attributes as $name => $value) {
            $_SESSION[$name]  = $value;
        }
    }

    public function remove(string $name)
    {
        unset($_SESSION[$name]);
    }

    public function clear()
    {
        session_unset();
    }

    public function isStarted()
    {
        return session_status() != PHP_SESSION_NONE;
    }
}
