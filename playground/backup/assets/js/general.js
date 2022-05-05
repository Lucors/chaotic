let flags = {
    mobileVersion: false,
    allowAnimations: true
};
// let flags.mobileVersion = false;
let currentDummy = null;
let currentModal = null;
let dummies = [topDummies, leftDummies, rightDummies];
let mouse = {
    x: 0,
    y: 0
};
let ws = {
    w: 0,
    h: 0
};

// let gameStages = ["intro", "menu", "room", "game"];
window.currentStage = 0;
window.user = {};

anime.suspendWhenDocumentHidden = false;
// EASING
let spring1 = "spring(1, 80, 10, 0)";
let elasticOut1 = "easeOutElastic(1.5, 0.5)";
let elasticIn1 = "easeInElastic(2, 2)";
let ease = "cubicBezier(0.250, 0.100, 0.250, 1.000)";
let easeOut = "cubicBezier(0.000, 0.000, 0.580, 1.000)";

function randomInt(min, max){
    return min + Math.floor(Math.random() * (max - min));
}

function randomFloat(min, max) {
    return min + (Math.random() * (max - min));
}
function randomElement(arr) {
    var index = randomInt(0, arr.length);
    return arr[index];
}

function checkWindowSize(wsw, wsh){
    if (wsh > wsw){
        $("#rotate-warning").css({visibility: "visible"});
        $("#rotate-warning > img").css({animation: "20s ease-in-out 0s infinite normal none running weak-wiggle"});
    }
    else {
        $("#rotate-warning").css({visibility: "hidden"});
        $("#rotate-warning > img").css({animation: "none"});
    }
}

function checkMobile(){
    // if(/Android|webOS|iPhone|iPad|Mac|Macintosh|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
    //     flags.mobileVersion = true;
    //     $("#mobile-warning").css({visibility: "visible"});
    // }
    return flags.mobileVersion;
};

function setAllowAnimations(value = true){
    if (value && value !== "0"){
        $(".parallax-bg > .shapes:nth-child(1)").css({
            animation: "200s ease-in-out 0s infinite normal none running pp-bg1"
        });
        $(".parallax-bg > .shapes:nth-child(2)").css({
            animation: "190s ease-in-out 0s infinite normal none running pp-bg2"
        });
        $(".parallax-bg > .shapes:nth-child(3)").css({
            animation: "170s ease-in-out 0s infinite normal none running pp-bg3"
        });
    }
    else {
        $(".parallax-bg > .shapes").css({
            animation: "none"
        });
    }
    flags.allowAnimations = value;
}


function adur(reqvalue){
    return (flags.allowAnimations) ? reqvalue : 0;
}


function scrollToBottom(element){
    element.scrollTop(element.prop("scrollHeight"));
}
// function tooglePanelBlur(toBlur = true){
//     if (toBlur){
//         $("body > .panel").addClass("blurred");
//     }
//     else {
//         $("body > .panel").removeClass("blurred");
//     }
// }
function topDummies(fade = true, callback = null){
    $(".dummies.right > .dummy, .dummies.left > .dummy").removeAttr("style");
    // tooglePanelBlur(fade);
    valto = 0;
    if (fade){
        valto = "100vh";
    }
    else {
        currentDummy = null;
    }
    anime({
        targets: ".dummies.top > .dummy",
        top: valto,
        duration: adur(300),
        delay: anime.stagger(adur(100)),
        easing: easeOut,
        complete: function(){
            if (fade){
                currentDummy = topDummies;
            }
            if (callback !== null && callback !== undefined){
                callback();
            }
        }
    });
}
function leftDummies(fade = true, callback = null){
    $(".dummies.right > .dummy, .dummies.top > .dummy").removeAttr("style");
    // tooglePanelBlur(fade);
    valto = 0;
    if (fade){
        valto = "100vw";
    }
    else {
        currentDummy = null;
    }
    anime({
        targets: ".dummies.left > .dummy",
        left: valto,
        duration: adur(300),
        delay: anime.stagger(adur(100)),
        easing: easeOut,
        complete: function(){
            if (fade){
                currentDummy = leftDummies;
            }
            if (callback !== null && callback !== undefined){
                callback();
            }
        }
    });
}
function rightDummies(fade = true, callback = null){
    $(".dummies.left > .dummy, .dummies.top > .dummy").removeAttr("style");
    // tooglePanelBlur(fade);
    valto = 0;
    if (fade){
        valto = "-100vw";
    }
    else {
        currentDummy = null;
    }
    anime({
        targets: ".dummies.right > .dummy",
        left: valto,
        duration: adur(300),
        delay: anime.stagger(adur(100)),
        easing: easeOut,
        complete: function(){
            if (fade){
                currentDummy = rightDummies;
            }
            if (callback !== null && callback !== undefined){
                callback();
            }
        }
    });
}


function showModalByData(title, data, callback = null){
    $(".modal").removeClass("active");
    $("#general-modal .content .title").html(title);
    $("#general-modal .content .data").html(data);

    showModal($("#general-modal"), callback);
}
function showModal(modal, callback = null){
    if (currentModal !== null && currentModal !== undefined){
        return;
    }
    currentModal = modal;

    if (currentDummy !== null && currentDummy !== undefined){
        modal.addClass("active");
    }
    else {
        randomElement(dummies)(true, function(){
            modal.addClass("active");
            if (callback !== null && callback !== undefined){
                callback();
            }
        });
    }
}
function hideModal(modal, callback = null){
    currentModal = null;
    modal.removeClass("active");
    if (currentDummy !== null && currentDummy !== undefined){
        currentDummy(false, callback);
    }
}


function showMessage(data, messageOnly = true){
    if (messageOnly){
        $("#message-box").removeClass("error warning");
    }
    $("#message-box .content").html(data);
    $("#message-box").addClass("active");
}
function hideMessage(){
    $("#message-box").removeClass("active");
}
function showWarning(data){
    $("#message-box").removeClass("error");
    $("#message-box").addClass("warning");
    showMessage(data, false);
}
function showError(data){
    $("#message-box").removeClass("warning");
    $("#message-box").addClass("error");
    showMessage(data, false);
}
function showCountdownMessage(data, dietime = 500){
    $("#message-box .close").css({
        display: "none"
    });
    showMessage(data, true);

    setTimeout(function(){
        hideMessage();

        setTimeout(function(){
            $("#message-box .close").removeAttr("style");
        }, 300);
    }, dietime)
}


function onEnter(element, callback){
    element.keydown(function(event){
        var kcode = (event.keyCode ? event.keyCode : event.which);
        if (kcode == 13){
            callback();
        }
    });
}


function tgbHandler(){
    var can = ($(this).attr("checked")) ? false : true;
    $(this).attr("checked", can);
}


function getAllSettings(callback = null){
    return $.ajax({
        url: "settings.php",
        method: "POST",
        data: "get=all",
        dataType: "json",
        success: function(data){
            if (data.result){
                if (callback !== null && callback !== undefined){
                    callback(data.settings);
                }
            }
            else {
                if ("msg" in data){
                    console.warn(data.msg);
                }
                this.error();
            }
        },
        error: function(){
            $(".do-settings").prop("disabled", false);
        }
    });
}
function applySettings(settings){
    // TypeID => (InputType, расшифровка, значение, name) 
    // InputType: (number - Числовой выбор), (checkbox - Чекбокс), (text - Иное, текст) 
    $.each(settings, function(key, value){
        if (value[3] == "allowAnim"){
            if (value[2] != '0'){
                setAllowAnimations(true);
            }
            else {
                setAllowAnimations(false);
            }
        }
    });
}


function getRoomsList(callback){
    $.ajax({
        url: "rooms.php",
        method: "POST",
        data: `op=getall`,
        dataType: "json",
        success: function(data){
            if (data.result){
                if (callback !== null && callback !== undefined){
                    callback(data.list);
                }
            }
            else {
                if ("msg" in data){
                    showError(data.msg);
                }
                this.error();
            }
        },
        error: function(){
            $("#room-list .list-refresh").prop("disabled", false);
        }
    });
}

function getUserRole(callback){
    return $.ajax({
        url: "users_roles.php",
        method: "POST",
        data: "op=get",
        dataType: "json",
        success: function(data){
            if (data.result){
                if (callback !== null && callback !== undefined){
                    callback(data.role);
                }
            }
            else {
                if ("msg" in data){
                    showError(data.msg);
                }
                this.error();
            }
        },
        error: function(){
        }
    });
}
function setExtraMenuAction(role = null){
    if (role == null || role == undefined){
        role = window.user.role;
    }
    if (window.user.role > 0){
        $("#q-edit").addClass("active");
    }
    else {
        $("#q-edit").removeClass("active");
    }
}

function getFormattedID(data){
    return "#"+("0000".substr(String(data.length))) + data;
}

function getAllFriends(callback){
    return $.ajax({
        url: "users.php",
        method: "POST",
        data: "op=getfriends",
        dataType: "json",
        success: function(data){
            if (data.result){
                if (callback !== null && callback !== undefined){
                    callback(data.list);
                }
            }
            else {
                if ("msg" in data){
                    showCountdownMessage(data.msg, 1000);
                }
                this.error();
            }
        },
        error: function(){
        }
    });
}

function getAllNotifications(callback){
    return $.ajax({
        url: "notifications.php",
        method: "POST",
        data: "op=getall",
        dataType: "json",
        success: function(data){
            if (data.result){
                if (callback !== null && callback !== undefined){
                    callback(data.list);
                }
            }
            else {
                if ("msg" in data){
                    showCountdownMessage(data.msg, 1000);
                }
                this.error();
            }
        },
        error: function(){
        }
    });
}


function getAllTopics(callback){
    return $.ajax({
        url: "topics.php",
        method: "POST",
        data: "op=getall",
        dataType: "json",
        success: function(data){
            if (data.result){
                if (callback !== null && callback !== undefined){
                    callback(data.topics);
                }
            }
            else {
                if ("msg" in data){
                    showCountdownMessage(data.msg, 1000);
                }
                this.error();
            }
        },
        error: function(){
        }
    });
}


//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
    checkMobile();
    ws.w = $(".panel").width();
    ws.h = $(".panel").height();
    checkWindowSize(ws.w, ws.h);

    $(window).resize(function(){
        ws.w = $(".panel").width();
        ws.h = $(".panel").height();
        checkWindowSize(ws.w, ws.h);
    });

    $(".tgb1").click(tgbHandler);

    $("#mobile-warning-ignore").click(function(){
        setAllowAnimations(false);
        $("#mobile-warning").css({visibility: "hidden"});
    });

    $(`.modal > .container > .modal-close,
        .modal > .container .content .close`).click(function(){
        hideModal($(this).closest(".modal"));
    });


    // Привязка событий к клавишам
    // В дальнейшем развить данную идею
    $(document).keydown(function(event){
        // ESC
        if (event.which == 27){
            if ($(".modal").hasClass("active")){
                hideMessage();
            }
            if ($(".modal").hasClass("active")){
                $(".modal").removeClass("active");
                if (currentDummy !== null && currentDummy !== undefined){
                    currentDummy(false);
                }
                currentModal = null;
            }
            if (flags.inSelfUser){
                $("#self-user .left").click();
            }
            if ($("#message-box").hasClass("active")){
                hideMessage();
            }
        }
    });


    $("#message-box .close").click(hideMessage);
});
