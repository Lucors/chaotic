<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Chaotic API Docs</title>
		<link rel="shortcut icon" href="assets/img/favicon.png"/>
		<link rel="stylesheet" href="assets/css/blank.css">
	</head>
	<body class="scrollable">
		<div id="dots"></div>

		<table class="docs">
			<tr>
				<th>Запрос</th>
				<th>Ответ</th>
			<tr>

			<tr class="route">
				<th colspan="2">
					route: auth<br>
					Секция авторизации
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: login<br>
					email: value<br>
					pass: value<br>
					Вход в профиль
				</td>
				<td>
					user_data = {}<br>
					Данные профиля
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: signup<br>
					nick: value<br>
					email: value<br>
					pass: value<br>
					Регистрация нового акк.
				</td>
				<td>
					user_data = {}<br>
					Данные профиля
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: logout<br>
					Выход из профиля
				</td>
				<td>
					-
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: daemon<br>
					Слушатели уведомлений, сообщений, игры
				</th>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: debug<br>
					Секция отладочных функций
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: time<br>
					Текущее время
				</td>
				<td>
					time = value
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: echo<br>
					Вернуть запрос
				</td>
				<td>
					request = {}
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: map<br>
					Секция игровых карт
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: get<br>
					map_id: value<br>
					Вернуть данные карты map_id
				</td>
				<td>
					map_data = {}<br>
					Данные карты
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: getall<br>
					Вернуть список карт
				</td>
				<td>
					maps_list = []<br>
					Список карт
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_scheme<br>
					map_id: value<br>
					Вернуть схему карты map_id
				</td>
				<td>
					scheme = {}<br>
					Схема карты<br>
					types_list = []<br>
					Список типов ячеек
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_node_types<br>
					Вернуть список типов ячеек
				</td>
				<td>
					types_list = []<br>
					Список типов ячеек
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_positions<br>
					room_id: value<br>
					Вернуть список позиций всех 
					игроков комнаты room_id
				</td>
				<td>
					pos_list = []<br>
					Позиции игроков
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: move<br>
					map_id: value<br>
					room_id: value<br>
					Сдвинуть фишку игрока 
					(самого себя) в игре
				</td>
				<td>
					movement = {}<br>
					Ассоц. массив данных о перемещении
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: message<br>
					Секция личных сообщений
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: getall<br>
					target: value<br>
					Весь диалог с user_id = target
				</td>
				<td>
					msg_list = []<br>
					Список сообщений
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: set<br>
					target: value<br>
					content: value<br>
					Отправить личн. сообщ. user_id = target 
				</td>
				<td>
					message_id = value<br>
					ID последнего сообщения
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: notification<br>
					Секция уведомлений
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: getall<br>
					Получить все активные уведомления 
				</td>
				<td>
					notif_list = []<br>
					Список уведомлений
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: set<br>
					type: value<br>
					target: value<br>
					content: optional<br>
					Отправить уведомление target типа type
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: answer<br>
					answer: value<br>
					notif_id: value<br>
					Ответить на notif_id ответом answer
				</td>
				<td>
					-
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: profile<br>
					Секция функций профиля<br>
					Наследует функции user/methods
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: get_friends<br>
					Получить список друзей
				</td>
				<td>
					friends_list = []<br>
					Список пользователей
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_role<br>
					Получить user_role
				</td>
				<td>
					user_role = value
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_avatar<br>
					Получить аватарку профиля
				</td>
				<td>
					avatar_path = value<br>
					Путь к файлу
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_settings<br>
					Получить настройки профиля
				</td>
				<td>
					settings_data = {}<br>
					Ассоц. массив пользоват. настроек
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: set_settings<br>
					settings_data: {}<br>
					Установить настройки профиля
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: setdefault_settings<br>
					Установить настройки профиля по умолчанию
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: set_stage<br>
					stage: value<br>
					Установить состояния клиента = stage
				</td>
				<td>
					-
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: question<br>
					Секция работы с вопросами
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: get_unique<br>
					room_id: value<br>
					Получить уникальный вопрос
				</td>
				<td>
					question = {}<br>
					Данные вопроса
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: answer_test<br>
					qid: value<br>
					answer: value<br>
					Тест проверки ответа на вопрос
				</td>
				<td>
					answer = bool<br>
					Результат ответа
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: answer<br>
					room_id: value<br>
					qid: value<br>
					give_up: bool<br>
					answer: value<br>
					Отвечает на вопрос qid 
					игровой комнаты room_id
				</td>
				<td>
					answer = bool<br>
					Результат ответа
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: rating<br>
					Секция работы с рейтингом
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: getall<br>
					Получить рейтинговую таблицу
				</td>
				<td>
					rating_list = []<br>
					Список рейтинга<br>
					user_rating = optional{}<br>
					Рейтинг профиля
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: room<br>
					Секция игровых комнат
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: getall<br>
					Получить список активных комнат
				</td>
				<td>
					rooms_list = []<br>
					Список комнат
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get<br>
					room_id: value<br>
					Получить данные комнаты room_id
				</td>
				<td>
					room_id = value<br>
					Данные комнаты<br>
					room_data = {}<br>
					Данные комнаты<br>
					topics_list = []<br>
					Список тем комнаты<br>
					users_list = []<br>
					Список пользователей комнаты
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: create<br>
					room_data: {}<br>
					Создать комнату по данным room_data
				</td>
				<td>
					room_id = value<br>
					Данные комнаты<br>
					room_data = {}<br>
					Данные комнаты<br>
					topics_list = []<br>
					Список тем комнаты<br>
					users_list = []<br>
					Список пользователей комнаты
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: exit<br>
					Выйти из текущей комнаты
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: enter<br>
					room_id: value<br>
					password: value<br>
					Войти в комнату room_id
				</td>
				<td>
					users_list = []<br>
					Список пользователей комнаты
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: set_room_ready<br>
					ready: value<br>
					Установить готовность ready
					для себя в текущей комнате
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: set_game_ready<br>
					ready: value<br>
					Установить готовность ready
					для себя в текущей игре
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: start_room<br>
					room_id: value<br>
					Запустить комнату
					(Перейти на этап RSTAGE_GAME_WAIT)
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: get_order<br>
					room_id: value<br>
					Вернуть очередь ходов 
					комнаты room_id
				</td>
				<td>
					order_list = []<br>
					Список, очередь ходов 
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: start_game<br>
					room_id: value<br>
					Запустить игру
					(Перейти на этап RSTAGE_GAME_START)
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: push_order<br>
					room_id: value<br>
					Протолкнуть очередь вперед
					на одного игрока 
				</td>
				<td>
					-
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: join<br>
					room_id: value<br>
					password: optional value<br>
					Войти в комнату room_id
				</td>
				<td>
					-
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: topic<br>
					Секция работы с темами вопросов
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: getall<br>
					Получить список всех возможных тем
				</td>
				<td>
					topics_list = []<br>
					Список тем
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: getby_room<br>
					room_id: value<br>
					Получить темы комнаты room_id
				</td>
				<td>
					topics_list = []<br>
					Список тем комнаты room_id
				</td>
			</tr>


			<tr class="route">
				<th colspan="2">
					route: user<br>
					Секция работы с пользователями
				</th>
			</tr>
			<tr class="handler">
				<td>
					op: getby_query<br>
					query: value<br>
					Найти пользователей по query
				</td>
				<td>
					users_list = []<br>
					Список пользователей
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: getby_room<br>
					room_id: value<br>
					Вернуть пользователей комнаты room_id
				</td>
				<td>
					users_list = []<br>
					Список пользователей
				</td>
			</tr>
			<tr class="handler">
				<td>
					op: getsome<br>
					list: []<br>
					Вернуть пользователей по id by list[]
				</td>
				<td>
					users_list = []<br>
					Список пользователей
				</td>
			</tr>
		</table>


<!-- 		<div class="modal">
		    <div class="container">
		        <div class="content">
		            <div class="header">
	                    <span class="title">Сообщение</span>
	                </div>
	                <span id="code">DOCS</span>
	                <span class="data">Заглушка документации по <b>Chaotic API</b></span>
	                <div class="hline"></div>
	                <span class="data desc">Заглушка документации по <b>Chaotic API</b></span>
					<div  class="hline"></div>
	                <a class="index" href="..">to Chaotic API</a>
		        </div>
		    </div>
		</div> -->
	</body>
</html>