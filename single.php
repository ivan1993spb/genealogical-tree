<?php

require_once "common.php";

$output = [];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	// Getting all single mans and womans
	$sth = $dbFamilies->query("SELECT `id`, `name`, `sex` FROM `persons`
		WHERE NOT EXISTS (SELECT 1 FROM `family_pairs`
			WHERE `family_pairs`.`man_id`=`persons`.`id`
			OR `family_pairs`.`woman_id`=`persons`.`id`)");

	$output['persons'] = $sth->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
