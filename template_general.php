<div id="message-box">
    <!-- <div class="title"></div> -->
    <div class="close b1 red">
        <img src="assets/img/close.svg">
    </div>
    <!-- <img class="close icob2 red" src="assets/img/close.svg"> -->
    <div class="content"></div>
</div>

<div id="rotate-warning">
    <img src="assets/img/rotate-your-device.svg">
    <div class="info">
        <span class="title">Пожалуйста, переверните ваше устройство</span>
        <span class="about">Игра Chaotic работает только в горизонтальной ориентации</span>
    </div>
</div>

<div id="mobile-warning">   
    <img src="assets/img/unsupported-device.svg">
    <div class="info">
        <span class="title">Устройство не поддерживается</span>
        <span class="about">Игра Chaotic пока не поддерживает данный тип устройств</span>
        <div class="action">
            <input id="mobile-warning-ignore" class="b2 red" type="submit" value="Игнорировать">
        </div>
    </div>
</div>

<div class="modals">
    <div class="modal" id="temp-unav">
        <div class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Сообщение</span>
                    <div class="modal-close b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>
                <span class="data">Временно недоступно</span>
                <span class="data">Возможно данный раздел в разработке</span>
            </div>
        </div>
    </div>

<!--     <div class="modal" id="general-modal">
        <div class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Сообщение</span>
                    <div class="modal-close b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>
                <span class="data"></span>
            </div>
        </div>
    </div> -->


    <div class="modal" id="general-modal">
        <form class="container">
            <div class="content">
                <div class="header">
                    <span class="title">Сообщение</span>
                    <div class="modal-close b1 red">
                        <img src="assets/img/close.svg">
                    </div>
                </div>

                <div class="fields">
                    <div class="field loading">
                        <div class="loading-ico"></div>
                    </div>
                    <div class="field modal-data">
                    </div>
                </div>

                <div id="general-modal-msg" class="fail-msg"></div>
                <div class="hline"></div>

                <div class="actions">
                    <input type="button" class="apply b2 grey" value="Ладно">
                    <input type="button" class="cancel b2 red" value="Отмена">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="parallax-bg">
    <!-- /imgs/p1a.gif -->
    <div class="shapes"></div>
    <div class="shapes"></div>
    <div class="shapes"></div>
    <!-- <div class="shapes"></div> -->
</div>

<!-- <video id="pbtest" src="assets/img/p1b.mp4" autoplay loop
style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 200vh;
    z-index: -3;
    opacity: 0.15;
"></video>  -->

<div id="dots"></div>

<div class="dummies left">
    <div class="dummy"></div>
    <div class="dummy"></div>
    <div class="dummy"></div>
</div>
<div class="dummies right">
    <div class="dummy"></div>
    <div class="dummy"></div>
    <div class="dummy"></div>
</div>
<div class="dummies top">
    <div class="dummy"></div>
    <div class="dummy"></div>
    <div class="dummy"></div>
</div>