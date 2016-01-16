<?php

require_once "common.php";

define("MAX_NAME_LENGTH", 50);

if (!isset($_GET['name'], $_GET['sex'])) {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

// DB table name for mans or womans
$table = "";
if ($_GET['sex'] === "man") {
	$table = TABLE_MANS;
} elseif ($_GET['sex'] === "woman") {
	$table = TABLE_WOMANS;
} else {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

if (strlen($_GET['name']) > MAX_NAME_LENGTH) {
	$_GET['name'] = substr($_GET['name'], 0, MAX_NAME_LENGTH);
}

$output = [];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$sql = "INSERT INTO `$table` (`parents_pair_id`, `name`) VALUES (:parents_pair_id, :name)";
	$sth = $dbFamilies->prepare($sql);

	if (isset($_GET['parents_pair_id'])) {
		$sql = "SELECT 1 FROM `family_pairs` WHERE `id` = ".intval($_GET['parents_pair_id']);

		if ($dbFamilies->query($sql)->rowCount() === 1) {
			// Create person that has parents
			$sth->execute([
				'parents_pair_id' => $_GET['parents_pair_id'],
				'name'            => $_GET['name']
			]);
		} else {
			// Return error if parents id is invalid
			$output['error'] = "parents not found";
		}
	} else {
		// Create person without parents
		$sth->execute([
			'parents_pair_id' => null,
			'name'            => $_GET['name']
		]);
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
