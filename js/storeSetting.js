let popup = document.getElementById("popup")
function openPopup(){
    popup.classList.add("open-popup");
}
function closePopup(){
    popup.classList.remove("open-popup"); 
}

let popupp = document.getElementById("popupp")
function openPopupp(){
    popupp.classList.add("open-popupp");
    navbar.style.display = "none";
    document.body.style.overflow = "hidden";
}
function closePopupp(){
    popupp.classList.remove("open-popupp"); 
         navbar.style.display = "none";
    document.body.style.overflow = "hidden";
}

function triggerFileInput() {
document.getElementById('fileInput').click();
}

document.getElementById('fileInput').addEventListener('change', function () {
    let formData = new FormData(document.getElementById('uploadForm'));

    fetch('storeSetting.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.text();
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
    });
});
