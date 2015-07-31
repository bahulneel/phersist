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
     * @param array $path
     * @param int $depth
     * @return null|Leaf|Node
     */
    public function get(array $path, $depth = 0)
    {
        $next = $path[$depth];
        $node = $this->children[$next];

        ++$depth;
        if ($node instanceof Node) {
            return $node->get($path, $depth);
        }

        if ($node instanceof Leaf) {
            $path = array_slice($path, $depth);
            if ($node->eqPath($path)) {
                return $node;
            }
        }

        return null;
    }

    public function set($path, $value)
    {
        $next = $path[0];
        $path = array_slice($path, 1);

        if (!isset($this->children[$next]) || !count($path)) {
            $this->children[$next] = new Leaf($path, $value);
            return true;
        }

        $leaf = $this->children[$next];

        if ($leaf instanceof Node) {
            return $leaf->set($path, $value);
        }

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
        $next = $path[0];
        $path = array_slice($path, 1);

        if (!isset($newNode->children[$next]) || !count($path)) {
            $newNode->children[$next] = new Leaf($path, $value);
            return $newNode;
        }
        $leaf = $newNode->children[$next];
        if ($leaf instanceof Node) {
            $newNode->children[$next] = $leaf->assoc($path, $value);
            return $newNode;
        }
        $newChild = new Node();
        $newNode->children[$next] = $newChild;
        $newChild->set($leaf->path(), $leaf->value());
        $newChild->set($path, $value);
        return $newNode;
    }

    public function assocNode($path, Node $node)
    {
        $newNode = clone $this;
        $next = $path[0];
        $path = array_slice($path, 1);

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
