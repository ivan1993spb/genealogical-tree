<?php

require_once "common.php";

if (!isset($_GET['man_id'], $_GET['woman_id'])) {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

$output = [];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	$sql =
		"SELECT
			`mans`.`parents_pair_id` as `man_parents_pair_id`,
			`man_parents`.`lft` AS `man_parents_lft`,
			`man_parents`.`rgt` AS `man_parents_rgt`,
			`womans`.`parents_pair_id` as `woman_parents_pair_id`,
			`woman_parents`.`lft` AS `woman_parents_lft`,
			`woman_parents`.`rgt` AS `woman_parents_rgt`
		FROM
			`mans` LEFT JOIN
			`family_pairs` as `man_parents` ON `mans`.`parents_pair_id`=`man_parents`.`id`,
			`womans` LEFT JOIN
			`family_pairs` as `woman_parents` ON `womans`.`parents_pair_id`=`woman_parents`.`id`
		WHERE
			`mans`.`id`=:man_id AND
			`womans`.`id`=:woman_id";

	$sth = $dbFamilies->prepare($sql);

	$sth->execute([
		'man_id'   => $_GET['man_id'],
		'woman_id' => $_GET['woman_id']
	]);

	if ($sth->rowCount() === 1) {
		$parentsPairs = $sth->fetch(PDO::FETCH_ASSOC);

		if ($parentsPairs['man_parents_pair_id'] !== $parentsPairs['woman_parents_pair_id'] ||
			(empty($parentsPairs['man_parents_pair_id']) && empty($parentsPairs['woman_parents_pair_id']))) {

			$sql = "INSERT INTO `family_pairs`(`man_id`, `woman_id`, `lft`, `rgt`)
				VALUES (:man_id, :woman_id, :lft, :rgt)";

			$params = [
				'man_id'   => $_GET['man_id'],
				'woman_id' => $_GET['woman_id']
			];

			if (!empty($parentsPairs['man_parents_pair_id']) && !empty($parentsPairs['woman_parents_pair_id'])) {
				// if woman and man have parents
				$params['lft'] = min($parentsPairs['man_parents_lft'], $parentsPairs['woman_parents_lft']);
				$params['rgt'] = max($parentsPairs['man_parents_rgt'], $parentsPairs['woman_parents_rgt']);

			} elseif (!empty($parentsPairs['man_parents_pair_id']) && empty($parentsPairs['woman_parents_pair_id'])) {
				// if man has parents and woman has not parents
				$params['lft'] = $parentsPairs['man_parents_lft'];
				$params['rgt'] = $parentsPairs['man_parents_rgt'];

			} elseif (empty($parentsPairs['man_parents_pair_id']) && !empty($parentsPairs['woman_parents_pair_id'])) {
				// if man has not parents and woman has parents
				$params['lft'] = $parentsPairs['woman_parents_lft'];
				$params['rgt'] = $parentsPairs['woman_parents_rgt'];

			} else {
				// if man has not parents and woman has not parents
				$sql = "INSERT INTO `family_pairs`(`man_id`, `woman_id`, `lft`, `rgt`)
					SELECT :man_id, :woman_id, COALESCE(MAX(`rgt`)+1, 1), COALESCE(MAX(`rgt`)+2, 2)
						FROM `family_pairs`";
			}

			$sth = $dbFamilies->prepare($sql);
			if ($sth->execute($params) === FALSE) {
				$output['error'] = "cannot create family pair: man or woman is already married";
			}
		} else {
			$output['error'] = "cannot marry brother and sister";
		}
	} else {
		$output['error'] = "passed invalid man id or woman id";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
