-- ПОЛЬЗОВАТЕЛИ
--  	STAGE: состояние пользователя в данный момент (check general.php)
-- 			0: Авторизация ?
-- 			1: В меню
-- 			2: В комнате (загрузка) ?
-- 			3: В комнате (не готов)
-- 			4: В комнате (готов)
-- 			
-- 		ROLE:
-- 			0: Пользователь
-- 			1: Модератор ?
-- 			2: Администратор
DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
	user_id 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	email 		VARCHAR(80) NOT NULL UNIQUE,
	passhash 	VARCHAR(70) NOT NULL,
	nick 		VARCHAR(70) NOT NULL UNIQUE,
	last_dt 	DATETIME,
	room_id 	INTEGER DEFAULT NULL REFERENCES rooms(room_id) ON DELETE SET NULL ON UPDATE CASCADE,
	stage 		TINYINT DEFAULT 1, 
	role 		TINYINT DEFAULT 0
);

DROP TABLE IF EXISTS ratings;
CREATE TABLE IF NOT EXISTS ratings (
	rating_id 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	user_id 	INTEGER REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
	total 		INTEGER DEFAULT 0,
	won 		INTEGER DEFAULT 0,
	CHECK(total >= 0 AND won >= 0 AND won <= total)
);
-- -- ТЕСТОВЫЙ ПОЛЬЗОВАТЕЛЬ:
-- INSERT INTO users (email, passhash, nick) VALUES 
-- ('thesourcecode.max@yandex.ru', '$2y$10$HwdRQY0PL4K0ePf0MPZxm.rBEwzInjbKTWljgis9JdJdr32pUbemK', 'Lucors');



-- МЕГА ГЛУПО!
	-- -- ТИПЫ НАСТРОЕК
	-- DROP TABLE IF EXISTS settings_types;

	-- CREATE TABLE IF NOT EXISTS settings_types (
	-- 	setting_type_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	-- 	name VARCHAR(50) NOT NULL UNIQUE,
	-- 	decryption VARCHAR(80) NOT NULL UNIQUE,
	-- 	input_type VARCHAR(30) NOT NULL DEFAULT 'text',
	-- 	default_value VARCHAR(80) NOT NULL
	-- );



	-- -- ЗНАЧЕНИЯ НАСТРОЕК
	-- DROP TABLE IF EXISTS settings_values;

	-- CREATE TABLE IF NOT EXISTS settings_values (
	-- 	setting_value_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	-- 	user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	-- 	setting_type_id INTEGER NOT NULL REFERENCES settings_types(setting_type_id) ON DELETE CASCADE ON UPDATE CASCADE,
	-- 	value VARCHAR(80) NOT NULL
	-- );
	
-- ПОЛЬЗОВАТЕЛЬСКИЕ НАСТРОЙКИ
DROP TABLE IF EXISTS users_settings;
CREATE TABLE IF NOT EXISTS users_settings (
	user_setting_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	user_id 		INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	allow_animation TINYINT(2) NOT NULL DEFAULT 1
);
-- INSERT INTO 
-- 	users_settings(user_id) 
-- VALUES
-- 	(1), (2), (3), (4), (5), (6), (7), (8), (13), (14), (15), (16);





-- ТЕМЫ ВОПРОСОВ
DROP TABLE IF EXISTS topics;
CREATE TABLE IF NOT EXISTS topics (
	topic_id 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	name 		VARCHAR(70) NOT NULL UNIQUE,
	icon_name 	VARCHAR(70) DEFAULT 'topic-default-ico.svg',
	description	TEXT DEFAULT NULL
);


-- ВОПРОСЫ
DROP TABLE IF EXISTS questions;
-- Максимальное кол-во ответов 4, мин. 2.
-- 		TYPE: тип вопроса
-- 			0: Выбор из вариантов
-- 			1: Догадка
CREATE TABLE IF NOT EXISTS questions (
	question_id 		INTEGER PRIMARY KEY AUTO_INCREMENT,
	topic_id 			INTEGER NOT NULL REFERENCES topics(topic_id) ON DELETE CASCADE ON UPDATE CASCADE,
	type 				TINYINT(5) NOT NULL,
	value 				VARCHAR(255) NOT NULL,
	correct_answer 		VARCHAR(255) NOT NULL,
	incorrect_answer_1 	VARCHAR(255) DEFAULT NULL,
	incorrect_answer_2 	VARCHAR(255) DEFAULT NULL,
	incorrect_answer_3 	VARCHAR(255) DEFAULT NULL
);



-- КОМНАТЫ
DROP TABLE IF EXISTS rooms;
-- PRIVACY: приватность комнаты
-- 		0: Открытая
-- 		1: Закрытая
-- 		2: Для друзей
-- STAGE: состояние комнаты в данный момент (check general.php)
-- 		0: Лобби ожидания
-- 		1: Распределение очереди
-- 		2: В игре
-- 		3: Конец игры
CREATE TABLE IF NOT EXISTS rooms (
	room_id 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	name 		VARCHAR(80) NOT NULL UNIQUE,
	privacy 	TINYINT(4) NOT NULL,
	password 	VARCHAR(100) DEFAULT NULL,
	creator_id 	INTEGER NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	stage 		TINYINT(10) NOT NULL DEFAULT 0,
	step_time 	TINYINT(40) NOT NULL DEFAULT 20,
	step_max 	TINYINT(40) NOT NULL DEFAULT 20,
	map_id 		INTEGER NOT NULL REFERENCES maps(map_id) ON DELETE CASCADE ON UPDATE CASCADE
);



-- КАРТЫ
DROP TABLE IF EXISTS maps;
-- SPRITES PATH: путь к спрайтам комнаты
CREATE TABLE IF NOT EXISTS maps (
	map_id 			INTEGER PRIMARY KEY AUTO_INCREMENT,
	name 			VARCHAR(80) NOT NULL UNIQUE,
	sprites_path 	VARCHAR(80) NOT NULL DEFAULT '',
	prepared_scheme	TEXT NOT NULL DEFAULT ''
);


-- ТИПЫ ЯЧЕЕК КАРТЫ
DROP TABLE IF EXISTS node_types;
-- SPRITES PATH: путь к спрайтам комнаты
CREATE TABLE IF NOT EXISTS node_types (
	node_types_id 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	name 			VARCHAR(80) NOT NULL UNIQUE,
	sprite 			VARCHAR(80) NOT NULL UNIQUE
);


-- ТЕМЫ КОМНАТЫ
DROP TABLE IF EXISTS rooms_topics;
CREATE TABLE IF NOT EXISTS rooms_topics (
	room_id 	INTEGER NOT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	topic_id 	INTEGER NOT NULL REFERENCES topics(topic_id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ШАГ ИГРЫ (1 на комнату)
DROP TABLE IF EXISTS ingame_steps;
CREATE TABLE IF NOT EXISTS ingame_steps (
	room_id 	INTEGER UNIQUE DEFAULT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	user_id 	INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	stage 		TINYINT DEFAULT 1, 
	order_value TINYINT(10) DEFAULT NULL,
	step_number TINYINT(40) NOT NULL DEFAULT 0,
	start_dt 	DATETIME DEFAULT NULL
);


-- ОЧЕРЕДНОСТЬ ХОДОВ (1 на персону)
DROP TABLE IF EXISTS ingame_steps_order;
CREATE TABLE IF NOT EXISTS ingame_steps_order (
	room_id 		INTEGER NOT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	user_id 		INTEGER NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	ignore_question TEXT NOT NULL DEFAULT '',
	color 			TINYINT(10) NOT NULL,
	value 			TINYINT(10) NOT NULL
);


-- ТЕКУЩЕЕ ПОЛОЖЕНИЕ ИГРОКА
DROP TABLE IF EXISTS ingame_position;
CREATE TABLE IF NOT EXISTS ingame_position (
	room_id 	INTEGER NOT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	user_id 	INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	position 	VARCHAR(80) NOT NULL,
	dice		TINYINT(10) DEFAULT 0
);



-- УВЕДОМЛЕНИЯ
DROP TABLE IF EXISTS notifications;
-- Type:
-- 0: Системное уведомление
-- 1: Добавление в друзья
-- 2: Приглашение в игру
-- Content служит как короткое хранилице текст сис. уведомления
CREATE TABLE IF NOT EXISTS notifications (
	notification_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	sender_id 		INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	recipient_id 	INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	type 			TINYINT(10) NOT NULL,
	content 		VARCHAR(255) DEFAULT NULL
);



-- ЛИЧНЫЕ СООБЩЕНИЯ
DROP TABLE IF EXISTS private_messages;
CREATE TABLE IF NOT EXISTS private_messages (
	private_message_id 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	sender_id 			INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	recipient_id 		INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	content 			TEXT NOT NULL,
	checked 			TINYINT(2) DEFAULT 0
);



-- ДРУЗЬЯ
-- TODO: надо бы сделать так, чтобы friend_1_id всегда был < friend_2_id
DROP TABLE IF EXISTS friends;
CREATE TABLE IF NOT EXISTS friends (
	friend_1_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	friend_2_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);