<?php

require_once "common.php";

$output = [];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	// Getting all family pairs
	$sth = $dbFamilies->query("SELECT
		`family_pairs`.`id`, `mans`.`name` AS `man_name`, `womans`.`name` AS `woman_name`
		FROM `mans`, `womans`, `family_pairs`
		WHERE `mans`.`id`=`family_pairs`.`man_id` AND `womans`.`id`=`family_pairs`.`woman_id`");

	$output['persons'] = $sth->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
