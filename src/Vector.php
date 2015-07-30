<?php
namespace Phersist;

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

    public function __construct()
    {
        $this->root = new Node();
        $this->tail = new Node();
        $this->length = 0;
        $this->tailLength = 0;
        $path = $this->offsetToPath(0);
        array_pop($path);
        $this->tailPath = $path;
    }

    public function push($value)
    {
        $newVector = clone $this;
        $newVector->length = $this->length + 1;
        $path = $this->offsetToPath($this->length);
        $pos = array_pop($path);
        if ($this->tailLength < 32) {
            $tail = $this->tail->assoc([$pos], $value);
            $newVector->tail = $tail;
            $newVector->tailLength++;
        } else {
            $newVector->tail = new Node();
            $newVector->tail->set([$pos], $value);
            $newVector->tailLength = 1;
            $newVector->tailPath = $path;
            $newVector->root = $this->root->assocNode($this->tailPath, $this->tail);
        }
        return $newVector;
    }
    private function offsetToPath($offset)
    {
        $shift = 5;
        $path = [];
        if ($offset > 0xffffffff) {
            throw new \Exception('offset too large');
        }
        $i = 0;
        for ($i = 0; $i < 32; $i += $shift) {
            $val = $offset >> $i;
            $val &= 0x1f;
            array_unshift($path, $val);
        }
        return $path;
    }

    private function getLeaf($offset)
    {
        $tailOffset = $this->length - $this->tailLength;
        if ($offset < $tailOffset) {
            $path = $this->offsetToPath($offset);
            $leaf = $this->root->get($path);
        } else {
            $offset -= $tailOffset;
            $leaf = $this->tail->get([$offset]);
        }
        return $leaf;
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