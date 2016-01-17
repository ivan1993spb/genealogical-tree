<?php
/*
	Script returns JSON with list of persons

	Input: page number
*/

require_once "common.php";

define("PAGE_SIZE", 4);

$page = 0;
if (isset($_GET['page'])) {
	$page = intval($_GET['page']);
}

$output = ['page' => $page];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$pageCount = ceil($dbFamilies->query("SELECT 1 FROM `persons`")->rowCount() / PAGE_SIZE);
	$output['page_count'] = $pageCount;

	if ($page < $pageCount) {
		$sql = sprintf("SELECT `id`, `name`, `sex`, (`parents_pair_id` IS NOT NULL) AS `has_parents`
			FROM `persons` LIMIT %d, %d", $page*PAGE_SIZE, PAGE_SIZE);
		$output['persons'] = $dbFamilies->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	} elseif ($pageCount === 0) {
		$output['persons'] = [];
	} else {
		$output['error'] = "passed invalid page";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
