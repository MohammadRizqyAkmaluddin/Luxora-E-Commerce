const navbar = document.getElementById('header');

let popup = document.getElementById("popup");
function openPopup(){
    popup.classList.add("open-popup");
    navbar.style.display = "none";
    document.body.style.overflow = "hidden";
}
function closePopup(){
    popup.classList.remove("open-popup"); 
    navbar.style.display = "flex";
    document.body.style.overflow = "auto"; 
}

let popupp = document.getElementById("popupp")
function openPopupp(){
    popupp.classList.add("open-popupp");
    navbar.style.display = "none";
    document.body.style.overflow = "hidden";
}
function closePopupp(){
    popupp.classList.remove("open-popupp"); 
    navbar.style.display = "flex";
    document.body.style.overflow = "auto";
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}