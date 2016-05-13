#!/usr/bin/env php
<?php
require 'vendor/autoload.php';

/**
 * @param int $pullrequestId
 * @param int $page 1 and increaseing
 * @param string $token
 * @return array|string[] empty array if no files on page
 */
function getAffectedFilesForPullrequestAndPage($pullrequestId, $page, $token)
{
    // -I get only headers
    $cmd = "curl -H 'Authorization: token $token' ".
    "https://api.github.com/repos/kaustik/aiai/pulls/{$pullrequestId}/files?page={$page}";
    $jsonResult = `$cmd`;

    /** @var stdClass[] $prFileList */
    $prFileList = json_decode($jsonResult);

    if ($prFileList instanceof \stdClass) {
        throw new \Exception("Github error: {$prFileList->message}");
    }
    
    $list = [];
    foreach ($prFileList as $file) {
        if ($file->status == 'removed') {
            continue;
        }
        $filename = $file->filename;
        $list[] = $filename;
    }

    return $list;
}


function generate()
{
    $phuml = new plPhuml();
    $phuml->generator = plStructureGenerator::factory('tokenparser');
    $options = getopt('t:p:f:o:b:'); //token pullrequestid filter

    if (isset($options['f'])) {
        $type = $options['f']; //test or user or empty string
    } else {
        $type = '';
    }
    if (isset($options['t'])) {
        $token = $options['t'];
    } else {
        echo "github token must be set";
        exit;
    }

    if (isset($options['p'])) {
        $pullrequestId = $options['p'];
    } else {
        echo "pullrequestid token must be set";
        exit;
    }

    if (isset($options['o'])) {
        $outputFile = $options['o'];
    } else {
        echo "outputfile -o must be set";
        exit;
    }

    if (isset($options['b'])) {
        $basepath = $options['b'];
    } else {
        echo "basepath -b must be set";
        exit;
    }
    $page = 1;
    $files = getAffectedFilesForPullrequestAndPage($pullrequestId, $page, $token);
    $allFiles = $files;
    while (count($files) > 0) {
        $page++;
        $files = getAffectedFilesForPullrequestAndPage($pullrequestId, $page, $token);
        $allFiles = array_merge($allFiles, $files);
        if ($page > 10) {
            echo "more than 10 pages. something wrong: $token $pullrequestId. Exiting";
            return;
        }
    }
    
    foreach ($allFiles as $file) {
        if (substr($file, -4, 4) != '.php') {
            continue;
        }

        if (substr($file, -8, 8) == 'User.php' && $type == 'test') {
            continue;
        }

        if (substr($file, -8, 8) == 'Test.php' && $type == 'user') {
            continue;
        }
        $phuml->addFile($basepath.$file);
    }
    $grProcessor = new plGraphvizProcessor();
    $grProcessor->options->createAssociations = true;
    $phuml->addProcessor($grProcessor);
    $phuml->addProcessor(new plDotProcessor());
    $phuml->generate($outputFile);
}

generate();
