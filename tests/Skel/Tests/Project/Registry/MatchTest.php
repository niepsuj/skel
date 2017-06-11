<?php

namespace Skel\Tests\Project\Registry;

use PHPUnit\Framework\TestCase;
use Skel\Project\Registry;

class MatchTest extends TestCase
{
    public function testMatch()
    {
        $registry = new Registry();
        $registry->push(['id' => 1, 'flag' => true]);
        $registry->push(['id' => 2, 'flag' => false]);

        $match = new Registry\Match($registry);
        $match->match(['id' => 1], function($row){
            $row['flag'] = false;
            return $row;
        });

        $this->assertAttributeEquals(
            [
                ['id' => 1, 'flag' => false],
                ['id' => 2, 'flag' => false]
            ], 'registry', $registry
        );
    }

    public function testMatchRemove()
    {
        $registry = new Registry();
        $registry->push(['id' => 1, 'flag' => true]);
        $registry->push(['id' => 2, 'flag' => false]);

        $match = new Registry\Match($registry);
        $match->match(['id' => 1], function(){ return false; });

        $this->assertAttributeEquals(
            [['id' => 2, 'flag' => false]],
            'registry', $registry
        );
    }
    public function testMatchRemoveShort()
    {
        $registry = new Registry();
        $registry->push(['id' => 1, 'flag' => true]);
        $registry->push(['id' => 2, 'flag' => false]);

        $match = new Registry\Match($registry);
        $match->match(['id' => 1], false);

        $this->assertAttributeEquals(
            [['id' => 2, 'flag' => false]],
            'registry', $registry
        );
    }
}