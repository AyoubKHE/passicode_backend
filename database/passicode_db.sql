-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           11.5.2-MariaDB - mariadb.org binary distribution
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage des données de la table passicode.admins : ~0 rows (environ)
DELETE FROM `admins`;
INSERT INTO `admins` (`id`, `user_id`) VALUES
	(1, 1);

-- Listage des données de la table passicode.categories : ~17 rows (environ)
DELETE FROM `categories`;
INSERT INTO `categories` (`id`, `name`, `description`, `price`, `discount`, `quantity`, `image_path`, `is_active`, `is_leaf_category`, `parent_id`, `created_at`, `updated_at`) VALUES
	(1, 'Gaming', 'Gaming', NULL, NULL, 0, 'categories/id_1/H1K2PHWvfuOeAHEChgVI6C1koWIlKoj9rYztCsj6.jpg', 1, 0, NULL, '2025-08-25 18:48:45', '2025-08-25 18:50:00'),
	(2, 'Apex Legends', 'Apex Legends', NULL, NULL, 0, 'categories/id_2/nPjhH5wDPuKmycpNrOcXyHmA94PRnM9ZTWjexf9e.jpg', 1, 0, 1, '2025-08-25 18:50:00', '2025-08-25 18:51:37'),
	(3, 'Apex Legends 1000', 'Obtenez 1000 Apex Coins pour enrichir votre expérience sur Apex Legends. Utilisez vos pièces pour acheter de nouveaux skins légendaires, débloquer des personnages, personnaliser vos armes et profiter du Battle Pass. Donnez un coup de boost à votre jeu et démarquez-vous dans l’arène avec du contenu exclusif.', 3675.00, NULL, 0, 'categories/id_3/uPm1WhQ6rwqOpGsVWRG1Pxe2Ie26dCBRlH2zHrIe.png', 1, 1, 2, '2025-08-25 18:51:37', NULL),
	(4, 'Apex Legends 2150', 'Obtenez 2150 Apex Coins pour enrichir votre expérience sur Apex Legends. Utilisez vos pièces pour acheter de nouveaux skins légendaires, débloquer des personnages, personnaliser vos armes et profiter du Battle Pass. Donnez un coup de boost à votre jeu et démarquez-vous dans l’arène avec du contenu exclusif.', 6415.00, NULL, 0, 'categories/id_4/wnozSaUHs4GrkfzHDYX8EkeOsnsk2RUXtSBeiVR1.png', 1, 1, 2, '2025-08-25 18:52:35', NULL),
	(5, 'Apex Legends 4350', 'Obtenez 4350 Apex Coins pour enrichir votre expérience sur Apex Legends. Utilisez vos pièces pour acheter de nouveaux skins légendaires, débloquer des personnages, personnaliser vos armes et profiter du Battle Pass. Donnez un coup de boost à votre jeu et démarquez-vous dans l’arène avec du contenu exclusif.', 12030.00, NULL, 0, 'categories/id_5/bXM4zt2wYTsCjun52PqizOYQEdytExWmOfIY1q3C.png', 1, 1, 2, '2025-08-25 18:53:12', NULL),
	(6, 'Apex Legends 6700', 'Obtenez 6700 Apex Coins pour enrichir votre expérience sur Apex Legends. Utilisez vos pièces pour acheter de nouveaux skins légendaires, débloquer des personnages, personnaliser vos armes et profiter du Battle Pass. Donnez un coup de boost à votre jeu et démarquez-vous dans l’arène avec du contenu exclusif.', 18580.00, NULL, 0, 'categories/id_6/UYrRvBbbUc4ZRvoL1ybFrTnPHbZAgKGLzj7gzge2.png', 1, 1, 2, '2025-08-25 18:53:36', NULL),
	(7, 'Blizzard', 'Blizzard', NULL, NULL, 0, 'categories/id_7/vf4e7Fa2qN2iuReJoP1TE3oG0TnWN1tzpyxRL9Yj.png', 1, 0, 1, '2025-08-25 18:54:14', '2025-08-25 18:54:39'),
	(8, 'Blizzard Europe', 'Blizzard Europe', NULL, NULL, 0, 'categories/id_8/UUlWveW6n8VbUwY7hq1dqs6c9REwtksqzjX7p63a.png', 1, 0, 7, '2025-08-25 18:54:39', '2025-08-25 18:56:14'),
	(9, 'Blizzard Europe 20€', 'Offrez-vous 20€ de crédit Battle.net à utiliser sur la boutique officielle Blizzard Europe. Achetez vos jeux préférés (World of Warcraft, Overwatch, Diablo, Hearthstone, Call of Duty et bien plus), profitez de contenus additionnels, extensions, objets en jeu et montures exclusives. Idéal pour recharger rapidement votre compte et accéder à tout l’univers Blizzard.', 6500.00, NULL, 0, 'categories/id_9/zM5vQcrtLdgb3RxbbafmYNG5St7pjsoS6gS3JGpS.png', 1, 1, 8, '2025-08-25 18:56:13', NULL),
	(10, 'Blizzard Europe 50€', 'Offrez-vous 50€ de crédit Battle.net à utiliser sur la boutique officielle Blizzard Europe. Achetez vos jeux préférés (World of Warcraft, Overwatch, Diablo, Hearthstone, Call of Duty et bien plus), profitez de contenus additionnels, extensions, objets en jeu et montures exclusives. Idéal pour recharger rapidement votre compte et accéder à tout l’univers Blizzard.', 16200.00, NULL, 0, 'categories/id_10/Rtnn1HdcrdQsc8wZcdFJVkqxdFrPlUKG2vDcDhMk.png', 1, 1, 8, '2025-08-25 18:56:28', NULL),
	(11, 'Blizzard Usa', 'Blizzard Usa', NULL, NULL, 0, 'categories/id_11/jjJKj4LB7KIL5JF2f6luG0vc8W7maLAfJkbLeUwt.png', 1, 0, 7, '2025-08-25 18:57:13', '2025-08-25 18:58:19'),
	(12, 'Blizzard Usa 20$', 'Obtenez 20$ de crédit Battle.net à utiliser sur la boutique officielle Blizzard USA. Achetez vos jeux favoris (World of Warcraft, Overwatch, Diablo, Hearthstone, Call of Duty et plus encore), profitez d’extensions, de contenus additionnels, d’objets en jeu et de montures exclusives. Rechargez facilement votre compte et plongez dans tout l’univers Blizzard.', 6195.00, NULL, 0, 'categories/id_12/zMPhIxkl4Cb3WVyC5X53mkpjdJnhxvR8SvbqC9sa.png', 1, 1, 11, '2025-08-25 18:58:19', NULL),
	(13, 'Blizzard Usa 50$', 'Obtenez 50$ de crédit Battle.net à utiliser sur la boutique officielle Blizzard USA. Achetez vos jeux favoris (World of Warcraft, Overwatch, Diablo, Hearthstone, Call of Duty et plus encore), profitez d’extensions, de contenus additionnels, d’objets en jeu et de montures exclusives. Rechargez facilement votre compte et plongez dans tout l’univers Blizzard.', 14820.00, NULL, 0, 'categories/id_13/ieDSWreoRwOW1pPxwMYtSQxLna6y0BlZ5w9t9qoN.png', 1, 1, 11, '2025-08-25 18:58:41', NULL),
	(14, 'EA Games', 'EA Games', NULL, NULL, 0, 'categories/id_14/V5BK8QE9CK9YN7iqDA9MuxsHJc8aMjj4y5shx0wF.jpg', 1, 0, 1, '2025-08-25 19:00:35', '2025-08-25 19:01:02'),
	(15, 'EA Games Usa', 'EA Games Usa', NULL, NULL, 0, 'categories/id_15/7bqIy59wDRs6BWGp5VGnlv025MsEs1PMI5ZtMjLE.png', 1, 0, 14, '2025-08-25 19:01:02', '2025-08-25 19:02:34'),
	(16, 'EA Games Usa 15$', 'Profitez de 15$ de crédit EA Games USA pour enrichir votre expérience de jeu. Utilisez ce solde sur la boutique officielle EA afin d’acheter vos titres préférés (FIFA, The Sims, Battlefield, Apex Legends et bien d’autres), débloquer des contenus additionnels, packs et extensions. Rechargez facilement votre compte EA et accédez à tout l’univers EA Games.', 3935.00, NULL, 0, 'categories/id_16/qIH0Xs8UvjZzFCVN5NzO6OYOmIgV7V3nf7EMW2T1.png', 1, 1, 15, '2025-08-25 19:02:34', NULL),
	(17, 'EA Games Usa 25$', 'Profitez de 25$ de crédit EA Games USA pour enrichir votre expérience de jeu. Utilisez ce solde sur la boutique officielle EA afin d’acheter vos titres préférés (FIFA, The Sims, Battlefield, Apex Legends et bien d’autres), débloquer des contenus additionnels, packs et extensions. Rechargez facilement votre compte EA et accédez à tout l’univers EA Games.', 6440.00, NULL, 0, 'categories/id_17/WNbJdfHDWjrwR2HZ8UgIT5fkQoMBjs3wTGRoDOAt.png', 1, 1, 15, '2025-08-25 19:02:49', NULL);

-- Listage des données de la table passicode.chargilypayments : ~0 rows (environ)
DELETE FROM `chargilypayments`;

-- Listage des données de la table passicode.clients : ~0 rows (environ)
DELETE FROM `clients`;
INSERT INTO `clients` (`id`, `user_id`) VALUES
	(1, 2);

-- Listage des données de la table passicode.failedquantityrequests : ~0 rows (environ)
DELETE FROM `failedquantityrequests`;

-- Listage des données de la table passicode.migrations : ~11 rows (environ)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(2, '2025_02_20_150000_create_users_table', 1),
	(3, '2025_02_24_112930_create_admins_table', 1),
	(4, '2025_02_24_113030_create_clients_table', 1),
	(5, '2025_03_13_171646_create_categories_table', 1),
	(6, '2025_03_20_002430_create_products_table', 1),
	(7, '2025_05_21_124043_create_orders_table', 1),
	(8, '2025_05_21_124514_create_ordersItems_table', 1),
	(9, '2025_05_21_125635_create_chargilyPayments_table', 1),
	(10, '2025_06_30_105843_create_failedQuantityRequests_table', 1),
	(11, '2025_07_04_114035_create_settings_table', 1);

-- Listage des données de la table passicode.orders : ~0 rows (environ)
DELETE FROM `orders`;

-- Listage des données de la table passicode.ordersitems : ~0 rows (environ)
DELETE FROM `ordersitems`;

-- Listage des données de la table passicode.personal_access_tokens : ~0 rows (environ)
DELETE FROM `personal_access_tokens`;

-- Listage des données de la table passicode.products : ~0 rows (environ)
DELETE FROM `products`;

-- Listage des données de la table passicode.settings : ~0 rows (environ)
DELETE FROM `settings`;
INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'is_admin_available_for_backorder', 'false', '2025-07-27 13:14:01', '2025-08-02 20:51:23');

-- Listage des données de la table passicode.users : ~2 rows (environ)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `image_url`, `role`, `is_active`, `refresh_token`, `last_login`, `created_at`, `updated_at`) VALUES
	(1, 'Passicode', 'Super Admin', 'passicode.dz@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocJlnvTxYOyFlmdIGhCiQl5IZ6O4mKR7eTRmI9JQ9OQaKp-4Yfo=s360-c-no', 'Super Admin', 1, '$2y$10$OZxR2Vzi53U8StYLuJoLX..eajJ5LkNr2og8uTIEiB9J0hUzwvtFO', '2025-08-25 17:57:04', '2025-08-04 13:20:31', NULL),
	(2, 'Ayoub', 'Kheyar', 'ayoub.kheyar06@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocKL1cZrAafoxBI-br3KPFNnOzL5K0tjo4YGf6JrLLTtblyr=s360-c-no', 'Client', 1, NULL, '2025-08-12 13:00:20', '2025-08-04 13:21:37', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
