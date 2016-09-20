<?php namespace FrenchFrogs\App\Console;

use FrenchFrogs\Maker\Maker;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Class_;
use Symfony\Component\Console\Input\InputArgument;

abstract class CodeCommand extends Command
{
    const CHOICE_NEW = ' > Nouveau';
}