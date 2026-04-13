<?php

use Tests\TestCase;

use Cbc\ColType;
use Cbc\Model;
use Cbc\Sense;
use Cbc\Status;

final class ModelTest extends TestCase
{
    public function testLibVersion()
    {
        $this->assertStringStartsWith('2.', Cbc\FFI::instance()->Cbc_getVersion());
    }

    public function testLoadProblemMip()
    {
        $model = Model::loadProblem(
            sense: Sense::Minimize,
            start: [0, 3, 6],
            index: [0, 1, 2, 0, 1, 2],
            value: [2, 3, 2, 2, 4, 1],
            colLower: [0, 0],
            colUpper: [1e30, 1e30],
            obj: [8, 10],
            rowLower: [7, 12, 6],
            rowUpper: [1e30, 1e30, 1e30],
            colType: [ColType::Integer, ColType::Integer]
        );

        $model->writeLp('/tmp/test.lp');
        $this->assertFileEquals('tests/support/test.lp', '/tmp/test.lp');

        $model->writeMps('/tmp/test');
        $this->assertFileExists('/tmp/test.mps.gz');

        $res = $model->solve();
        $this->assertEquals(Status::Optimal, $res['status']);
        $this->assertEquals(32, $res['objective']);
        $this->assertEquals([4, 0], $res['primalCol']);
    }

    public function testLoadProblemLp()
    {
        $model = Model::loadProblem(
            sense: Sense::Minimize,
            start: [0, 3, 6],
            index: [0, 1, 2, 0, 1, 2],
            value: [2, 3, 2, 2, 4, 1],
            colLower: [0, 0],
            colUpper: [1e30, 1e30],
            obj: [8, 10],
            rowLower: [7, 12, 6],
            rowUpper: [1e30, 1e30, 1e30],
            colType: [ColType::Continuous, ColType::Continuous]
        );

        $res = $model->solve();
        $this->assertEquals(Status::Optimal, $res['status']);
        $this->assertEqualsWithDelta($res['objective'], 31.2, 0.001);
        $this->assertEqualsWithDelta($res['primalCol'][0], 2.4, 0.001);
        $this->assertEqualsWithDelta($res['primalCol'][1], 1.2, 0.001);
    }

    public function testReadMps()
    {
        $model = Model::readMps('tests/support/test.mps');
        $res = $model->solve();
        $this->assertEquals(Status::Optimal, $res['status']);
        $this->assertEqualsWithDelta($res['objective'], 32, 0.001);
        $this->assertEquals([4, 0], $res['primalCol']);
    }

    public function testReadMpsGz()
    {
        $model = Model::readMps('tests/support/test.mps.gz');
        $res = $model->solve();
        $this->assertEquals(Status::Optimal, $res['status']);
        $this->assertEqualsWithDelta($res['objective'], 32, 0.001);
        $this->assertEquals([4, 0], $res['primalCol']);
    }

    public function testReadLp()
    {
        $model = Model::readLp('tests/support/test.lp');
        $res = $model->solve();
        $this->assertEquals(Status::Optimal, $res['status']);
        $this->assertEqualsWithDelta($res['objective'], 32, 0.001);
        $this->assertEquals([4, 0], $res['primalCol']);
    }

    public function testTimeLimit()
    {
        $model = Model::readMps('tests/support/test.mps');
        $res = $model->solve(timeLimit: 0.000001);
        $this->assertEquals(Status::StoppedTime, $res['status']);
    }
}
