let canvas = null;
let ctx = null;
let arrowImg = null;
let theta = null;
let arrows = [];
let arrowCount = randomInt(150, 200);


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
    //Защита от переполнения
    var protection = 1000;
    while (arrows.length < arrowCount && counter < protection) {
        // scale = 1; //Истинный размер
        scale = randomFloat(0.2, 0.8);
        var arrow = {
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
            //Чем больше distance, тем ближе возможность наложения стрелок
            if (distance*1.5 < (arrow.size.w + previousArrow.size.w)) {
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
            // redraw({
            //     x: mouse.x,
            //     y: mouse.y
            // });
        }
    });
    
    arrowImg = new Image;
    arrowImg.src = "assets/img/arrow-colored.png";
    arrowImg.onload = function(){
        // initArrows();
        // redraw({
        //     x: 0,
        //     y:0
        // });

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
}