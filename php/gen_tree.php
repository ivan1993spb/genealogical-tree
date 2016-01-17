<?php
/*
	Script returns JSON lists of nodes for genealogical tree for a person
	Nodes are persons and family pairs

	Input: person id
*/

require_once "common.php";

if (!isset($_GET['id'])) {
	header('HTTP/1.0 400 Bad Request', true, 400);
	exit;
}

$output = ['target_id' => $_GET['id']];

try {
	$dbFamilies = new PDO(DSN, DB_USR, DB_PWD);

	// Getting parents info: pair id, lft and rgt
	$sql = "SELECT `fps`.`id`, `fps`.`lft`, `fps`.`rgt`
			FROM `persons` AS `ps` LEFT JOIN `family_pairs` AS `fps`
			ON `fps`.`id`=`ps`.`parents_pair_id`
			WHERE `ps`.`id` = ".intval($_GET['id']);
	$sth = $dbFamilies->query($sql);

	if ($sth->rowCount() === 1) {

		$parentsInfo = $sth->fetch(PDO::FETCH_ASSOC);

		if (empty($parentsInfo['id'])) {
			// Person has not parents
			$output['error'] = "person has not parents";
		} else {
			// Person has parents

			// Getting family tree pairs
			$sth = $dbFamilies->prepare("SELECT `id`, `man_id`, `woman_id`
				FROM `family_pairs` WHERE `lft` BETWEEN ? AND ?");
			$sth->execute([$parentsInfo['lft'], $parentsInfo['rgt']]);
			$familyPairs = $sth->fetchAll(PDO::FETCH_ASSOC);

			// Getting all people from gen tree...

			$parentsPairsIds = []; // It is necessary to get children
			$personsIds      = []; // To get dads and moms

			foreach ($familyPairs as $family_pair) {
				array_push($parentsPairsIds, $family_pair['id']);
				array_push($personsIds, $family_pair['man_id'], $family_pair['woman_id']);
			}

			$parentsPairsIds = implode(", ", $parentsPairsIds);
			$personsIds      = implode(", ", $personsIds);

			$sql = "SELECT `id`, `name`, `sex`, `parents_pair_id` FROM `persons`
				WHERE `parents_pair_id` IN (%s) OR `id` IN (%s)";
			
			$sth = $dbFamilies->prepare(sprintf($sql, $parentsPairsIds, $personsIds));
			if ($sth->execute()) {
				$persons = $sth->fetchAll(PDO::FETCH_ASSOC);

				// Verify and clear tree...

				$personIdsTodo = [$_GET['id']];

				$personsIdsDone  = [];
				$trustedPairsIds = [];

				$output['persons']      = [];
				$output['family_pairs'] = [];

				while (count($personIdsTodo) > 0) {
					$person_id = array_shift($personIdsTodo);

					foreach ($persons as $person) {
						if ($person['id'] === $person_id) {
							array_push($output['persons'], $person);
							array_push($personsIdsDone, $person_id);

							foreach ($familyPairs as $pair) {
								if ($pair['id'] === $person['parents_pair_id']) {
									array_push($output['family_pairs'], $pair);
									// Pair is trusted so we can get their children
									array_push($trustedPairsIds, $person['parents_pair_id']);
									array_push($personIdsTodo, $pair['man_id'], $pair['woman_id']);

									break;
								}
							}

							break;
						}
					}
				}

				// Get children of trusted pairs
				foreach ($persons as $person) {
					// Push if person is child of one of the trusted pairs and was not added yet
					if (in_array($person['parents_pair_id'], $trustedPairsIds) &&
						!in_array($person['id'], $personsIdsDone)) {

						array_push($output['persons'], $person);
					}
				}
			} else {
				$output['error'] = "cannot get dads and moms";
			}
		}
	} else {
		$output['error'] = "passed invalid person id";
	}
} catch (PDOException $e) {
	$output['error'] = "cannot work with database";
}

$output['req_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

header("Content-Type: application/json; charset=utf-8");
echo json_encode($output);
