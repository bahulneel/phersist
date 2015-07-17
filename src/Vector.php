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

    public function __construct()
    {
        $this->root = new Node();
        $this->length = 0;
    }

    public function push($value)
    {
        $newVector = clone $this;
        $path = $this->offsetToPath($this->length);
        print_r($path);
        $newVector->root = $this->root->assoc($path, $value);
        $newVector->length = $this->length + 1;
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
            array_push($path, $val);
        }
        return $path;
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
        $path = $this->offsetToPath($offset);
        return $this->root->get($path) instanceof Leaf;
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
        $path = $this->offsetToPath($offset);
        $leaf = $this->root->get($path);

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