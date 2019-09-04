#!/usr/bin/env php
<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
#Include
require_once __DIR__.'/include/lib/Date/Date.php';
require_once __DIR__.'/include/local/Config.php';
require_once __DIR__.'/include/local/Dump.php';
require_once __DIR__.'/include/local/DumpPostgreSQL.php';
require_once __DIR__.'/include/local/DumpMySQL.php';
require_once __DIR__.'/include/local/DumpJobs.php';
#/Include
$jobs = new DumpJobs($argv[1]);
$jobs->run();