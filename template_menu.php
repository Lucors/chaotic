<div class="modals">
    <div class="modal" id="quick-game-modal">
        <form class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Поиск игры</span>
                    <div class="modal-close hide b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>

	            <div class="fields">
	               	<div class="loading-ico searching"></div>
	                <div id="quick-game-msg" class="fail-msg"></div>
               	</div>
                <div class="hline hide"></div>

                <div class="actions hide">
					<input type="button" class="do-retry b2 grey" value="Повторить">
					<input type="button" class="close b2 red" value="Отмена">
                </div>
            </div>
        </form>
    </div>

    <div class="modal" id="settings">
        <form class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Настройки</span>
                    <div class="modal-close b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>

               	<div class="fields">
               	</div>

                <div id="apply-settings-msg" class="fail-msg"></div>
                <div class="hline"></div>

                <div id="do-apply-settings" class="submit b1 grey">
                    <div class="value">Сохранить</div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal" id="ratings">
        <form class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Рейтинг</span>
                    <div class="modal-close b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>

               	<div class="fields">
	                <div class="header field">
	                	<div class="position">№</div>
						<div class="vline"></div>
						<div class="name">Никнейм</div>
						<div class="vline"></div>
						<div class="count-won">Побед</div>
						<div class="vline"></div>
						<div class="count-total">Всего</div>
	                </div>
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
                <div class="header">
                    <span class="title">Создание комнаты</span>
                    <div class="modal-close b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>

               	<div class="fields">
					<div class="field name">
						<div class="key">Название комнаты:</div>
						<input type="text" name="room-name" class="value i1 grey">
					</div>

					<div class="field step-time">
						<div class="key">Время ответа:</div>
						<input type="number" name="step-time" class="value i1 grey" min="10" max="40" value="20">
						<div class="postfix">сек</div>
					</div>

					<div class="field maps">
						<div class="key">Карта:</div>
						<select class="value s1 grey">
						</select>
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
						<div class="postfix icob1">
							<img src="assets/img/show-ico.svg">
						</div>
					</div>

               	</div>

                <div id="room-create-msg" class="fail-msg"><!-- Сообщение об ошибка --></div>
                <div class="hline"></div>
                <div class="actions">
					<input type="button" class="close b2 red" value="Отмена">
					<input type="button" id="do-create-room" class="b2 grey" value="Создать комнату">
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
				echo "Username";
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
		<div id="get-friends-list" class="b1 grey">
            <div class="value">Друзья</div>
        </div>
		<div id="get-notif-list" class="b1 grey">
            <div class="value">Уведомления</div>
        </div>
	</div>

	<div class="users-list">
		
	</div>
</div>

<div id="messenger">
	<div class="header">
		<span class="title">Диалог</span>
	    <div class="close b1 red">
	        <img src="assets/img/close.svg">
	    </div>
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
			<div id="q-edit" class="b1 grey">
	            <div class="value">Конструктор вопросов</div>
	        </div>
			<div id="show-room-create" class="b1 grey">
	            <div class="value">Создать комнату</div>
	        </div>
			<div id="do-quick-game" class="b1 grey">
	            <div class="value">Быстрая игра</div>
	        </div>
			<div class="do-settings b1 grey">
	            <div class="value">Настройки</div>
	        </div>
			<div id="do-rating" class="b1 grey">
	            <div class="value">Рейтинг</div>
	        </div>
			<div class="do-logout b1 red">
	            <div class="value">Выйти</div>
	        </div>
		</div>
	</div>

	<div class="right">
		<div id="room-explorer">
			<div id="room-list">
				<div class="substrate"></div>

				<div class="body">
					<div class="header">
						<div class="list-refresh b1 grey">
			            	<img src="assets/img/refresh.svg">
			            </div>
						<span class="title">Название</span>
	            		<div class="vline"></div>
						<img class="closeness" src="assets/img/lock-ico.svg">
	            		<div class="vline"></div>
						<span class="users">Игроки</span>
	            		<div class="vline"></div>
						<!-- <span class="topics">Темы</span>
	            		<div class="vline"></div> -->
						<span class="map">Карта</span>
					</div>

<!-- 					<div class="room" rid="36">
						<span class="title">Название #1</span>
	            		<div class="vline"></div>
						<img class="closeness" src="assets/img/lock-ico.svg">
	            		<div class="vline"></div>
						<span class="users">1 / 4</span>
	            		<div class="vline"></div>
						<div class="topics">
							<img class="topic" src="assets/img/topics/topic-papich-ico.svg">
							<img class="topic" src="assets/img/topics/topic-it-ico.svg">
							<img class="topic" src="assets/img/topics/topic-rulang-ico.svg">
							<img class="topic" src="assets/img/topics/topic-minecraft-ico.svg">
						</div>
	            		<div class="vline"></div>
						<span class="map">Conquest</span>
					</div> -->
				</div>
			</div>

			<div id="room-info-parent">
				<div class="room-info menu">
					<div class="header title">
						<!-- MAP PREVIEW HERE -->
						<img class="preview" src="assets/maps/conquest/map-preview.png">
						<span class="value">ТЕСТОВОЕ НАЗВАНИЕ!</span>
					</div>
					<div class="field map">
						<div class="key">Карта:</div>
						<div class="value"></div>
					</div>
					<div class="field creator">
						<div class="key">Создатель:</div>
						<div class="value">Lucors #0001</div>
					</div>
					<div class="field users">
						<div class="key">Игроки:</div>
						<div class="value"></div>
					</div>
					<div class="field step-time">
						<div class="key">Время ответа:</div>
						<div class="value"></div>
					</div>
					<div class="field topics simplified">
						<div class="key">Темы вопросов:</div>
						<div class="value">
							<!-- Иконки тем -->
							<div class="topic icob1" tid="0">
								<img class="icon" src="assets/img/topics/topic-harry-ico.svg">
								<div class="name">Гарри Поттер</div>
							</div>
						</div>
					</div>
					<div class="field privacy">
						<div class="key">Приватность:</div>
						<div class="value"></div>
					</div> 

					<div class="footer">
						<div class="room-info-msg fail-msg"></div>
						<div class="hline"></div>
						<!-- Если .field.password.active, то пароль активен -->
						<div class="field password">
							<img class="ico" src="assets/img/lock-ico.svg">
							<div class="i1parent">
								<input class="i1 grey" type="password" name="pass" placeholder="Пароль">
							</div> 
						</div>
						<div class="field actions">
							<div id="do-room-enter" class="b1 grey">
					            <div class="value">Войти</div>
					        </div>
							<!-- <input id="do-room-enter" class="b1 grey" type="submit" value="Войти"> -->
						</div>
					</div>
		        </div>
			</div>
		</div>
	</div>
</div>