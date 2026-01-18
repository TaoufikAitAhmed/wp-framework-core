<?php

use themes\PhpCsFixer\Config\Factory;
use themes\PhpCsFixer\Config\RuleSet\Php74;
use PhpCsFixer\Finder;

$config = Factory::fromRuleSet(new Php74());

$finder = Finder::create()
                ->in([
                    __DIR__,
                ])
                ->exclude('vendor')
                ->exclude('tests/wordpress')
                ->exclude('tests/theme')
                ->ignoreDotFiles(true)
                ->ignoreVCS(true);

return $config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
