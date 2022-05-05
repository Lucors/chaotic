<div class="modals">
    <div class="modal" id="settings">
        <form class="container">
            <div class="modal-close b1 red">
                <img src="assets/img/close.svg">
            </div>
            <div class="content">
                <div class="title">
                    Настройки
                </div>
               	<div class="fields">

               	</div>

                <div id="apply-settings-msg"></div>
                <div class="hline"></div>
                <div class="submit">
                    <input id="do-apply-settings" class="b1 grey" type="button" value="Сохранить">
                </div>
            </div>
        </form>
    </div>

    <div class="modal" id="ratings">
        <form class="container">
            <div class="modal-close b1 red">
                <img src="assets/img/close.svg">
            </div>
            <div class="content">
                <div class="title">
                    Рейтинг
                </div>
                <div class="header field">
                	<div class="position">№</div>
					<div class="vline"></div>
					<div class="name">Никнейм</div>
					<div class="vline"></div>
					<div class="count-won">Побед</div>
					<div class="vline"></div>
					<div class="count-total">Всего</div>
                </div>
               	<div class="fields">
               		<!-- Здесь общий рейтинг -->
               	</div>
               	<div class="hline"></div>
               	<div class="user-rating field">
               		<!-- Здесь рейтинг пользователя -->
               	</div>
            </div>
        </form>
    </div>

    <div class="modal" id="room-create-form">
        <form class="container">
            <div class="content">
                <div class="title">
                    Создание комнаты
                </div>

               	<div class="fields">
					<div class="field name">
						<div class="key">Название комнаты:</div>
						<input type="text" name="room-name" class="value i1 grey">
					</div>

					<div class="field step-time">
						<div class="key">Время ответа:</div>
						<input type="number" name="step-time" class="value i1 grey" min="10" max="40" value="10">
						<div class="postfix">сек</div>
					</div>

					<div class="field topics">
						<div class="key">Темы вопросов (макс. 4):</div>
						<div class="value">
							<!-- Иконки тем -->
<!-- 							<div class="topic icob1" tid="0">
								<img class="icon" src="assets/img/topics/topic-harry-ico.svg">
								<div class="name">Гарри Поттер</div>
							</div> -->
						</div>
					</div>

					<div class="field privacy">
						<div class="key">Приватность:</div>
						<select class="value s1 grey">
							<option value="0" selected>Открытая</option>
							<option value="1">Закрытая</option>
							<option value="2">Для друзей</option>
						</select>
					</div> 

					<!-- Если .field.password.active, то пароль активен -->
					<div class="field password">
						<div class="key">Пароль:</div>
						<input type="password" name="password" class="value i1 grey">
						<img src="assets/img/show-ico.svg" class="postfix icob1">
					</div>

               	</div>

                <div id="room-create-msg"><!-- Сообщение об ошибка --></div>
                <div class="hline"></div>
                <div class="actions">
					<input type="button" class="close b1 red" value="Отмена">
					<input type="button" id="do-create-room" class="b1 grey" value="Создать комнату">
                </div>
            </div>
        </form>
    </div>
</div>

<div id="self-user">
	<div class="left">
		<img class="u-avatar" src="assets/img/avatars/default.png">
		<div class="substrate"></div>
	</div>
	<div class="right">
		<span class="u-nick">
			<?php
				echo "Биг Джей";
			?>
		</span>
		<span class="u-id" title="Нажмите, чтобы скопировать">
			<?php
				echo "#0000";
			?>
		</span>
	</div>
</div>

<div id="profile-container">

	<div class="search">
		<div class="i1parent">
            <input id="query-input" class="i1 grey" type="text" name="search" placeholder="Никнейм или #ID">
        </div>

        <div id="do-search-users" class="b1 grey">
            <img src="assets/img/search-ico.svg">
        </div>
	</div>

	<div class="actions">
		<div class="b1parent">
            <button id="get-friends-list" class="b1 grey">
                Друзья
            </button>
        </div>

        <div class="b1parent">
            <button id="get-notif-list" class="b1 grey">
                Уведомления
            </button>
        </div>
	</div>

	<div class="list">
		
	</div>
</div>

<div id="messenger">
    <div class="close b1 red">
        <img src="assets/img/close.svg">
    </div>

    <div class="dialogue">
<!--     	<div class="msg incoming">
    		<div class="msg-body">Текст входящего сообщения</div>
    	</div>
    	<div class="msg outgoing">
    		<div class="msg-body">Текст исходящего сообщения</div>
    	</div> -->
    </div>
    <div class="actions">
    	<div class="i1parent">
            <input id="msgr-input" class="i1 grey" type="text" name="msg" placeholder="Сообщение">
        </div>

        <div id="do-send-msg" class="b1 grey">
            <img src="assets/img/send-ico.svg">
        </div>
    </div>

</div>


<div class="content">
	<div class="left">
		<div id="mm-actions" class="mm-buttons">
			<div class="b1parent">
                <button id="q-edit" class="b1 grey">
                    Конструктор вопросов
                </button>
            </div>
			<div class="b1parent">
				<button id="room-create" class="b1 grey">
					Создать комнату
				</button>
			</div>
			<div id="do-quick-game" class="b1parent">
				<button class="b1 grey">
					Быстрая игра
				</button>
			</div>
			<div class="b1parent">
				<button class="b1 grey do-settings">
					Настройки
				</button>
			</div>
			<div class="b1parent">
				<button id="do-rating" class="b1 grey">
					Рейтинг
				</button>
			</div>
			<div class="b1parent">
				<button class="b1 red do-logout">
					Выйти
				</button>
			</div>
		</div>
	</div>

	<div class="right">
		<div id="room-explorer">
			<div id="room-list">
				<div class="substrate"></div>

				<div class="body">
					<div class="headers">
						<div class="list-refresh b1 grey">
			            	<img src="assets/img/refresh.svg">
			            </div>
						<span class="title">Название</span>
	            		<div class="vline"></div>
						<img class="closeness" src="assets/img/lock-ico.svg">
	            		<div class="vline"></div>
						<span class="users">Игроки</span>
					</div>
				</div>
			</div>

			<div id="room-info-parent">
				<div id="room-info">
	<!-- 				<span class="title">ТЕСТОВОЕ НАЗВАНИЕ!</span>
					<div class="difficulty">
						<div class="title">Темы вопросов: 
							<span class="data as-text">Легко</span>
						</div>
						<img class="data as-ico" src="assets/img/difficulty-1-ico.svg">
					</div>
					<div class="users">
						<div class="title">Игроки [
							<span class="data as-text">1/5</span>
							]:
						</div>
						<div class="list">
							
						</div>
					</div>

					<div class="closeness">
						<div class="about">Данная комната требует пароль для входа:</div>
						<div class="data">
							<img class="as-ico" src="assets/img/lock-ico.svg">
							<div class="i1parent">
	                        	<input class="i1 grey" type="text" name="pass" placeholder="Пароль">
	                    	</div>
						</div>
					</div>

					<div class="submit">
		                <input id="r-enter" class="b1 grey" type="submit" value="Войти">
		            </div> -->
		        </div>
			</div>
		</div>
	</div>
</div>