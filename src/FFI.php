<?php

namespace Cbc;

class FFI
{
    public static $lib;

    private static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            // https://github.com/coin-or/Cbc/blob/master/src/Cbc_C_Interface.h
            self::$instance = \FFI::cdef('
                typedef int CoinBigIndex;
                typedef struct Cbc_Model Cbc_Model;

                const char * Cbc_getVersion(void);

                Cbc_Model * Cbc_newModel(void);
                void Cbc_loadProblem(Cbc_Model *model, const int numcols, const int numrows, const CoinBigIndex *start, const int *index, const double *value, const double *collb, const double *colub, const double *obj, const double *rowlb, const double *rowub);
                void Cbc_setObjSense(Cbc_Model *model, double sense);
                void Cbc_setContinuous(Cbc_Model *model, int iColumn);
                void Cbc_setInteger(Cbc_Model *model, int iColumn);
                void Cbc_deleteModel(Cbc_Model *model);

                int Cbc_readMps(Cbc_Model *model, const char *filename);
                void Cbc_writeMps(Cbc_Model *model, char *filename);

                int Cbc_getNumCols(Cbc_Model *model);
                int Cbc_solve(Cbc_Model *model);
                const double * Cbc_getColSolution(Cbc_Model *model);
                int Cbc_isProvenOptimal(Cbc_Model *model);
                int Cbc_isProvenInfeasible(Cbc_Model *model);
                int Cbc_isInitialSolveProvenOptimal(Cbc_Model *model);
                int Cbc_isInitialSolveProvenPrimalInfeasible(Cbc_Model *model);
                double Cbc_getObjValue(Cbc_Model *model);
                int Cbc_status(Cbc_Model *model);
                int Cbc_secondaryStatus(Cbc_Model *model);

                int Cbc_readLp(Cbc_Model *model, const char *filename);
                void Cbc_writeLp(Cbc_Model *model, char *filename);

                double Cbc_getMaximumSeconds(Cbc_Model *model);
                void Cbc_setMaximumSeconds(Cbc_Model *model, double maxSeconds);
                int Cbc_getLogLevel(Cbc_Model *model);
                void Cbc_setLogLevel(Cbc_Model *model, int logLevel);
            ', self::$lib ?? self::defaultLib());
        }

        return self::$instance;
    }

    private static function defaultLib()
    {
        if (PHP_OS_FAMILY == 'Windows') {
            // TODO test
            return 'CbcSolver.dll';
        } elseif (PHP_OS_FAMILY == 'Darwin') {
            if (php_uname('m') == 'x86_64') {
                return '/usr/local/lib/libCbcSolver.dylib';
            } else {
                return '/opt/homebrew/lib/libCbcSolver.dylib';
            }
        } else {
            return 'libCbcSolver.so';
        }
    }
}
