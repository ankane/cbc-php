<?php

namespace Cbc;

enum Status
{
    case Optimal;
    case PrimalInfeasible;
    case Unset;
    case Completed;
    case Infeasible;
    case StoppedGap;
    case StoppedNodes;
    case StoppedTime;
    case StoppedUser;
    case StoppedSolutions;
    case Unbounded;
    case StoppedIterations;
}
