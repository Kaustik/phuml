#!/usr/bin/env php
<?php
require_once dirname(__FILE__).'/../config/config.php';

/**
 * @param int $pullrequestId
 * @param int $page 1 and increaseing
 * @return array|string[] empty array if no files on page
 */
function getAffectedFilesForPullrequestAndPage($pullrequestId, $page)
{
    // -I get only headers
    $cmd = "curl -H 'Authorization: token cd1bc496b8d4c2d145e68ebab0d7233fde35660d' ".
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
    
    $page = 1;
    $files = getAffectedFilesForPullrequestAndPage($pullrequestId, $page);
    $allFiles = $files;
    while (count($files) > 0) {
        $page++;
        $files = getAffectedFilesForPullrequestAndPage($pullrequestId, $page);
        $allFiles = array_merge($allFiles, $files);
    }
    
    foreach ($allFiles as $file) {
        if (substr($file, -4, 4) != '.php') {
            continue;
        }

        if (substr($file, -8, 8) == 'User.php') {
            #continue;
        }

        if (substr($file, -8, 8) == 'Test.php') {
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