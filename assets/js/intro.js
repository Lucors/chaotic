
function startIntro(){
    anime({
        targets: "#authparent",
        height: "22vw",
        padding: "1%",
        duration: adur(600),
        easing: "linear"
    });

    var tl = anime.timeline({
        targets: "#chaotic-logo",
    });
    tl
        .add({
            width: "65vw",
            duration: adur(200),
            easing: ease,
            complete: function(){
                anime({
                    targets: "#auth-substrate",
                    scaleX: {
                        value: [0, 1],
                        easing: elasticOut1,
                        duration: adur(1200)
                    },
                    opacity: {
                        // value: 0.46,
                        value: 1,
                        easing: "linear",
                        duration: adur(600)
                    },
                    // easing: spring1,
                    delay: adur(150)
                });

                setTimeout(function(){
                    $("#auth").css({
                        visibility: "visible"
                    });
                    anime({
                        targets: "#auth",
                        translateY: ["20%", 0],
                        opacity: 1,
                        easing: "easeOutSine",
                        duration: adur(500)
                    });
                    // $("#auth").addClass("active");
                    setTimeout(function(){
                        $("#intro > .content").css({
                            overflow: "hidden auto"
                        });
                    }, adur(500)) 
                }, adur(150));
            }
        })
        .add({
            width: "20vw",
            duration: adur(400),
            easing: easeOut,
            complete: function(){
                // redraw({
                //     x: mouse.x,
                //     y: mouse.y
                // });
                // $("#arrows").addClass("active");
            }
        })
}


function handleLoginData(data){
    $("#create-account .field input").val("");
    $("#auth .inputs input").val("");
    window.user.stage = data.stage;
    window.user.nick = data.nick;
    window.user.user_id = data.user_id;
    window.user.role = data.role;
    // window.user.login = data.login;
    // getUserRole(setExtraMenuAction);
    if (window.user.stage > 4){
        location.reload();
    }
}
function setupLoginPresets(gotoRoom){
    var promise = $.Deferred();
    var uid = getFormattedID(window.user.user_id);
    $("#self-user .u-id").html(uid);
    $("#self-user .u-nick").html(window.user.nick);
    if (!gotoRoom){
        $("#room-list .list-refresh").click();
    }

    setUserAvatar();
    setExtraMenuAction();
    getAllSettings().then(function(data){
        user.settings_data = data.settings_data;
        applySettings(user.settings_data);
        promise.resolve();
    });
    return promise;
}
function startLoginAnim(){
    let gotoRoom = Boolean(window.user.stage > 1);
    setupLoginPresets(gotoRoom).then(function(){
        anime({
            targets: "#chaotic-logo, #authparent",
            opacity: 0,
            duration: adur(600),
            easing: easeOut
        });
        anime({
            targets: "#auth-substrate",                
            scaleX: {
                value: [1, 0],
                easing: elasticIn1,
                duration: adur(600)
            },
            opacity: {
                value: 0,
                easing: "linear",
                duration: adur(600)
            }
        });

        // valto = "-100vh";
        // if (gotoRoom){
        //     valto = "0";
        // }
        // anime({
        //     targets: "body",
        //     top: valto,
        //     duration: adur(1200),
        //     easing: "easeInOutCubic",
        //     complete: function(){
        //         // killArrows();
        //         $("#auth, #chaotic-logo, #authparent, #intro > .content").removeAttr("style");
        //         $("#auth, #arrows").removeClass("active");
        //         $("#do-login, #auth .inputs input").prop("disabled", false);
        //     }
        // })

        // bodygoto("room");

        sleep(adur(500)).then(function(){
            startMenu(!gotoRoom)
        });
        if (gotoRoom){
            // $("body").attr("class", "room");
            getRoomData(currentRoom.id, -1).then(
                function(responsed){
                    currentRoom.setup(responsed);

                    handleRoomInfoData(currentRoom);
                    startRoomAnim(Boolean(currentRoom.creator_id == user.user_id));
                },
                function(){
                    showError("Ошибка получения данных комнаты");
                    bodygoto("menu");
                }
            );
        }
        else {
            // $("body").attr("class", "menu");
            bodygoto("menu");
        }
        // setTimeout(startMenu, adur(500));
    });
}





//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
    $("#do-login").click(function(){
        if (!$(this).is(':disabled')){
            $(this).prop("disabled", true);
            $("#auth .inputs input").prop("disabled", true);

            var data = {
                email: $("#auth input[name=email]").val(),
                pass: $("#auth input[name=pass]").val()
            }

            var errorTargetName = null;
            try {
                if (!data.email){
                    errorTargetName = "email";
                    throw new Error("Эл. Почта");
                }
                else if (!data.pass){
                    errorTargetName = "pass";
                    throw new Error("Пароль");
                }
            }
            catch (error){
                anime({
                    targets: `#auth input[name=${errorTargetName}]`,
                    scale: [
                        {value: 1},
                        {value: 0.85},
                        {value: 1}
                    ],
                    delay: anime.stagger(adur(100)),
                    duration: adur(200),
                    easing: ease
                });
                $("#auth-fail-msg").html(`Заполните поле "${error.message}"`);
                $("#do-login, #auth .inputs input").prop("disabled", false);
                return;
            }
            $("#auth-fail-msg").html("");

            apiRequest({
                route: "auth",
                op: "login",
                email: data.email,
                pass: data.pass
            }, {
                method: "POST",
                success: function(data){
                    if (data.result){
                        if ("msg" in data){
                            showCountdownMessage(data.msg, 2000)
                        }
                        handleLoginData(data.user_data);
                        currentRoom.id = data.user_data.room_id;
                        startLoginAnim();
                    }
                    else {
                        if ("msg" in data){
                            $("#auth-fail-msg").html(data.msg);
                        }
                        this.error();
                    }
                },
                error: function(){
                    $("#do-login, #auth .inputs input").prop("disabled", false);
                }
            });

            // $.ajax({
            //     url: "login.php",
            //     method: "POST",
            //     data: data,
            //     dataType: "json",
            //     success: function(data){
            //         if (data.result){
            //             if ("msg" in data){
            //                 showCountdownMessage(data.msg, 2000)
            //             }
            //             handleLoginData(data);
            //             startLoginAnim();

            //         }
            //         else {
            //             if ("msg" in data){
            //                 $("#auth-fail-msg").html(data.msg);
            //             }
            //             this.error();
            //         }
            //     },
            //     error: function(){
            //         $("#do-login, #auth .inputs input").prop("disabled", false);
            //     }
            // });
        }
    });
    onEnter($("#auth input[name=email]"), function(){
        $("#auth input[name=pass]").focus();
    });
    onEnter($("#auth input[name=pass]"), function(){
        $("#do-login").click();
    });

    $("#do-create-account").click(function(){
        if (!$(this).attr('disabled')){
            $("#do-create-account, #create-account input").attr("disabled", true);

            var data = {
                nick: $("#create-account input[name=nick]").val(),
                email: $("#create-account input[name=email]").val(),
                pass: $("#create-account input[name=pass]").val()
            }
            var pass2 = $("#create-account input[name=pass2]").val();

            var errorTargetName = null;
            try {
                if (!data.nick){
                    errorTargetName = "nick";
                    throw new Error("Никнейм");
                }
                else if (!data.email){
                    errorTargetName = "email";
                    throw new Error("Эл. Почта");
                }
                else if (!data.pass){
                    errorTargetName = "pass";
                    throw new Error("Пароль");
                }
                else if (!pass2){
                    errorTargetName = "pass2";
                    throw new Error("Повторение пароля");
                }
            }
            catch (error){
                anime({
                    targets: `#create-account input[name=${errorTargetName}]`,
                    scale: [
                        {value: 1},
                        {value: 0.85},
                        {value: 1}
                    ],
                    delay: anime.stagger(adur(100)),
                    duration: adur(200),
                    easing: ease
                });
                $("#create-account-fail-msg").html(`Заполните поле "${error.message}"`);
                $("#do-create-account").attr('disabled', false);
                $("#create-account input").prop("disabled", false);
                return;
            }

            if (data.pass != pass2){
                // $("#create-account .both-required").css({
                //     background: "#ff00002e"
                // })
                anime({
                    targets: "#create-account .both-required .field .i1",
                    scale: [
                        {value: 1},
                        {value: 0.85},
                        {value: 1}
                    ],
                    delay: anime.stagger(adur(100)),
                    duration: adur(400),
                    // direction: 'alternate', //Работает некорр. при stagger
                    easing: ease,
                    complete: function(){
                        $("#create-account .both-required")
                            .add(".field .i1")
                            .removeAttr("style");
                    }
                });

                $("#create-account-fail-msg").html("Пароли не совпадают");
                $("#do-create-account").attr('disabled', false);
                $("#create-account input").prop("disabled", false);
                return;
            }  

            $("#create-account-fail-msg").html("");
            $("#do-create-account").attr('disabled', false);
            $("#create-account input").prop("disabled", false);

            apiRequest({
                route: "auth",
                op: "signup",
                nick: data.nick,
                email: data.email,
                pass: data.pass
            }, {
                method: "POST",
                success: function(data){
                    if (data.result){
                        $("#do-login, #auth .inputs input").prop("disabled", true);
                        handleLoginData(data.user_data);
                        hideModal(startLoginAnim);
                    }
                    else {
                        if ("msg" in data){
                            $("#create-account-fail-msg").html(data.msg);
                        }
                        this.error();
                    }
                },
                error: function(){
                    $("#do-create-account").attr('disabled', false);
                    $("#create-account input").prop("disabled", false);
                }
            });

            // $.ajax({
            //     url: "signup.php",
            //     method: "POST",
            //     data: data,
            //     dataType: "json",
            //     success: function(data){
            //         if (data.result){
            //             $("#do-login, #auth .inputs input").prop("disabled", true);
            //             handleLoginData(data.user_data);
            //             hideModal(startLoginAnim);
            //         }
            //         else {
            //             if ("msg" in data){
            //                 $("#create-account-fail-msg").html(data.msg);
            //             }
            //             this.error();
            //         }
            //     },
            //     error: function(){
            //         $("#do-create-account, #create-account input").prop("disabled", false);
            //     }
            // });
        }
    });
    onEnter($("#create-account input[name=nick]"), function(){
        $("#create-account input[name=email]").focus();
    });
    onEnter($("#create-account input[name=email]"), function(){
        $("#create-account input[name=pass]").focus();
    });
    onEnter($("#create-account input[name=pass]"), function(){
        $("#create-account input[name=pass2]").focus();
    })
    onEnter($("#create-account input[name=pass2]"), function(){
        $("#do-create-account").click();
    });

    $("#extr-restore").click(function(){
        if (!$(this).attr('disabled')){
            showModal($("#temp-unav"));
        }
    });

    $("#extr-create").click(function(){
        if (!$(this).attr('disabled')){
            showModal($("#create-account"));
        }
    });

    // USER НЕ АВТОРИЗИРОВАН
    if (window.user.stage == 0){
        setTimeout(startIntro, adur(100)); 
    }  
    // USER АВТОРИЗИРОВАН
    if (window.user.stage > 0){
        setTimeout(startLoginAnim, adur(50)); 
    }
});