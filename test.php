<?php
require_once("php/class/MysqliDb.php");
require_once("php/class/Model.php");
include("php/class/StrHtml.php");
include("php/class/Sync.php");
$sync = new Sync("PH002","Dz73ZtTAu1");
$test = $sync->online_practice();
