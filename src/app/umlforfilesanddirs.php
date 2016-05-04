#!/usr/bin/env php
<?php
require __DIR__ . '/../../vendor/autoload.php';

function generate($argv)
{
    $phuml = new plPhuml();
    $phuml->generator = plStructureGenerator::factory('tokenparser');

    for ($i = 1; $i < count($argv); ++$i) {
        $path = $argv[$i];
        if (is_dir($path)) {
            $phuml->addDirectory($path);
        } else {
            $phuml->addFile($path);
        }
    }
    $grProcessor = new plGraphvizProcessor();
    $grProcessor->options->createAssociations = true;
    $phuml->addProcessor($grProcessor);
    $phuml->addProcessor(new plDotProcessor());
    $phuml->generate('/srv/aiai/current/log/uml.png');
}

generate($argv);
