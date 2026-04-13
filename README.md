# Cbc PHP

[Cbc](https://github.com/coin-or/Cbc) - the mixed-integer programming solver - for PHP

[![Build Status](https://github.com/ankane/cbc-php/actions/workflows/build.yml/badge.svg)](https://github.com/ankane/cbc-php/actions)

## Installation

First, install Cbc. For Homebrew, use:

```sh
brew install cbc
```

And for Ubuntu, use:

```sh
sudo apt-get install coinor-libcbc-dev
```

Then run:

```sh
composer require ankane/cbc
```

## Getting Started

*The API is fairly low-level at the moment*

Load a problem

```php
$model = Cbc\Model::loadProblem(
    sense: Cbc\Sense::Minimize,
    start: [0, 3, 6],
    index: [0, 1, 2, 0, 1, 2],
    value: [2, 3, 2, 2, 4, 1],
    colLower: [0, 0],
    colUpper: [1e30, 1e30],
    obj: [8, 10],
    rowLower: [7, 12, 6],
    rowUpper: [1e30, 1e30, 1e30],
    colType: [Cbc\ColType::Integer, Cbc\ColType::Continuous]
);
```

Solve

```php
$model->solve();
```

Write the problem to an LP or MPS file

```php
$model->writeLp('hello.lp');
// or
$model->writeMps('hello'); // adds .mps.gz
```

Read a problem from an LP or MPS file

```php
$model = Cbc\Model::readLp('hello.lp');
// or
$model = Cbc\Model::readMps('hello.mps.gz');
```

## Reference

Set the log level

```php
$model->solve(logLevel: 1); // 0 = off, 3 = max
```

Set the time limit in seconds

```php
$model->solve(timeLimit: 30);
```

## History

View the [changelog](https://github.com/ankane/cbc-php/blob/master/CHANGELOG.md)

## Contributing

Everyone is encouraged to help improve this project. Here are a few ways you can help:

- [Report bugs](https://github.com/ankane/cbc-php/issues)
- Fix bugs and [submit pull requests](https://github.com/ankane/cbc-php/pulls)
- Write, clarify, or fix documentation
- Suggest or add new features

To get started with development:

```sh
git clone https://github.com/ankane/cbc-php.git
cd cbc-php
composer install
composer test
```
