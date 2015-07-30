<?php
namespace Phersist\Trie;

class Node
{
    /**
     * @var null|\SplFixedArray
     */
    private $children;

    public function __construct($children = null)
    {
        if (!$children) {
            $children = new \SplFixedArray(32);
        }
        $this->children = $children;
    }

    public function children()
    {
        return $this->children;
    }

    /**
     * @param $path
     * @return Node|Leaf|null
     */
    public function get($path)
    {
        $next = array_shift($path);
        $node = $this->children[$next];

        if ($node instanceof Node) {
            return $node->get($path);
        }

        if ($node instanceof Leaf && $node->path() === $path) {
            return $node;
        }

        return null;
    }

    public function set($path, $value)
    {
        $next = array_shift($path);
        if (!isset($this->children[$next])) {
            $this->children[$next] = new Leaf($path, $value);
            return true;
        }
        if ($this->children[$next] instanceof Node) {
            return $this->children[$next]->set($path, $value);
        }
        $leaf = $this->children[$next];
        $this->children[$next] = new Node();
        $this->children[$next]->set($leaf->path(), $leaf->value());
        return $this->children[$next]->set($path, $value);
    }

    public function __clone()
    {
        $this->children = clone $this->children;
    }

    public function assoc($path, $value)
    {
        $newNode = clone $this;
        $next = array_shift($path);
        if (!isset($newNode->children[$next])) {
            $newNode->children[$next] = new Leaf($path, $value);
            return $newNode;
        }
        if ($newNode->children[$next] instanceof Node) {
            $newNode->children[$next] = $newNode->children[$next]->assoc($path, $value);
            return $newNode;
        }
        $leaf = $newNode->children[$next];
        $newNode->children[$next] = new Node();
        $newNode->children[$next]->set($leaf->path(), $leaf->value());
        $newNode->children[$next]->set($path, $value);
        return $newNode;
    }

    public function assocNode($path, Node $node)
    {
        $newNode = clone $this;
        $next = array_shift($path);
        if (!count($path)) {
            $newNode->children[$next] = $node;
            return $newNode;
        }

        if (!isset($newNode->children[$next])) {
            $newNode->children[$next] = new Node();
        }

        $newNode->children[$next] = $newNode->children[$next]->assocNode($path, $node);
        return $newNode;
    }
}
