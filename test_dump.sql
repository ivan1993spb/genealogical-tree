
USE families;

INSERT INTO `family_pairs` (`id`, `man_id`, `woman_id`, `lft`, `rgt`) VALUES
(1, 1, 2, 1, 2),
(2, 3, 4, 3, 4),
(3, 5, 6, 5, 6),
(4, 8, 7, 7, 8),
(5, 9, 10, 1, 4),
(6, 11, 12, 5, 8),
(7, 13, 14, 1, 8),
(8, 17, 16, 5, 8),
(9, 19, 20, 1, 6);

INSERT INTO `persons` (`id`, `name`, `sex`, `parents_pair_id`) VALUES
(1, 'Иван', 'man', NULL),
(2, 'Вика', 'woman', NULL),
(3, 'Андрей', 'man', NULL),
(4, 'Оля', 'woman', NULL),
(5, 'Никита', 'man', NULL),
(6, 'Варя', 'woman', NULL),
(7, 'Сима', 'woman', NULL),
(8, 'Саша', 'man', NULL),
(9, 'Даня', 'man', 1),
(10, 'Юля', 'woman', 2),
(11, 'Женя', 'man', 3),
(12, 'Даша', 'woman', 4),
(13, 'Гоша', 'man', 5),
(14, 'Маша', 'woman', 6),
(15, 'Макс', 'man', 7),
(16, 'Ира', 'woman', 6),
(17, 'Гриша', 'man', NULL),
(18, 'Люда', 'woman', 8),
(19, 'Сережа', 'man', 1),
(20, 'Аня', 'woman', 3),
(21, 'Костя', 'man', 9);
