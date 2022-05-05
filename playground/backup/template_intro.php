<div class="modals">
    <div class="modal" id="create-account">
        <form class="container">
            <div class="modal-close b1 red">
                <img src="assets/img/close.svg">
            </div>
            <div class="content">
                <div class="title">
<!--                     Дорогой аноним!<br>
                    Ты — на пороге удивительных открытий.
                    Перед тобой могут распахнуться двери нашей прекрасной игры, стоит лишь заполнить форму ниже! -->
                    Регистрация
                </div>

                <div class="field">
                    <img class="ico" src="assets/img/nick-ico.svg">
                    <div class="i1parent">
                        <input class="i1 grey" type="text" name="nick" placeholder="Никнейм">
                    </div> 
                </div>
                <div class="field">
                    <img class="ico" src="assets/img/email-ico.svg">
                    <div class="i1parent">
                        <input class="i1 grey" type="email" name="email" placeholder="Эл. Почта">
                    </div> 
                </div>
                <div class="both-required">
                    <div class="field">
                        <img class="ico" src="assets/img/lock-ico.svg">
                        <div class="i1parent">
                            <input class="i1 grey" type="password" name="pass" placeholder="Пароль">
                        </div> 
                    </div>
                    <div class="field">
                        <img class="ico" src="assets/img/lock2-ico.svg">
                        <div class="i1parent">
                            <input class="i1 grey" type="password" name="pass2" placeholder="Повторите пароль">
                        </div> 
                    </div>
                </div>
                <div id="create-account-fail-msg"></div>

                <div class="hline"></div>

                <div class="submit">
                    <input id="do-create-account" class="b1 grey" type="button" value="Создать аккаунт">
                </div>
            </div>
        </form>
    </div>
</div>

<canvas id="arrows"></canvas>
<div id="auth-substrate"></div>

<div class="content">
    <a id="chaotic-a" href="/">
        <img id="chaotic-logo" src="assets/img/logo-big.svg">
    </a>

    <div id="authparent">
        <form id="auth">
            <div class="inputs">
                <div>
                    <img class="ico" src="assets/img/email-ico.svg">
                    <div class="i1parent">
                        <input class="i1 grey" type="email" name="email" placeholder="Эл. Почта">
                    </div>
                </div>
                <div>
                    <img class="ico" src="assets/img/lock-ico.svg">
                    <div class="i1parent">
                        <input class="i1 grey" type="password" name="pass" placeholder="Пароль">
                    </div>
                </div>
            </div>
            <div id="auth-fail-msg"></div>

            <div class="submit">
                <input id="do-login" class="b1 grey" type="button" value="Войти">
            </div>

            <div class="hline"></div>

<!--                     <div class="about">
                Данный текст написан в качестве заглушки.
            </div> -->

            <div class="extras">
                <div class="extra">
                    <span class="title">Забыли пароль?</span>
                    <div class="b1parent">
                        <input id="extr-restore" class="b1 grey" type="button" value="Восстановить">
                    </div>
                </div>

                <div class="extra">
                    <span class="title">У вас нет аккаунта?</span>
                    <div class="b1parent">
                        <input id="extr-create" class="b1 grey" type="button" value="Создать">
                    </div>
                </div>
            </div>
        </form>
    </div>   
</div>