const navbar = document.getElementById('header');

let popup = document.getElementById("popup")
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




$(document).ready(function() {
$("#paymentTypeID").change(function() {
    let selectedOption = $(this).find(":selected"); 
    let adminFee = selectedOption.data("adminfee") || 0; 
    let paymentIcon = selectedOption.data("icon");

            $("#adminFee").text("$" + parseFloat(adminFee).toFixed(2));

            if (paymentIcon) {
                $("#paymentIcon").attr("src", paymentIcon).show();
            } else {
                $("#paymentIcon").hide();
            }
            $("#payment-info").show();
        });
    });