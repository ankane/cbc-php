<?php

namespace Cbc;

class Model
{
    private $ffi;
    private $model;

    private function __construct()
    {
        $this->ffi = FFI::instance();
        $this->model = new Pointer($this->ffi->Cbc_newModel(), $this->ffi->Cbc_deleteModel);
        $this->ffi->Cbc_setLogLevel($this->model->ptr, 0);
    }

    public static function loadProblem(
        $sense,
        $start,
        $index,
        $value,
        $colLower,
        $colUpper,
        $obj,
        $rowLower,
        $rowUpper,
        $colType
    ) {
        $model = new Model();
        $ffi = $model->ffi;
        $ptr = $model->model->ptr;

        $startSize = count($start);
        $indexSize = count($index);
        $numCols = count($colLower);
        $numRows = count($rowLower);

        $ffi->Cbc_loadProblem(
            $ptr,
            $numCols,
            $numRows,
            self::bigIndexArray($start, $startSize),
            self::intArray($index, $indexSize),
            self::doubleArray($value, $indexSize),
            self::doubleArray($colLower, $numCols),
            self::doubleArray($colUpper, $numCols),
            self::doubleArray($obj, $numCols),
            self::doubleArray($rowLower, $numRows),
            self::doubleArray($rowUpper, $numRows)
        );
        $ffi->Cbc_setObjSense($ptr, $sense->value);

        if (count($colType) != $numCols) {
            throw new \InvalidArgumentException('wrong size');
        }

        for ($i = 0; $i < count($colType); $i++) {
            switch ($colType[$i]) {
                case ColType::Integer:
                    $ffi->Cbc_setInteger($ptr, $i);
                    break;
                case ColType::Continuous:
                    $ffi->Cbc_setContinuous($ptr, $i);
                    break;
                default:
                    throw new \InvalidArgumentException('Unknown colType');
            }
        }

        return $model;
    }

    public static function readLp($filename)
    {
        $model = new Model();
        $model->checkStatus($model->ffi->Cbc_readLp($model->model->ptr, $filename));
        return $model;
    }

    public static function readMps($filename)
    {
        $model = new Model();
        $model->checkStatus($model->ffi->Cbc_readMps($model->model->ptr, $filename));
        return $model;
    }

    public function writeLp($filename)
    {
        $this->ffi->Cbc_writeLp($this->model->ptr, $filename);
    }

    public function writeMps($filename)
    {
        $this->ffi->Cbc_writeMps($this->model->ptr, $filename);
    }

    public function solve($logLevel = null, $timeLimit = null)
    {
        try {
            if (!is_null($logLevel)) {
                $previousLogLevel = $this->ffi->Cbc_getLogLevel($this->model->ptr);
                $this->ffi->Cbc_setLogLevel($this->model->ptr, $logLevel);
            }

            if (!is_null($timeLimit)) {
                $previousTimeLimit = $this->ffi->Cbc_getMaximumSeconds($this->model->ptr);
                $this->ffi->Cbc_setMaximumSeconds($this->model->ptr, $timeLimit);
            }

            // do not check status
            $this->ffi->Cbc_solve($this->model->ptr);
        } finally {
            if (isset($previousLogLevel)) {
                $this->ffi->Cbc_setLogLevel($this->model->ptr, $previousLogLevel);
            }

            if (isset($previousTimeLimit)) {
                $this->ffi->Cbc_setMaximumSeconds($this->model->ptr, $previousTimeLimit);
            }
        }

        $numCols = $this->ffi->Cbc_getNumCols($this->model->ptr);
        $status = $this->ffi->Cbc_status($this->model->ptr);
        $secondaryStatus = $this->ffi->Cbc_secondaryStatus($this->model->ptr);

        if ($status == -1) {
            if ($this->ffi->Cbc_isInitialSolveProvenOptimal($this->model->ptr) != 0) {
                $retStatus = Status::Optimal;
            } elseif ($this->ffi->Cbc_isInitialSolveProvenPrimalInfeasible($this->model->ptr) != 0) {
                $retStatus = Status::PrimalInfeasible;
            }
        } elseif ($status == 0) {
            if ($this->ffi->Cbc_isProvenOptimal($this->model->ptr)) {
                $retStatus = Status::Optimal;
            } elseif ($this->ffi->Cbc_isProvenInfeasible($this->model->ptr)) {
                $retStatus = Status::Infeasible;
            }
        }

        if (!isset($retStatus)) {
            $statusMap = [
                -1 => Status::Unset,
                0 => Status::Completed,
                1 => Status::Infeasible,
                2 => Status::StoppedGap,
                3 => Status::StoppedNodes,
                4 => Status::StoppedTime,
                5 => Status::StoppedUser,
                6 => Status::StoppedSolutions,
                7 => Status::Unbounded,
                8 => Status::StoppedIterations
            ];
            $retStatus = $statusMap[$secondaryStatus];
        }

        $objective = $this->ffi->Cbc_getObjValue($this->model->ptr);
        $solution = $this->ffi->Cbc_getColSolution($this->model->ptr);

        return [
            'status' => $retStatus,
            'objective' => $objective,
            'primalCol' => $this->readDoubleArray($solution, $numCols),
        ];
    }

    private function checkStatus($status)
    {
        if ($status != 0) {
            throw new Exception('Bad status: ' . $status);
        }
    }

    private static function doubleArray($value, $size)
    {
        return self::baseArray($value, $size, 'double');
    }

    private static function intArray($value, $size)
    {
        return self::baseArray($value, $size, 'int');
    }

    private static function bigIndexArray($value, $size)
    {
        return self::intArray($value, $size);
    }

    private static function baseArray($value, $size, $type)
    {
        $data = FFI::instance()->new("{$type}[$size]");
        for ($i = 0; $i < $size; $i++) {
            $data[$i] = $value[$i];
        }
        return $data;
    }

    private function readDoubleArray($ptr, $size)
    {
        $arr = [];
        for ($i = 0; $i < $size; $i++) {
            $arr[] = $ptr[$i];
        }
        return $arr;
    }
}
