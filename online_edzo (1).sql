-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Dec 02. 13:00
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `online_edzo`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `ai_chat_messages`
--

CREATE TABLE `ai_chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('user','assistant') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `ai_chat_messages`
--

INSERT INTO `ai_chat_messages` (`id`, `user_id`, `role`, `message`, `created_at`) VALUES
(3, 2, 'user', 'Mennyit kell innom naponta?', '2025-11-04 11:46:31'),
(4, 2, 'assistant', 'Súly meghatározásához: 🏋️\n\n1. Kezdd könnyebben!\n2. Ha 12 ismétlés könnyen megy → nehezebb\n3. Ha 8-nál kevesebb megy tisztán → könnyebb\n4. Célzóna: 8-12 ismétlés izomépítéshez\n\nMelyik gyakorlatról van szó?', '2025-11-04 11:46:31');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `body_measurements`
--

CREATE TABLE `body_measurements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `measurement_date` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `body_fat_percentage` decimal(4,2) DEFAULT NULL,
  `chest` decimal(5,2) DEFAULT NULL,
  `arm` decimal(5,2) DEFAULT NULL,
  `waist` decimal(5,2) DEFAULT NULL,
  `hips` decimal(5,2) DEFAULT NULL,
  `thigh` decimal(5,2) DEFAULT NULL,
  `calf` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `difficulty` enum('könnyű','közepes','nehéz') DEFAULT 'közepes',
  `target_value` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `reward_points` int(11) DEFAULT 0,
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `description`, `category`, `difficulty`, `target_value`, `unit`, `duration_days`, `reward_points`, `status`, `created_at`) VALUES
(1, '30 napos fekvőtámasz kihívás', 'Végezz el összesen 1000 fekvőtámaszt 30 nap alatt!', 'Erő', 'közepes', 1000, 'db', 30, 100, 'active', '2025-11-04 11:31:37'),
(2, 'Heti 5 edzés', 'Edz legalább 5 alkalommal egy héten!', 'Konzisztencia', 'közepes', 5, 'edzés', 7, 50, 'active', '2025-11-04 11:31:37'),
(3, '100 km futás', 'Fuss összesen 100 km-t 30 nap alatt!', 'Kardiό', 'nehéz', 100, 'km', 30, 200, 'active', '2025-11-04 11:31:37'),
(4, '7 napos plank kihívás', 'Tarts plank pozíciót összesen 30 percig 7 nap alatt!', 'Core', 'könnyű', 30, 'perc', 7, 75, 'active', '2025-11-04 11:31:37'),
(5, 'Guggolás mester', 'Végezz 500 guggolást egy hét alatt!', 'Láb', 'közepes', 500, 'db', 7, 80, 'active', '2025-11-04 11:31:37'),
(6, '早起 madár', 'Kelj fel 7 napig egymás után 6 óra előtt!', 'Életmód', 'könnyű', 7, 'nap', 7, 50, 'active', '2025-11-04 11:31:37'),
(7, 'Víz kihívás', 'Igyál napi 3 liter vizet 14 napig!', 'Egészség', 'könnyű', 42, 'liter', 14, 60, 'active', '2025-11-04 11:31:37'),
(8, 'Bicepsz bomba', 'Emelj összesen 10 tonna súlyt bicepsz gyakorlatokkal egy hónap alatt!', 'Erő', 'nehéz', 10000, 'kg', 30, 150, 'active', '2025-11-04 11:31:37'),
(9, '30 napos has', 'Végezz hasgyakorlatokat 30 napig megszakítás nélkül!', 'Core', 'közepes', 30, 'nap', 30, 120, 'active', '2025-11-04 11:31:37'),
(10, 'Kezdő maraton előkészítő', 'Fuss összesen 50 km 21 nap alatt!', 'Kardiό', 'közepes', 50, 'km', 21, 100, 'active', '2025-11-04 11:31:37');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `muscle_group` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `tips` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `nutrition_log`
--

CREATE TABLE `nutrition_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `meal_type` varchar(50) DEFAULT NULL,
  `food_name` varchar(255) DEFAULT NULL,
  `calories` decimal(10,2) DEFAULT NULL,
  `protein` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `nutrition_log`
--

INSERT INTO `nutrition_log` (`id`, `user_id`, `log_date`, `meal_type`, `food_name`, `calories`, `protein`, `created_at`) VALUES
(1, 2, '2025-10-25', 'Reggeli', '5 fott tojas 80g zabbal', 450.00, 62.00, '2025-10-25 14:55:53'),
(2, 6, '0000-00-00', NULL, '5 fott tojas 80g zabbal', 400.00, 45.00, '2025-10-25 15:31:12');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `progress_photos`
--

CREATE TABLE `progress_photos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `photo_date` date NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_type` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_type`, `status`, `start_date`, `end_date`, `payment_method`, `created_at`) VALUES
(1, 2, 'free', 'cancelled', '2025-10-25', NULL, NULL, '2025-10-25 14:05:17'),
(2, 3, 'free', 'active', '2025-10-25', NULL, NULL, '2025-10-25 14:05:17'),
(3, 4, 'free', 'active', '2025-10-25', NULL, NULL, '2025-10-25 14:05:17'),
(4, 1, 'free', 'active', '2025-10-25', NULL, NULL, '2025-10-25 14:05:17'),
(8, 2, 'premium', 'cancelled', '2025-10-25', '2025-11-25', 'card', '2025-10-25 14:33:34'),
(9, 5, 'premium', 'active', '2025-10-25', '2025-11-25', 'card', '2025-10-25 15:13:26'),
(10, 6, 'premium', 'active', '2025-10-25', '2025-11-25', 'card', '2025-10-25 15:30:22'),
(11, 7, 'premium', 'active', '2025-10-26', '2025-11-26', 'card', '2025-10-25 22:06:24'),
(12, 8, 'premium', 'active', '2025-10-26', '2026-10-26', 'card', '2025-10-26 17:46:35'),
(13, 10, 'premium', 'active', '2025-10-27', '2026-10-27', 'card', '2025-10-27 21:07:31'),
(14, 11, 'premium', 'active', '2025-10-28', '2026-10-28', 'card', '2025-10-28 11:10:24'),
(15, 12, 'premium', 'active', '2025-10-29', '2026-10-29', 'card', '2025-10-29 18:12:49'),
(16, 13, 'premium', 'active', '2025-11-04', '2025-12-04', 'card', '2025-11-04 11:08:48'),
(17, 2, 'premium', 'cancelled', '2025-11-04', '2025-12-04', 'card', '2025-11-04 11:43:46'),
(18, 2, 'premium', 'cancelled', '2025-11-19', '2025-12-19', 'card', '2025-11-19 17:55:36'),
(19, 2, 'premium', 'cancelled', '2025-12-02', '2026-01-02', 'card', '2025-12-02 11:42:31'),
(20, 2, 'premium', 'active', '2025-12-02', '2026-01-02', 'card', '2025-12-02 11:47:30');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `status` enum('open','answered','closed') DEFAULT 'open',
  `user_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `answered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `category`, `question`, `answer`, `status`, `user_read`, `created_at`, `answered_at`) VALUES
(1, 2, 'edzésterv', 'halooo', NULL, 'open', 0, '2025-11-18 11:38:21', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `goal` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `daily_calorie_goal` int(11) DEFAULT 2000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `age`, `weight`, `goal`, `created_at`, `daily_calorie_goal`) VALUES
(1, 'asdasd', 'lakatoskevin3@gmail.com', '$2y$10$Ty2YTFOQd7bTyxmmiXck/ODyVY8dw4Y7axmplV4ZjPdqS/ny9oKWu', 18, 80, 'izom', '2025-10-14 11:00:18', 2000),
(2, 'bturbo', 'banumate@gmail.com', '$2y$10$m5D3YGDKwd6xr7t0kLe6JOWdlqZy0J6zSvazZwQyYKKC.lnS0Y.W2', 18, 80, 'fogyas', '2025-10-14 11:02:43', 2000),
(3, 'bela', 'gal.bela@gmail.com', '$2y$10$foDy/3X/LVTbxrv00lmSFuvIN9kvoZoOXxdH/N97m50XBsuiC1uki', 20, 121, 'fogyas', '2025-10-14 14:05:12', 2000),
(4, 'ivan', 'ivan2@gmail.com', '$2y$10$14bNUi.d2AvmhPUD8fU34O1WAEPIzObReX6T.j6b8vvGODQ1nsi7a', 19, 65, 'izom', '2025-10-14 15:11:16', 2000),
(5, 'Kevin776', 'lakatoskevin2@gmail.com', '$2y$10$FtaK/IxgI0Fp93m3uWGqjOb8/bP6CvSebEm8J8lMLm9BN9vCl2Jt.', NULL, NULL, NULL, '2025-10-25 15:11:59', 2000),
(6, 'asd', 'asd@gmail.com', '$2y$10$CIRprVYz53Wf.xOULimZfe6920qJbiJnesF0SWR0b5arkWLprgPjm', NULL, NULL, NULL, '2025-10-25 15:25:30', 2000),
(7, 'fitkevin', 'kevin@gmail.com', '$2y$10$SPlvCZZ18gBvxBQsuleqxOxeFe/dQgoX3Yr4u20SOKC7Kb2Sf4zuS', NULL, NULL, NULL, '2025-10-25 21:20:10', 2000),
(8, 'hasd', 'asd12@gmail.com', '$2y$10$P5Ue6C1o9OBRkeo82h8mfuZ80t1LU1ls0Zha2393dj5RVui0iwTS2', NULL, NULL, NULL, '2025-10-26 17:44:03', 2000),
(9, 'felisten', 'laki@gmail.com', '$2y$10$KlgKXM2fXoPiIXWXIptg9eVlKbnKEQjYpL/ZaPFRSqwUNBpZ76UAa', NULL, NULL, NULL, '2025-10-27 21:01:42', 2000),
(10, '123', '1234@gmail.com', '$2y$10$pJdr/aJt2ecr96lvaA06aOcjj..Dv62hQpJda7njDuzD2mebPChyO', NULL, NULL, NULL, '2025-10-27 21:06:07', 2000),
(11, 'dariusz', 'dariusz@gmail.com', '$2y$10$sJGGL78QLDRYTCymR0UIs.6ihwOVBuCim9BZPA/HZHHSiQGhAF.P2', NULL, NULL, NULL, '2025-10-28 11:07:31', 2000),
(12, 'dewtaste', 'dewtaste@gmail.com', '$2y$10$QsFE1ZhM1GwLwEkb1PmeX.LsVaEqQwroFN3/ztND9Up3oJUYQA7yS', NULL, NULL, NULL, '2025-10-29 18:09:16', 2000),
(13, 'pista', 'pista@gmail.com', '$2y$10$codbdKhtEUc8.jAj3mCjqOldJbK//XXI41axTFlrSXRhXbwyTOBNu', NULL, NULL, NULL, '2025-11-04 11:05:56', 2000);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_challenges`
--

CREATE TABLE `user_challenges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `status` enum('active','completed','failed') DEFAULT 'active',
  `joined_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `user_challenges`
--

INSERT INTO `user_challenges` (`id`, `user_id`, `challenge_id`, `progress`, `status`, `joined_date`, `completed_date`, `created_at`, `updated_at`) VALUES
(1, 2, 4, 0, 'active', '2025-11-04', NULL, '2025-11-04 11:36:53', '2025-11-04 11:36:53');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_goals`
--

CREATE TABLE `user_goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `goal_type` varchar(50) NOT NULL,
  `target_value` decimal(10,2) NOT NULL,
  `current_value` decimal(10,2) DEFAULT 0.00,
  `target_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `user_goals`
--

INSERT INTO `user_goals` (`id`, `user_id`, `title`, `goal_type`, `target_value`, `current_value`, `target_date`, `description`, `status`, `completed_date`, `created_at`, `updated_at`) VALUES
(1, 2, 'elérni a 100kg fekvenyomás', 'Súly', 100.00, 0.00, '0000-00-00', '', 'active', NULL, '2025-11-04 11:36:36', '2025-11-04 11:36:36');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `height` int(11) DEFAULT NULL COMMENT 'cm-ben',
  `current_weight` decimal(5,2) DEFAULT NULL COMMENT 'kg-ban',
  `goal` varchar(100) DEFAULT NULL COMMENT 'fogyás, izomnövelés, erősödés, állóképesség',
  `fitness_level` varchar(50) DEFAULT NULL COMMENT 'kezdő, középhaladó, haladó',
  `weekly_sessions` int(11) DEFAULT NULL COMMENT 'heti edzések száma',
  `restrictions` text DEFAULT NULL COMMENT 'sérülések, korlátozások',
  `onboarding_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `age`, `gender`, `height`, `current_weight`, `goal`, `fitness_level`, `weekly_sessions`, `restrictions`, `onboarding_completed`, `created_at`, `updated_at`) VALUES
(1, 7, 19, 'férfi', 175, 91.00, 'izomnövelés', 'haladó', 5, 'visszatérő hátfájás', 1, '2025-10-25 21:21:43', '2025-10-25 21:21:43'),
(2, 8, 19, 'férfi', 167, 90.00, 'izomnövelés', 'haladó', 3, '', 1, '2025-10-26 17:44:59', '2025-10-26 17:44:59'),
(3, 10, 19, 'férfi', 175, 80.00, 'izomnövelés', 'középhaladó', 4, '', 1, '2025-10-27 21:06:35', '2025-10-27 21:06:35'),
(4, 11, 19, 'férfi', 175, 80.00, 'izomnövelés', 'középhaladó', 4, '', 1, '2025-10-28 11:08:31', '2025-10-28 11:08:31'),
(5, 12, 19, 'férfi', 190, 120.00, 'fogyás', 'középhaladó', 4, '', 1, '2025-10-29 18:10:27', '2025-10-29 18:10:27'),
(6, 13, 30, 'férfi', 160, 70.00, 'fogyás', 'haladó', 5, '', 1, '2025-11-04 11:06:40', '2025-11-04 11:06:40'),
(7, 2, 19, 'férfi', 175, 80.00, 'fogyás', 'középhaladó', 3, 'térdprobléma', 1, '2025-12-02 11:41:20', '2025-12-02 11:41:20');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `workout_log`
--

CREATE TABLE `workout_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `muscle_group` varchar(50) DEFAULT NULL,
  `exercise` varchar(100) DEFAULT NULL,
  `sets` int(11) DEFAULT NULL,
  `reps` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `workout_log`
--

INSERT INTO `workout_log` (`id`, `user_id`, `date`, `muscle_group`, `exercise`, `sets`, `reps`, `weight`, `note`) VALUES
(1, 2, '2025-10-17', 'Mell', 'fekvenyomás', 12, 7, 60.00, 'nehéz volt...'),
(2, 2, '2025-10-17', 'Mell', '', 3, 13, 90.00, ''),
(3, 2, '2025-10-17', 'Mell', 'tárogatás', 3, 12, 14.00, 'utolsót már nem birtam'),
(4, 2, '2025-10-21', 'Mell', 'fekvenyomás', 6, 17, 100.00, 'konnyen ment '),
(5, 2, '2025-10-21', 'Mell', '', 0, 0, 0.00, ''),
(6, 2, '2025-10-24', 'Mell', '', 0, 0, 0.00, ''),
(7, 6, '2025-10-25', 'Láb', 'guggolás', 3, 10, 50.00, 'nehéz volt');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- A tábla indexei `body_measurements`
--
ALTER TABLE `body_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `muscle_group` (`muscle_group`);
ALTER TABLE `exercises` ADD FULLTEXT KEY `ft_name_description` (`name`,`description`);

--
-- A tábla indexei `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `nutrition_log`
--
ALTER TABLE `nutrition_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `progress_photos`
--
ALTER TABLE `progress_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_status` (`user_id`,`status`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A tábla indexei `user_challenges`
--
ALTER TABLE `user_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_challenge` (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- A tábla indexei `user_goals`
--
ALTER TABLE `user_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `workout_log`
--
ALTER TABLE `workout_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `body_measurements`
--
ALTER TABLE `body_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `nutrition_log`
--
ALTER TABLE `nutrition_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `progress_photos`
--
ALTER TABLE `progress_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT a táblához `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT a táblához `user_challenges`
--
ALTER TABLE `user_challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `user_goals`
--
ALTER TABLE `user_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a táblához `workout_log`
--
ALTER TABLE `workout_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  ADD CONSTRAINT `ai_chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `body_measurements`
--
ALTER TABLE `body_measurements`
  ADD CONSTRAINT `body_measurements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `nutrition_log`
--
ALTER TABLE `nutrition_log`
  ADD CONSTRAINT `nutrition_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Megkötések a táblához `plans`
--
ALTER TABLE `plans`
  ADD CONSTRAINT `plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `progress_photos`
--
ALTER TABLE `progress_photos`
  ADD CONSTRAINT `progress_photos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `user_challenges`
--
ALTER TABLE `user_challenges`
  ADD CONSTRAINT `user_challenges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_challenges_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `user_goals`
--
ALTER TABLE `user_goals`
  ADD CONSTRAINT `user_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `workout_log`
--
ALTER TABLE `workout_log`
  ADD CONSTRAINT `workout_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
