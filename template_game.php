<div id="do-game-exit" class="b1 red">
    <div class="value" title="Покинуть игру">
        <img src="assets/img/exit.svg">
        <span>Покинуть игру</span>
    </div>
</div>

<div class="step-info left">
    <div class="step-order side-info">
        <div class="header">
            <div class="title">Очередь:</div>
        </div>
        <div class="value">
            <div class="circle" color=1 uid=0></div>
            <div class="circle" color=2 uid=0></div>
            <div class="circle" color=3 uid=0></div>
            <div class="circle" color=4 uid=0></div>
        </div>
    </div>

    <div class="room-users side-info">
        <div class="header">
            <div class="title">
                <div class="key">Игроки</div>
                <span class="value">1/4</span>
            </div>
            <div class="fold b1 grey">
                <img src="assets/img/fold.svg">
            </div>
        </div>
        <div class="users-list"></div> 
    </div>
</div>

<div class="step-info right">
    <div class="step-time side-info">
        <div class="header">
            <div class="title">Осталось времени:</div>
        </div>
        <div class="time">
            <div class="value">0</div>
            <div class="prefix">сек.</div>
        </div>
    </div>

    <div id="take-a-moment" title="Ожидание окончания хода">
        <img src="/assets/img/loading.gif"/>
        <span>Чужой ход</span>
    </div>
</div>

<div class="side-topics side-info">
    <div class="header">
        <div class="title">Темы:</div>
    </div>
    <div class="value field topics simplified">
    </div>
</div>


<div id="canvas-parent">
    <div class="title" title="Название комнаты"></div>
    <div class="scale">
        <div class="scale-p icob1">
            <img src="assets/img/scale-p.svg">
        </div>
        <div class="hline"></div>
        <div class="scale-n icob1">
            <img src="assets/img/scale-n.svg">
        </div>
    </div>
    <canvas></canvas>
</div>

<div id="banner">
    <div class="dummy"></div>
    <div class="dummy"></div>
    <div class="value">
    </div>
</div>

<div class="modals">
    <div class="modal" id="endgame">
        <div class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Игра окончена</span>
                </div>

	            <div class="fields">
                    <div class="field avatar">
                        <img class="value" src="">
                        <div class="substrate"></div>
                    </div>
                    <div class="field nickname">
                        <span class="u-nick"></span>
                        <span class="u-id"></span>
                    </div>
                    <div class="field winner">Стал победителем</div>
                    <div class="field about">
                        Увы, вы проиграли<br>
                        Повезет в следующий раз!
                    </div>
               	</div>

                <div class="hline"></div>
                <div class="actions">
                    <div id="do-end-game" class="b1 grey">
                        <div class="value">Ладно</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="question" qid="0">
        <div class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Внимание, вопрос!</span>
                    <div class="topic" tid="0" title="Тема вопроса">
                        <img class="icon" src="">
                    </div>
                </div>

                <div class="question-value">
                </div>

	            <div class="fields answers">
                    <div class="field answer b1 grey" av="0">
                        <div class="value">В 1945 году.</div>
                    </div>
               	</div>

                <div class="actions">
                    <div class="icob1">
                        <div class="give-up">Сдаюсь</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
