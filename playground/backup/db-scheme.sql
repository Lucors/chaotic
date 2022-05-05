-- ПОЛЬЗОВАТЕЛИ
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS users (
	user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	email VARCHAR(80) NOT NULL UNIQUE,
	passhash VARCHAR(70) NOT NULL,
	nick VARCHAR(70) NOT NULL UNIQUE,
	last_dt DATETIME,
	room_id INTEGER DEFAULT NULL REFERENCES rooms(room_id) ON DELETE SET NULL ON UPDATE CASCADE,
	rating_total INTEGER DEFAULT 0,
	rating_won INTEGER DEFAULT 0,
	CHECK(rating_total >= 0 AND rating_won >= 0 AND rating_won <= rating_total)
);
-- -- ТЕСТОВЫЙ ПОЛЬЗОВАТЕЛЬ:
-- INSERT INTO users (email, passhash, nick) VALUES 
-- ('thesourcecode.max@yandex.ru', '$2y$10$HwdRQY0PL4K0ePf0MPZxm.rBEwzInjbKTWljgis9JdJdr32pUbemK', 'Lucors');



-- ТИПЫ НАСТРОЕК
DROP TABLE IF EXISTS settings_types;

CREATE TABLE IF NOT EXISTS settings_types (
	setting_type_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL UNIQUE,
	decryption VARCHAR(80) NOT NULL UNIQUE,
	input_type VARCHAR(30) NOT NULL DEFAULT 'text',
	default_value VARCHAR(80) NOT NULL
);



-- ЗНАЧЕНИЯ НАСТРОЕК
DROP TABLE IF EXISTS settings_values;

CREATE TABLE IF NOT EXISTS settings_values (
	setting_value_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	setting_type_id INTEGER NOT NULL REFERENCES settings_types(setting_type_id) ON DELETE CASCADE ON UPDATE CASCADE,
	value VARCHAR(80) NOT NULL
);



-- РОЛИ ИГРОКОВ
	--  Если роль не прописана, то 0:User
DROP TABLE IF EXISTS users_roles;

-- ROLES:
	-- 0:User
	-- 1:Moderator 
	-- 2:Admin
CREATE TABLE IF NOT EXISTS users_roles (
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	role TINYINT NOT NULL
);



-- ТЕМЫ ВОПРОСОВ
DROP TABLE IF EXISTS topics;

CREATE TABLE IF NOT EXISTS topics (
	topic_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(70) NOT NULL UNIQUE,
	icon_path VARCHAR(70) DEFAULT 'default.png'
);



-- ВОПРОСЫ
DROP TABLE IF EXISTS questions;

-- Максимальное кол-во ответов 4, мин. 2.
-- Тип вопроса:
	-- 0: Выбор из вариантов
	-- 1: Догадка
CREATE TABLE IF NOT EXISTS questions (
	question_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	topic_id INTEGER NOT NULL REFERENCES topics(topic_id) ON DELETE CASCADE ON UPDATE CASCADE,
	type TINYINT(5) NOT NULL,
	value VARCHAR(255) NOT NULL,
	correct_answer VARCHAR(255) NOT NULL,
	incorrect_answer_1 VARCHAR(255) DEFAULT NULL,
	incorrect_answer_2 VARCHAR(255) DEFAULT NULL,
	incorrect_answer_3 VARCHAR(255) DEFAULT NULL
);



-- КОМНАТЫ
DROP TABLE IF EXISTS rooms;

-- privacy:
	-- 0: Открытая
	-- 1: Закрытая
	-- 2: Для друзей
-- stage:
	-- 0: перед игрой
	-- 1: распределение очереди
	-- 2: в игре
	-- 3: конец игры
CREATE TABLE IF NOT EXISTS rooms (
	room_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(80) NOT NULL UNIQUE,
	privacy TINYINT(4) NOT NULL,
	password VARCHAR(100) DEFAULT NULL,
	creator_id INTEGER NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	stage TINYINT(10) NOT NULL DEFAULT 0,
	step_time TINYINT(40) NOT NULL DEFAULT 20 
);



-- ТЕМЫ КОМНАТЫ
DROP TABLE IF EXISTS rooms_topics;

CREATE TABLE IF NOT EXISTS rooms_topics (
	room_id INTEGER NOT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	topic_id INTEGER NOT NULL REFERENCES topics(topic_id) ON DELETE CASCADE ON UPDATE CASCADE
);



-- ОЧЕРЕДНОСТЬ ХОДОВ
DROP TABLE IF EXISTS ingame_steps_order;

CREATE TABLE IF NOT EXISTS ingame_steps_order (
	room_id INTEGER NOT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	value TINYINT NOT NULL
);



-- ШАГ ИГРЫ
DROP TABLE IF EXISTS ingame_steps;

CREATE TABLE IF NOT EXISTS ingame_steps (
	roomd_id INTEGER UNIQUE DEFAULT NULL REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
	user_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	question_id INTEGER DEFAULT NULL REFERENCES questions(question_id) ON DELETE CASCADE ON UPDATE CASCADE,
	start_dt DATETIME NOT NULL
);



-- ТЕКУЩЕЕ ПОЛОЖЕНИЕ ИГРОКА
DROP TABLE IF EXISTS ingame_position;

CREATE TABLE IF NOT EXISTS ingame_position (
	user_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	position TINYINT NOT NULL
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
	sender_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	recipient_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	type TINYINT(10) NOT NULL,
	content VARCHAR(255) DEFAULT NULL
);



-- ЛИЧНЫЕ СООБЩЕНИЯ
DROP TABLE IF EXISTS private_messages;

CREATE TABLE IF NOT EXISTS private_messages (
	private_message_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	sender_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	recipient_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	content TEXT NOT NULL,
	checked TINYINT(2) DEFAULT 0
);



-- ДРУЗЬЯ
DROP TABLE IF EXISTS friends;

CREATE TABLE IF NOT EXISTS friends (
	friend_1_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	friend_2_id INTEGER DEFAULT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);