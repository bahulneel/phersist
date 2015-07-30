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
}
