<div class="modals">
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


<!-- <div></div> -->