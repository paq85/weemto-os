<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\Pro\Test\Unit\Csv;


use Paq\Pro\Csv\CsvIterator;

class CsvIteratorTest extends \PHPUnit_Framework_TestCase
{

    private $testStream;

    public function setUp()
    {
        $csv = "a1,a2,a3\nb1,b2,b3\nc1,c2,c3";
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $this->testStream = $stream;
    }

    public function tearDown()
    {
        if (is_resource($this->testStream)){
            fclose($this->testStream);
        }
    }

    public function testIterator()
    {
        $iterator = new CsvIterator($this->testStream);

        $this->assertTrue($iterator->valid());
        foreach ($iterator as $row) {
            $this->assertCount(3, $row);
        }

        $rowCount = 0;
        foreach ($iterator as $row) {
            $this->assertCount(3, $row);
            ++$rowCount;
        }

        $this->assertEquals(3, $rowCount);
    }

    public function testIteratorWithFormatter()
    {
        $formatter = function($row) {
            array_walk($row, function(&$value, $key) {
                $value = '{format}' . $value . '{format}';
            });

            return $row;
        };
        $iterator = new CsvIterator($this->testStream);
        $iterator->setFormatter($formatter);

        $firstRow = $iterator->current();
        $this->assertContains('{format}', $firstRow[0]);
    }
}
