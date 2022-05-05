document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll("#control-box > button").forEach(function(value){
        value.addEventListener("click", function(){
            let entity = document.querySelector("a-marker[value='1'] a-entity");
            entity.setAttribute("gltf-model", this.getAttribute("src"));
            entity.setAttribute("scale", this.getAttribute("scale"));
        })
    })
})