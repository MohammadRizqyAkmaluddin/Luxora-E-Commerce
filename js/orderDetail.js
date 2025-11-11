const navbar = document.getElementById('headerDetail');
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