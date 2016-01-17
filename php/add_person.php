<?php
/*
	Script adds new person

	Input: name, sex and parents pair id (optional)
*/

require_once "common.php";

define("MAX_NAME_LENGTH", 50);

if (!isset($_GET['name'], $_GET['sex']) || !in_array($_GET['sex'], ["man", "woman"])) {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

if (strlen($_GET['name']) > MAX_NAME_LENGTH) {
	$_GET['name'] = substr($_GET['name'], 0, MAX_NAME_LENGTH);
}

$output = [];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$sth = $dbFamilies->prepare("INSERT INTO `persons` (`name`, `sex`, `parents_pair_id`)
		VALUES (:name, :sex ,:parents_pair_id)");

	if (isset($_GET['parents_pair_id'])) {
		// Checking parents pair existence

		$sql = "SELECT 1 FROM `family_pairs` WHERE `id` = ".intval($_GET['parents_pair_id']);

		if ($dbFamilies->query($sql)->rowCount() === 1) {
			// Create person that has parents
			$sth->execute([
				'name'            => $_GET['name'],
				'sex'             => $_GET['sex'],
				'parents_pair_id' => $_GET['parents_pair_id']
			]);
		} else {
			// Return error if parents id is invalid
			$output['error'] = "parents not found";
		}
	} else {
		// Create person without parents
		$sth->execute([
			'name'            => $_GET['name'],
			'sex'             => $_GET['sex'],
			'parents_pair_id' => null
		]);
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
