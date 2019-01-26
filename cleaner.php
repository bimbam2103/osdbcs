#!/usr/bin/php
<?php

//Please enter your database login data

$dbserver = "localhost";
$dbuser = "opensim_db";
$dbpass = "opensim_pw";
$database = "opensim";

// Different settings

// Do you want to optimize the database (true|false)?

$optimizedb = true;

// --------------- NO USER SERVICEABLE PART ----------------

$startup = mktime();
$link = mysqli_connect($dbserver,$dbuser,$dbpass,$database);
if($link) {
    if(!defined("STDIN")) {
	print "ERROR: This script can only be executed from a console.\n";
	return 1;
    }
    if($argc < 2) {
	print "ERROR: Please specify OAR and/or IAR archives\n";
	print "Syntax: php " . $argv[0] . " <OAR|IAR|...>\n";
	return 1;
    }
    array_shift($argv);
    if(!file_exists("users.txt")) {
	print "ERROR: User file does not exist. Restore will not be possible. Aborting...\n";
	return 1;
    }
    $liveAssets = array();
    $assets = array("texture" => 0, "sound" => 1, "clothing" => 5, "script" => 10, "bodypart" => 13, "animation" => 20);
    $scriptReferences = array();
    $orphaned = array();
    foreach($argv as $arg) {
	if(!file_exists($arg)) {
	    print "ERROR: Archive " . $arg . " not found. Aborting...\n";
	    return 1;
	}
	$archive = pathinfo($arg);
	$archivename = $archive["filename"];
	exec("mkdir " . $archivename, $output, $ret);
	unset($output);
	if($ret != 0) {
	    print "ERROR: Could not create archive directory " . $archivename . ". Aborting...\n";
	    return 1;
	}
	exec("cd " . $archivename . " && tar -xzf ../" . $arg . " 2>/dev/null", $output, $ret);
	unset($output);
	if($ret != 0) {
	    print "ERROR: Could not extract archive " . $arg . ". Aborting...\n";
	    return 1;
	}
	if(is_dir("./" . $archivename . "/assets")) {
	    $files = scandir("./" . $archivename . "/assets");
	    foreach($files as $file) {
		if(!preg_match("/_script/i", $file)) continue;
		$data = file_get_contents("./" . $archivename . "/assets/" . $file);
		preg_match_all("/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-90]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i", $data, $referencesInScripts);
		foreach($referencesInScripts as $uuid) {
		    array_push($scriptReferences, $uuid);
		}
	    }
	    foreach($assets as $assetName => $assetValue) {
		preg_match_all("/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})_" . $assetName . "/i", implode($files), $dumpAssets);
		foreach($dumpAssets[1] as $uuid) {
		    array_push($liveAssets, $uuid);
		    
		}
	    }
	} else {
	    print "Asset-Directory not found in archive " . $arg . ". Going to the next archive...\n";
	}
	exec("rm -rf ./" . $archivename, $output, $ret);
	unset($output);
	if($ret != 0) {
	    print "ERROR: Could not remove temporary archive directory " . $archivename . ". Aborting...\n";
	    return 1;
	}
    }
    foreach($scriptReferences as $ref) {
	array_push($liveAssets, $ref);
    }
    print "Live Assets: " . count($liveAssets) . "\n";
    print "Checking now database assets...\n";
    $assetsFound = 0;
    $assetsNotFound = 0;
    foreach($assets as $assetName => $assetValue) {
	$request = mysqli_query($link, "select id,name from `assets` where (`assetType`) = '" . $assetValue . "' and (`CreatorID`) != '11111111-1111-0000-0000-000100bba000'");
	while($result = mysqli_fetch_array($request)) {
	    if(in_array($result["id"], $liveAssets)) {
		$assetsFound++;
	    } else {
		$assetsNotFound++;
		array_push($orphaned, $result["id"]);
	    }
	}
    }
    print "Assets found in live assets: $assetsFound\n";
    print "Assets orphaned: $assetsNotFound\n";
    print "Here are the tests:\n";
    print "Live Assets: " . count($liveAssets) . "\n";
    $allAssetNums = $assetsFound+$assetsNotFound;
    print "Checked Assets: $allAssetNums\n";
    if(count($liveAssets) > $allAssetNums) {
	$difference = count($liveAssets)-$allAssetNums;
    } else {
	$difference = $allAssetNums-count($liveAssets);
    }
    print "Difference: $difference\n";
    if($assetsNotFound > 0) {
	print "WARNING: Database cleaning will be continue in...\n";
	print "3\n";
	sleep(1);
	print "2\n";
	sleep(1);
	print "1\n";
	sleep(1);
	print "Cleaning now the database...\n";
	foreach($orphaned as $o) {
	    mysqli_query($link, "delete from `assets` where (`id`) = '" . $o . "'");
	    print "-";
	}
	print "\n";
	if($optimizedb) {
	    print "Optimizing database. This may take a while...\n";
	    mysqli_query($link, "optimize table assets");
	}
	print "Database cleanup complete. Congratulations. The asset database does not have any orphaned items anymore.\n";
    } else {
	print "VERY GOOD WORK. No orphaned assets were found. Your database is clean.\n";
    }
}
$finish = mktime();
$timetofinish = $finish-$startup;
print "Needed time to work: $timetofinish seconds\n";
?>