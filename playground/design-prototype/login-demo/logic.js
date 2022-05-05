let canvas = null;
let ctx = null;
let arrowImg = null;
let theta = null;
let arrows = [];
let arrowCount = randomInt(150, 300);
let mouse = {
    x: 0,
    y: 0
}

//-------------------------------------------------------------------------------------------------------

// Objs
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

//Funcs
function randomInt(min, max){
    return min + Math.floor(Math.random() * (max - min));
}
function randomFloat(min, max) {
    return min + (Math.random() * (max - min));
}

function redraw(crds){
    ctx.clearRect(0,0,ctx.canvas.width, ctx.canvas.height);
    for (let i = 0; i < arrows.length; i++){
        arrows[i].draw(crds);
    }
}
function initArrows(){
    var counter = 0;
    var protection = 10000;
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


function startIntroAnim_OLD(){
    var spring = [250, 15];

    $("#chaotic-logo")
        .velocity({
            width: "65vw",
            top: "-6%"
        }, 
        {
            duration: 200,
            easing: "ease-out",
            complete: function() {
                $("#login-substrate").velocity({
                    transform: ["scaleX(1)", "scaleX(0)"],
                    opacity: "0.46"
                }, {
                    duration: 1200,
                    easing: spring,
                    delay: 150
                });
                setTimeout(function(){
                    $("#login").addClass("active");
                }, 150);
            }
        })

        .velocity({
            width: "20vw",
            top: "-20%"
        }, 
        {
            duration: 400,
            easing: "ease-in",
            complete: function(){
                redraw({
                    x: mouse.x,
                    y: mouse.y
                });
                $("#arrows").addClass("active");
            }
        });
}



function startIntroAnim(){
    var spring = [250, 15];

    $("#loginparent").velocity({
        height: "40%"
    }, {
        duration: 600,
        easing: "linear",
        complete: function() {
        }
    })

    $("#chaotic-logo")
        .velocity({
            width: "65vw"
        }, 
        {
            duration: 200,
            easing: "ease",
            complete: function() {
                $("#login-substrate").velocity({
                    transform: ["scaleX(1)", "scaleX(0)"],
                    opacity: "0.46"
                }, {
                    duration: 1200,
                    easing: spring,
                    delay: 150
                });
                setTimeout(function(){
                    $("#login").addClass("active");
                }, 150);
            }
        })

        .velocity({
            width: "20vw"
        }, 
        {
            duration: 400,
            easing: "ease-out",
            complete: function(){
                redraw({
                    x: mouse.x,
                    y: mouse.y
                });
                $("#arrows").addClass("active");
            }
        });
}

$(document).ready(function(){
    canvas = $("#arrows").get(0);
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    ctx = canvas.getContext("2d");

    $(window).resize(function(){
        canvas.width = $(this).width();
        canvas.height = $(this).height();
        killArrows();
        initArrows();
    });
    $(document).mousemove(function(e){
        mouse.x = e.pageX;
        mouse.y = e.pageY;
        if ($("#arrows").hasClass("active")){
            redraw({
                x: mouse.x,
                y: mouse.y
            });
        }
    });

    arrowImg = new Image;
    arrowImg.src = "../src/simple-arrow-colored.png";
    arrowImg.onload = function(){
        initArrows();
        redraw({
            x: 0,
            y:0
        });
    }

    setTimeout(function(){
        startIntroAnim();
    }, 400);

    // $("#chaotic-logo").one("click", function(){
    //     startIntroAnim2();
    // })
});


