//Используйте Промисы $.ajax ($.get/$.post) вместо callbacks!
"use strict";

let flags = {
    mobileVersion: false,
    allowAnimations: true
};
// let flags.mobileVersion = false;
let currentModal = null;
let mouse = {
    x: 0,
    y: 0
};
let ws = {
    w: 0,
    h: 0
};
// EASING
let spring1 = "spring(1, 80, 10, 0)";
let elasticOut1 = "easeOutElastic(1.5, 0.5)";
let elasticIn1 = "easeInElastic(2, 2)";
let ease = "cubicBezier(0.250, 0.100, 0.250, 1.000)";
let easeOut = "cubicBezier(0.000, 0.000, 0.580, 1.000)";
// Пользователь
window.user = {
    settings_data: null,
    stage: 0
};
window.currentRoom = {
    // id: -1,
    // creator_id: -1,
    data: null,
    topics_list: null,
    users_list: null,
    clear: function(){
        this.id           = -1;
        this.creator_id   = -1;
        this.data         = null;
        this.topics_list  = null;
        this.users_list   = null;
    },
    setup: function(responsed){
        this.id          = responsed.room_id;
        this.data        = responsed.room_data;
        this.topics_list = responsed.topics_list;
        this.users_list  = responsed.users_list;
        this.creator_id  = this.data.creator_id;
    }
}
anime.suspendWhenDocumentHidden = false;


let dummies = {
    current: null,
    isOpen: function(){
        return !isNull(this.current);
    },
    close: function(){
        var promise = $.Deferred();
        if (this.isOpen()){
            this.current(false).then(promise.resolve, promise.reject);
        }
        else {
            promise.resolve();
        }
        return promise;
    },
    general: function(caller, fade, selector, value){
        $(`.dummies:not(.${selector}) > .dummy`).removeAttr("style");
        dummies.current = fade ? caller : null;
        return anime({
            targets: `.dummies.${selector} > .dummy`,
            [selector]: fade ? value : 0,
            duration: adur(300),
            delay: anime.stagger(adur(100)),
            easing: easeOut
        }).finished;
    },
    top: function(fade = true){
        return dummies.general(dummies.top, fade, "top", "100vh");
    },
    left: function(fade = true){
        return dummies.general(dummies.left, fade, "left", "100vw");
    },
    right: function(fade = true){
        return dummies.general(dummies.right, fade, "right", "100vw");
    },
    random: function(fade = true){
        return randomElement([this.top, this.left, this.right])(fade);
    }
}

let generalModal = {
    promise: null,
    element: null,
    options: null,
    loadingState: function(state = true){
        this.element.removeClass("fail");
        state = state ? "loading" : "fail";
        this.element.find(".loading-ico")
            .removeClass()
            .addClass(`loading-ico ${state}`);
        this.element.addClass(state);
        // if (loading){
        //     this.element.find(".loading-ico").addClass("loading");
        //     return;
        // }
        // this.element.find(".loading-ico").addClass("fail");
        return this.promise;
    },
    loading: function(options = {}){
        options = Object.assign({
            title: "Загрузка",
            class: "loading",
            data: ""
        }, options);
        this.loadingState();
        return this.confirm(options);
    },
    alert: function(options = {}){
        options = Object.assign({
            title: "Сообщение",
            class: "alert"
        }, options);
        return this.confirm(options);
    },
    confirm: function(options = {}){
        if (currentModal){
            //мы теперь swapModal
            // return $.Deferred();
        }
        this.reset();
        options = Object.assign({
            beforeStart:    null,
            title:          "Подтверждение",
            data:           "Сообщение",
            class:          "confirm",
            apply:          "Ладно",
            cancel:         "Отмена",
            applyHandler:   null,
            cancelHandler:  null
        }, options);
        this.set(options, true);

        this.element.find(".modal-close, .actions > .cancel")
            .unbind("click")
            .click(function(){
                this.cancel();
            }.bind(this));
        this.element.find(".actions > .apply")
            .unbind("click")
            .click(function(){
                this.apply();
            }.bind(this))

        swapModal($("#general-modal"), options.beforeStart);
        return this.promise;
    },
    set: function(options, ignoreCurrentOptions = false){
        if (!ignoreCurrentOptions){
            options = Object.assign(this.options, options);
        }
        this.options = options;

        let activeClass = this.element.hasClass("active") ? "active" : "";
        this.element.removeClass().addClass(`modal ${activeClass}`);
        if (options.class){
            this.element.addClass(options.class);
        }

        this.element.find(".title").html(options.title);
        this.element.find(".field.modal-data").html(options.data);
        this.element.find(".actions > .apply").val(options.apply);
        this.element.find(".actions > .cancel").val(options.cancel);
        return this;
    },
    cancel: function(callback = null){
        if (this.options.cancelHandler){
            return this.options.applyHandler();
        }
        else {
            if (this.promise){
                this.promise.reject();
                hideModal(callback);
            }
            return this;
        }
    },
    apply: function(callback = null){
        if (this.options.applyHandler){
            return this.options.applyHandler();
        }
        else {
            if (this.promise){
                this.promise.resolve();
                hideModal(callback);
            }
            return this;
        }
    },
    reset: function(){
        this.promise = $.Deferred();
    }
}

function setAllowAnimations(value = true){
    if (value && value !== 0){
        $(".parallax-bg > .shapes").addClass("active");
        $("body").removeAttr("style");
        // $(".parallax-bg > .shapes:nth-child(1)").css({
        //     animation: "200s ease-in-out 0s infinite normal none running pp-bg1"
        // });
        // $(".parallax-bg > .shapes:nth-child(2)").css({
        //     animation: "190s ease-in-out 0s infinite normal none running pp-bg2"
        // });
        // $(".parallax-bg > .shapes:nth-child(3)").css({
        //     animation: "170s ease-in-out 0s infinite normal none running pp-bg3"
        // });
    }
    else {
        $(".parallax-bg > .shapes").removeClass("active");
        $("body").attr("style", "--adur: 0;");
        // $(".parallax-bg > .shapes").css({
        //     animation: "none"
        // });
    }
    flags.allowAnimations = value;
}

function adur(reqvalue){
    return (flags.allowAnimations) ? reqvalue : 0;
}

function sleep(time){
    return new Promise(resolve => setTimeout(resolve, time));
}
async function stagger(list, time, callback, dir=true){
    const ds = dir ? 1 : -1;
    // const range = dir ? 0 : list.length;
    for (var i = dir ? 0 : list.length-1; 
            ((dir && i < list.length) || (!dir && i >= 0));
            i += ds
        ){
        await sleep(adur(time));
        callback(list[i]);
    } 
}

function isNull(value){
    return (value === null || value === undefined);
}

function randomInt(min, max){
    return min + Math.floor(Math.random() * (max - min));
}
function randomFloat(min, max) {
    return min + (Math.random() * (max - min));
}
function randomIndex(arr) {
    return randomInt(0, arr.length);
}
function randomElement(arr) {
    return arr[randomIndex(arr)];
}

function checkWindowSize(ws){
    if (ws.h > ws.w){
        $("#rotate-warning").css({visibility: "visible"});
        $("#rotate-warning > img").css({animation: "20s ease-in-out 0s infinite normal none running weak-wiggle"});
    }
    else {
        $("#rotate-warning").css({visibility: "hidden"});
        $("#rotate-warning > img").css({animation: "none"});
    }
}

function checkMobile(){
    return true; //TODO: Убрать после пересдачи
    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
        flags.mobileVersion = true;
        $("#mobile-warning").css({visibility: "visible"});
    }
    return flags.mobileVersion;
};


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



// function showModalByData(title, data, callback = null){
//     $(".modal").removeClass("active");
//     $("#general-modal .content .title").html(title);
//     $("#general-modal .content .data").html(data);

//     return showModal($("#general-modal"), callback);
// }
function showModal(modal, callback = null){
    if (currentModal){
        // if (callback){
        //     return callback();
        // }
        return;
    }
    currentModal = modal;
    if (dummies.isOpen()){
        modal.addClass("active");
    }
    else {
        dummies.random(true).then(function(){
            modal.addClass("active");
            if (callback !== null && callback !== undefined){
                callback();
            }
        })
    }
}
function hideModal(callback = null){
    if (!currentModal){
        // if (callback){
        //     return callback();
        // }
        return;
    }
    currentModal.removeClass("active");
    currentModal = null;
    if (dummies.isOpen){
        dummies.close().then(callback);
    }
}
function swapModal(modal, callback = null){
    if (!currentModal){
        return showModal(modal, callback);
    }
    currentModal.removeClass("active");
    currentModal = modal;
    modal.addClass("active");
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
    sleep(adur(350)).then(function(){
        $("#message-box .content").html("");
    })
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

function showCountdownWarning(data, dietime = 500){
    $("#message-box").removeClass("error");
    $("#message-box").addClass("warning");
    showCountdownMessage(data, dietime, false);
}
function showCountdownError(data, dietime = 500){
    $("#message-box").removeClass("warning");
    $("#message-box").addClass("error");
    showCountdownMessage(data, dietime, false);
}
function showCountdownMessage(data, dietime = 500, messageOnly = true){
    $("#message-box .close").css({
        display: "none"
    });
    showMessage(data, messageOnly);

    setTimeout(function(){
        hideMessage();

        setTimeout(function(){
            $("#message-box .close").removeAttr("style");
        }, 300);
    }, dietime);
}

function bodygoto(state){
    var promise = $.Deferred();
    $("body").attr("class", `active f{state}`);
    //анимация body занимает 1200мс
    if (flags.allowAnimations){
        state += " active";
    }
    setTimeout(function(){
        $("body").removeClass("active");
        promise.resolve();
    }, adur(1210));

    $("body").attr("class", state);
    return promise;
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


// function getAllSettings(){
//     var promise = $.Deferred();
//     $.ajax({
//         url: "settings.php",
//         method: "POST",
//         data: "get=all",
//         dataType: "json",
//         success: function(data){
//             if (!data.result){
//                 if ("msg" in data){
//                     showError(data.msg);
//                 }
//                 this.error(data);
//             }
//             promise.resolve(data);
//         },
//         error: function(data){
//             promise.reject(data);
//         }
//     });
//     return promise;
// }
function applySettings(settings){
    // TypeID => (InputType, расшифровка, значение, name) 
    // InputType: (number - Числовой выбор), (checkbox - Чекбокс), (text - Иное, текст) 
    $.each(settings, function(key, value){
        switch (key){
            case "allow_animation":
                const allowflag = value != '0' ? true : false; 
                setAllowAnimations(allowflag);
                break;
        }
        // if (value[3] == "allowAnim"){
        //     if (value[2] != '0'){
        //         setAllowAnimations(true);
        //     }
        //     else {
        //         setAllowAnimations(false);
        //     }
        // }
    });
}

function setExtraMenuAction(role = null){
    if (role == null || role == undefined){
        role = window.user.role;
    }
    if (role > 0){
        $("#q-edit").removeClass("hidden");
    }
    else {
        $("#q-edit").addClass("hidden");
    }
}

function getFormattedID(data){
    return "#"+("0000".substr(String(data).length)) + data;
}

function setUserAvatar(){
    return apiRequest({
        route: "profile",
        op: "get_avatar"
    }, {
       success: function(data){
            if (data.result){
                $("#self-user .u-avatar").attr("src", data.avatar_path);
                // promise.resolve();
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
            // promise.reject();
        } 
    });
    // var promise = $.Deferred();
    // $.ajax({
    //     url: "avatars.php",
    //     data: "op=get",
    //     dataType: "json",
    //     success: function(data){
    //         if (data.result){
    //             $("#self-user .u-avatar").attr("src", data.path);
    //             promise.resolve();
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
    //         promise.reject();
    //     }
    // });
    // return promise;
}
function promisedAjax(setup){
    var promise = $.Deferred();
    setup = Object.assign({
        method: "GET",
        dataType: "json",
        weight: -1,
        resolve: promise.resolve,
        reject: promise.reject,
        success: function(data){
            if (!data.result){
                if ("msg" in data){
                    switch (this.weight) {
                        case -1:
                            console.log(data.msg);
                            break;
                        case 0:
                            showMessage(data.msg);
                            break;
                        case 1:
                            showWarning(data.msg);
                            break;
                        default:
                            showError(data.msg);
                    }
                }
                this.error(data);
            }
            this.resolve(data);
        },
        error: function(data){
            this.reject(data);
        }
    }, setup)

    $.ajax(setup);
    return promise;
}
function apiRequest(data, options={}){
    let setup = Object.assign({
        method: "GET",
        url: "api/",
        data: data,
        weight: 2
    }, options)
    if (setup.method == "POST"){
        setup.data = encodeURIComponent(JSON.stringify(setup.data));
    }
    return promisedAjax(setup);
}
function getRoomsList(){
    return apiRequest({
        route: "room",
        op: "getall"
    });
    // return promisedAjax({
    //     url: "rooms.php",
    //     data: `op=getall`,
    //     weight: 2
    // });
}
function getRoomData(id, weight=1){
    return apiRequest({
        route: "room",
        op: "get",
        room_id: id
    }, {
        weight: weight
    });
    // return promisedAjax({
    //     url: "rooms.php",
    //     data: `op=get&id=${id}`,
    //     weight: weight
    // });
}
//->data.role
function getUserRole(){
    return apiRequest({
        route: "profile",
        op: "get_role"
    });
    // return promisedAjax({
    //     url: "users.php",
    //     data: `op=getrole`,
    //     weight: 2
    // });
}
function getAllFriends(){
    return apiRequest({
        route: "profile",
        op: "get_friends"
    });
    // return promisedAjax({
    //     url: "users.php",
    //     data: `op=getfriends`,
    //     weight: 2
    // });
}
function getAllNotifications(){
    return apiRequest({
        route: "notification",
        op: "getall"
    }, {weight: 1});
    // return promisedAjax({
    //     url: "notifications.php",
    //     data: `op=getall`,
    //     weight: 1
    // });
}
function getAllTopics(){
    return apiRequest({
        route: "topic",
        op: "getall"
    }, {weight: 1});
    // return promisedAjax({
    //     url: "topics.php",
    //     data: `op=getall`,
    //     weight: 1
    // });
}
function getRoomTopics(id, weight=1){
    return apiRequest({
        route: "topic",
        op: "getby_room",
        room_id: id
    }, {
        weight: weight
    });
    // return promisedAjax({
    //     url: "topics.php",
    //     data: `op=getRoom&rid=${id}`,
    //     weight: 1
    // });
}
function getAllMaps(){
    return apiRequest({
        route: "map",
        op: "getall"
    }, {weight: 1});
    // return promisedAjax({
    //     url: "maps.php",
    //     data: `op=getall`,
    //     weight: 1
    // });
}
function getAllRatings(){
    return apiRequest({
        route: "rating",
        op: "getall"
    });
    // return promisedAjax({
    //     url: "rating.php",
    //     data: `op=getall`,
    //     weight: 2
    // });
}
function getAllSettings(){
    return apiRequest({
        route: "profile",
        op: "get_settings"
    }, {weight: 1});
    // return promisedAjax({
    //     url: "settings.php",
    //     data: `op=getall`,
    //     weight: 1
    // });
}
function sendExitRoom(){
    return apiRequest({
        route: "room",
        op: "exit"
    }, {
        method: "POST",
        success: function(data){
            if (data.result){
                if ("msg" in data){
                    showCountdownMessage(data.msg, 1500);
                }
                this.resolve(data);
            }
            else {
                if ("msg" in data){
                    showError(data.msg);
                }
                this.error(data);
            }
        },
    });
    // return promisedAjax({
    //     method: "POST",
    //     url: "rooms.php",
    //     data: `op=exit`,
    //     success: function(data){
    //         if (data.result){
    //             if ("msg" in data){
    //                 showCountdownMessage(data.msg, 1500);
    //             }
    //             this.resolve(data);
    //         }
    //         else {
    //             if ("msg" in data){
    //                 showError(data.msg);
    //             }
    //             this.error(data);
    //         }
    //     },
    // });
}
function escapeEvent(){
    $(".modal.active").each(function(value, i){
        if (!$(this).find(".modal-close").hasClass("hide") 
            && !$(this).hasClass("loading")){
            $(this).find(".modal-close").click();
            // hideModal();
            // if (generalModal.promise){
            //     if (generalModal.promise.state() == "pending"){
            //         generalModal.promise.reject()
            //     }
            // }
        }
    })
    if (flags.inSelfUser){
        $("#self-user .left").click();
    }
    if ($("#message-box").hasClass("active")){
        hideMessage();
    }
}

//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
    checkMobile();
    ws.w = $(".panel").width();
    ws.h = $(".panel").height();
    checkWindowSize(ws);

    $(window).resize(function(){
        ws.w = $(".panel").width();
        ws.h = $(".panel").height();
        checkWindowSize(ws);
    });

    $(".tgb1").click(tgbHandler);

    $("#mobile-warning-ignore").click(function(){
        setAllowAnimations(false);
        $("#mobile-warning").css({visibility: "hidden"});
    });

    $(`.modal > .container .modal-close,
        .modal > .container .content .close`).click(function(){
        hideModal();
    });


    generalModal.element = $("#general-modal");

    //СОЗДАТЬ УНИФ. СПОСОБ МОДУЛЬНОСТИ
    // Привязка событий к клавишам
    // В дальнейшем развить данную идею
    $(document).keydown(function(event){
        // ESC
        if (event.which == 27){
            escapeEvent();
        }
    });
    $(`.dummies.left .dummy:last-child,
       .dummies.right .dummy:last-child,
       .dummies.top .dummy:last-child`).click(function(){
        escapeEvent();
    });


    $("#message-box .close").click(hideMessage);
});
