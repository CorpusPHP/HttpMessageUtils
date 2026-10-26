<?php

$finder = PhpCsFixer\Finder::create()
	->files()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/test')
	->name('*.php');

$config = Corpus\PhpCsFixer\Config::php8x1();

return $config
	->setFinder($finder)
	->setUnsupportedPhpVersionAllowed(true);
