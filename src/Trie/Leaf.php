<?php
namespace Trie;

class Leaf
{
    private $path;
    private $value;

    public function __construct($path, $value)
    {
        $this->path = $path;
        $this->value = $value;
    }

    public function path()
    {
        return $this->path;
    }

    public function value()
    {
        return $this->value;
    }
}
