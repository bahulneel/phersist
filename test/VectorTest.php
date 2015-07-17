<?php

class VectorTest extends PHPUnit_Framework_TestCase
{
    public function testPushingNewVals()
    {
        $vector = new \Phersist\Vector();
        $max = 2;
        for ($i = 0; $i < $max; $i += 1) {
            $vector = $vector->push(-$i);
        }
        for ($i = 0; $i < $max; $i += 1) {
            $this->assertEquals(-$i, $vector[$i]);
        }
        print_r($vector);
    }
}