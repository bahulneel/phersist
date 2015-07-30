<?php

class VectorTest extends PHPUnit_Framework_TestCase
{
    public function testPushingNewVals()
    {
        $vector = new \Phersist\Vector();
        $max = 5000;
        for ($i = 0; $i < $max; $i += 1) {
            $vector = $vector->push(-$i);
            $this->assertEquals($i + 1, count($vector));
        }

        for ($i = 0; $i < $max; $i += 1) {
            $this->assertEquals(-$i, $vector[$i]);
        }
    }

    public function testAssocInTail()
    {
        $startVector = new \Phersist\Vector();
        $max = 32;
        for ($i = 0; $i < $max; $i += 1) {
            $startVector = $startVector->push(-$i);
        }

        $newVector = $startVector->assoc(16, "foo");
        for ($i = 0; $i < $max; $i += 1) {
            $this->assertEquals(-$i, $startVector[$i]);
            if ($i === 16) {
                $this->assertEquals("foo", $newVector[$i]);
            } else {
                $this->assertEquals(-$i, $newVector[$i]);
            }
        }
    }

    public function testAssocInHead()
    {
        $startVector = new \Phersist\Vector();
        $max = 64;
        for ($i = 0; $i < $max; $i += 1) {
            $startVector = $startVector->push(-$i);
        }

        $newVector = $startVector->assoc(16, "foo");
        for ($i = 0; $i < $max; $i += 1) {
            $this->assertEquals(-$i, $startVector[$i]);
            if ($i === 16) {
                $this->assertEquals("foo", $newVector[$i]);
            } else {
                $this->assertEquals(-$i, $newVector[$i]);
            }
        }
    }
}
