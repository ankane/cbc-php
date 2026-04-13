<?php

namespace Cbc;

enum Sense: int
{
    case Minimize = 1;
    case Ignore = 0;
    case Maximize = -1;
}
