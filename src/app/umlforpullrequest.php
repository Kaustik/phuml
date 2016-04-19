#!/usr/bin/env php
<?php
require __DIR__ . '/../../vendor/autoload.php';

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


function generate($argv)
{
    $phuml = new plPhuml();
    $phuml->generator = plStructureGenerator::factory('tokenparser');
    $pullrequestId = $argv[1];

    if (isset($argv[2])) {
        $type = $argv[2]; //test or user or empty string
    } else {
        $type = '';
    }
    if (isset($argv[3])) {
        $token = $argv[3]; //test or user or empty string
    } else {
        echo "github token must be set";
        exit;
    }
    
    $page = 1;
    $files = getAffectedFilesForPullrequestAndPage($pullrequestId, $page, $token);
    $allFiles = $files;
    while (count($files) > 0) {
        $page++;
        $files = getAffectedFilesForPullrequestAndPage($pullrequestId, $page, $token);
        $allFiles = array_merge($allFiles, $files);
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
        $phuml->addFile('/srv/aiai/current/'.$file);
    }
    $grProcessor = new plGraphvizProcessor();
    $grProcessor->options->createAssociations = true;
    $phuml->addProcessor($grProcessor);
    $phuml->addProcessor(new plDotProcessor());
    $phuml->generate('/srv/aiai/current/log/uml.png');
}

generate($argv);
