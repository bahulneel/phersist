<?php
namespace Phersist;

use Phersist\Exception\IndexOutOfBounds;
use Phersist\Exception\MutationDisallowed;
use Phersist\Trie\Leaf;
use Phersist\Trie\Node;

class Vector implements \ArrayAccess, \Countable
{
    /**
     * @var Node
     */
    private $root;

    private $length;

    /**
     * @var Node
     */
    private $tail;

    private $tailLength;

    private $tailPath;

    private $tailOffset;

    public function __construct()
    {
        $this->root = new Node();
        $this->tail = new Node();
        $this->length = 0;
        $this->tailLength = 0;
        $this->tailOffset = null;
        $path = $this->offsetToPath(0, 7, 1);
        $this->tailPath = $path;
    }

    public function push($value)
    {
        $this->tailOffset = null;
        $newVector = clone $this;
        ++$newVector->length;
        $path = $this->offsetToPath($this->length, 1);
        $pos = $path[0];

        $tail = $this->tail->assoc([$pos], $value);
        $newVector->tail = $tail;
        ++$newVector->tailLength;
        if ($newVector->tailLength === 32) {
            $path = $this->offsetToPath($newVector->length, 7, 1);
            $newVector->tail = new Node();
            $newVector->tailLength = 0;
            $newVector->tailPath = $path;
            $newVector->root = $this->root->assocNode($this->tailPath, $tail);
        }

        return $newVector;
    }
    private function offsetToPath($offset, $maxDepth = 7, $skip = 0)
    {
        if ($offset > 0xffffffff) {
            throw new \Exception('offset too large');
        }
        $path = [];
        for (
            $p = $skip, $i = 5 * $skip;
            $i < 32 && $p < $maxDepth;
            $i += 5, ++$p
        ) {
            $path[$p] = ($offset >> $i) & 0x1f;
        }
        return array_reverse($path);
    }

    private function getLeaf($offset)
    {
        if ($this->tailOffset === null) {
            $this->tailOffset = $this->length - $this->tailLength;
        }

        if ($offset < $this->tailOffset) {
            $path = $this->offsetToPath($offset);
            $leaf = $this->root->get($path);
        } else {
            $offset -= $this->tailOffset;
            $leaf = $this->tail->get([$offset]);
        }
        return $leaf;
    }

    public function assoc($offset, $value)
    {
        if ($offset >= $this->length) {
            throw new IndexOutOfBounds("Offset must be less than {$this->length}");
        }

        if ($this->tailOffset === null) {
            $this->tailOffset = $this->length - $this->tailLength;
        }

        $newVector = clone $this;
        if ($offset < $this->tailOffset) {
            $path = $this->offsetToPath($offset);
            $newVector->root = $newVector->root->assoc($path, $value);
        } else {
            $offset -= $this->tailOffset;
            $newVector->tail = $newVector->tail->assoc([$offset], $value);
        }
        return $newVector;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        $leaf = $this->getLeaf($offset);
        return $leaf instanceof Leaf;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $leaf = $this->getLeaf($offset);

        if ($leaf instanceof Leaf) {
            return $leaf->value();
        }

        return null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new MutationDisallowed('Cannot mutate a vector');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new MutationDisallowed('Cannot mutate a vector');
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->length;
    }

}