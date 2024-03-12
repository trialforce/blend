<?php

namespace Cache;

interface Service
{
    public function __construct(\Cache\ConnInfo $info);

    public function set($key, $value);

    public function get($key);

    public function del($key);

    public function exists($key);

    public function allKeys();
}