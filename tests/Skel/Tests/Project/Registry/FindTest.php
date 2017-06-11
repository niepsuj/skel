<?php

namespace Skel\Tests\Project\Registry;

use PHPUnit\Framework\TestCase;
use Skel\Project\Registry;

class FindTest extends TestCase
{
    protected static function getRegistry()
    {
        $registry = new Registry();
        return $registry->set(
            json_decode(
                file_get_contents(TEST_ROOT_PATH.'/Fixtures/config/states.json'),
                true
            )
        );
    }

    public function testFind()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->find(['abbreviation' => 'CA']);

        $this->assertEquals(
            [['name' => 'California', 'abbreviation' => 'CA']],
            $result
        );
    }

    public function testFindWithCallback()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->find(function($row){
            return $row['name']{0} == 'C';
        });

        $this->assertEquals([
            ['name' => 'California', 'abbreviation' => 'CA'],
            ['name' => 'Colorado', 'abbreviation' => 'CO'],
            ['name' => 'Connecticut', 'abbreviation' => 'CT']
        ], $result);
    }

    public function testFindOne()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->findOne(['abbreviation' => 'CT']);

        $this->assertEquals(
            ['name' => 'Connecticut', 'abbreviation' => 'CT'],
            $result
        );
    }

    public function testFindOneWithCallback()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->findOne(function($row){
            return 'Cali' === substr($row['name'], 0, 4);
        });

        $this->assertEquals(
            ['name' => 'California', 'abbreviation' => 'CA'],
            $result
        );
    }

    public function testCount()
    {
        $find = new Registry\Find(self::getRegistry());
        $this->assertEquals(1, $find->count(['abbreviation' => 'CT']));
    }

    public function testFindBy()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->findByAbbreviation('CT');

        $this->assertEquals(
            [['name' => 'Connecticut', 'abbreviation' => 'CT']],
            $result
        );
    }

    public function testFindOneBy()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->findOneByAbbreviation('CT');

        $this->assertEquals(
            ['name' => 'Connecticut', 'abbreviation' => 'CT'],
            $result
        );
    }

    public function testFindEmptyResult()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->findByAbbreviation('AAA');

        $this->assertEquals([], $result);
    }

    public function testFindOneEmptyResult()
    {
        $find = new Registry\Find(self::getRegistry());
        $result = $find->findOneByAbbreviation('AAA');

        $this->assertNull($result);
    }

    public function testCountBy()
    {
        $find = new Registry\Find(self::getRegistry());
        $this->assertEquals(1, $find->countByAbbreviation('CT'));
    }
}