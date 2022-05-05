let canvas = null;
let ctx = null;
let arrowImg = null;
let theta = null;
let arrows = [];
let arrowCount = randomInt(150, 200);
// flags.inAccountRestore = false; 
// flags.inAccountCreate = false; 

class Arrow {
    constructor(data){
        this.x = data.x;
        this.y = data.y;
        this.size = data.size;
        this.angle = data.angle;
        this.tracking = randomInt(0, 2) == 1 ? -1 : 1;
    }
    draw(crds){
        theta = this.tracking*(this.angle + Math.atan2(crds.y - this.y, crds.x - this.x)) + Math.PI/2;
        
        ctx.save();
        ctx.translate(this.x, this.y);
        ctx.rotate(theta);
        ctx.drawImage(arrowImg, -this.size.w/2, -this.size.h/2, this.size.w, this.size.h);
        ctx.restore();
    }
}


function redraw(crds){
    ctx.clearRect(0,0,ctx.canvas.width, ctx.canvas.height);
    for (let i = 0; i < arrows.length; i++){
        arrows[i].draw(crds);
    }
}
function initArrows(){
    var counter = 0;
    var protection = 1000;
    while (arrows.length < arrowCount && counter < protection) {
        scale = randomFloat(0.3, 0.9);
        let arrow = {
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: {
                w: arrowImg.width*scale,
                h: arrowImg.height*scale
            },
            angle: randomInt(0, 360)
        };

        overlapping = false;
        for (let i = 0; i < arrows.length; i++){
            let previousArrow = arrows[i];
            let dx = arrow.x - previousArrow.x;
            let dy = arrow.y - previousArrow.y;
            let distance = Math.sqrt(dx * dx + dy * dy);
            if (distance < (arrow.size.w + previousArrow.size.w)) {
                overlapping = true;
                break;
            }
        }

        if (!overlapping){
            arrows.push(new Arrow(arrow));
        }
        counter++;
    }
}
function killArrows(){
    arrows.length = 0;
}

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
                redraw({
                    x: mouse.x,
                    y: mouse.y
                });
                $("#arrows").addClass("active");
            }
        })
}

function startLoginAnim(gotoRoom = false){
    setupLoginPresets();

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

    valto = "-100vh";
    if (gotoRoom){
        valto = "0";
    }
    anime({
        targets: "body",
        top: valto,
        duration: adur(1200),
        easing: "easeInOutCubic",
        complete: function(){
            killArrows();
            $("#auth, #chaotic-logo, #authparent, #intro > .content").removeAttr("style");
            $("#auth, #arrows").removeClass("active");
            $("#do-login, #auth .inputs input").prop("disabled", false);
        }
    })

    setTimeout(startMenu, adur(500));
}


function setUserAvatar(){
    $.ajax({
        url: "avatars.php",
        method: "GET",
        data: "op=get",
        dataType: "json",
        success: function(data){
            if (data.result){
                $("#self-user .u-avatar").attr("src", data.path);
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
}

function handleLoginData(data){
    $("#create-account .field input").val("");
    $("#auth .inputs input").val("");
    window.user.nick = data.nick;
    window.user.user_id = data.user_id;
    window.user.role = data.role;
    // window.user.login = data.login;
    // getUserRole(setExtraMenuAction);
}

function setupLoginPresets(){
    setUserAvatar();
    setExtraMenuAction();
    getAllSettings(function(data){
        applySettings(data);
    });

    $("#room-list .list-refresh").click();
    // startGeneralListener();

    $("#self-user .u-nick").html(window.user.nick);
    var uid = getFormattedID(window.user.user_id);
    $("#self-user .u-id").html(uid);
}

//-------------------------------------------------------------------------------------------------------
// DOCUMENT READY EVENT
//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
    canvas = $("#arrows").get(0);
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    ctx = canvas.getContext("2d");

    $(window).resize(function(){
        if (window.currentStage == 0){
            ws.w = $(".panel").width();
            ws.h = $(".panel").height();
            canvas.width = ws.w;
            canvas.height = ws.h;
            killArrows();
            initArrows();
        }
    });

    $("#intro").mousemove(function(event){
        var pos = $(this).offset();
        mouse.x = event.pageX - pos.left;
        mouse.y = event.pageY - pos.top;
        if ($("#arrows").hasClass("active")){
            redraw({
                x: mouse.x,
                y: mouse.y
            });
        }
    });

    $("#do-login").click(function(){
        if (!$(this).is(':disabled')){
            $(this).prop("disabled", true);
            $("#auth .inputs input").prop("disabled", true);

            var data = {
                email: $("#auth input[name=email]").val(),
                pass: $("#auth input[name=pass]").val()
            }

            try {
                if (!data.email){
                    throw new Error("Эл. Почта");
                }
                else if (!data.pass){
                    throw new Error("Пароль");
                }
            }
            catch (error){
                $("#auth-fail-msg").html(`Заполните поле "${error.message}"`);
                $("#do-login, #auth .inputs input").prop("disabled", false);
                return;
            }
            $("#auth-fail-msg").html("");

            $.ajax({
                url: "login.php",
                method: "POST",
                data: data,
                dataType: "json",
                success: function(data){
                    if (data.result){
                        handleLoginData(data);
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
        }
    });
    onEnter($("#auth input[name=email]"), function(){
        $("#auth input[name=pass]").focus();
    });
    onEnter($("#auth input[name=pass]"), function(){
        $("#do-login").click();
    });

    $("#do-create-account").click(function(){
        if (!$(this).is(':disabled')){
            $("#do-create-account, #create-account input").prop("disabled", true);

            var data = {
                nick: $("#create-account input[name=nick]").val(),
                email: $("#create-account input[name=email]").val(),
                pass: $("#create-account input[name=pass]").val()
            }
            var pass2 = $("#create-account input[name=pass2]").val();

            try {
                if (!data.nick){
                    throw new Error("Никнейм");
                }
                else if (!data.email){
                    throw new Error("Эл. Почта");
                }
                else if (!data.pass){
                    throw new Error("Пароль");
                }
                else if (!pass2){
                    throw new Error("Повторение пароля");
                }
            }
            catch (error){
                $("#create-account-fail-msg").html(`Заполните поле "${error.message}"`);
                $("#do-create-account, #create-account input").prop("disabled", false);
                return;
            }

            if (data.pass != pass2){
                $("#create-account .both-required").css({
                    background: "#ff00002e"
                })
                anime({
                    targets: "#create-account .both-required",
                    rotateX: [
                        {value: "25deg"},
                        {value: "-30deg"},
                        {value: 0}
                    ],
                    duration: adur(800),
                    easing: ease,
                    complete: function(){
                        $("#create-account .both-required").removeAttr("style");
                    }
                });

                $("#create-account-fail-msg").html("Пароли не совпадают");
                $("#do-create-account, #create-account input").prop("disabled", false);
                return;
            }  

            $("#create-account-fail-msg").html("");
            $("#do-create-account, #create-account input").prop("disabled", false);
            $.ajax({
                url: "signup.php",
                method: "POST",
                data: data,
                dataType: "json",
                success: function(data){
                    if (data.result){
                        $("#do-login, #auth .inputs input").prop("disabled", true);
                        handleLoginData(data);
                        hideModal($("#create-account"), startLoginAnim);
                    }
                    else {
                        if ("msg" in data){
                            $("#create-account-fail-msg").html(data.msg);
                        }
                        this.error();
                    }
                },
                error: function(){
                    $("#do-create-account, #create-account input").prop("disabled", false);
                }
            });
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
        if (!$(this).is(':disabled')){
            showModal($("#temp-unav"));
            // flags.inAccountRestore = !flags.inAccountRestore;
        }
    });

    $("#extr-create").click(function(){
        if (!$(this).is(':disabled')){
            showModal($("#create-account"));
            // flags.inAccountCreate = !flags.inAccountCreate;
        }
    });

    arrowImg = new Image;
    arrowImg.src = "assets/img/arrow-colored.png";
    arrowImg.onload = function(){
        initArrows();
        redraw({
            x: 0,
            y:0
        });

        if (window.currentStage == 0){
            setTimeout(function(){
                startIntro();
            }, adur(50));   
        }
    }
    arrowImg.onerror = function(){
        if (window.currentStage == 0){
            setTimeout(function(){
                startIntro();
            }, adur(50));   
        }
    }

    if (window.currentStage > 0){
        setTimeout(function(){
            startLoginAnim(Boolean(window.currentStage == 2));
        }, adur(50));
    }
});