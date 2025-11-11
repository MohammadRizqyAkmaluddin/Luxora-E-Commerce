const signUpButton = document.getElementById('signUpButton');
const signInButton = document.getElementById('signInButton');
const signUpForm = document.getElementById('signUp');
const signInForm = document.getElementById('signIn');

const signUpSellerButton = document.getElementById('signUpSellerButton');
const signInSellerButton = document.getElementById('signInSellerButton');
const signUpSellerButton2 = document.getElementById('signUpSellerButton2');
const signInSellerButton2 = document.getElementById('signInSellerButton2');
const signUpSellerForm = document.getElementById('signUpSeller');
const signInSellerForm = document.getElementById('signInSeller');

const signInBuyer = document.getElementById('signInBuyer');
const signUpBuyer = document.getElementById('signUpBuyer');


document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("form") === "register") {
        document.getElementById("signInForm").style.display = "none";
        document.getElementById("signUpForm").style.display = "block";
    }
});

// in buyer
signUpButton.addEventListener('click', function(){
    signInForm.style.display = "none";
    signUpForm.style.display = "block";
    signUpSellerForm.style.display = "none";
    signInSellerForm.style.display = "none";
})
signInButton.addEventListener('click', function(){
    signUpForm.style.display = "none";
    signInForm.style.display = "block";
    signUpSellerForm.style.display = "none";
    signInSellerForm.style.display = "none";
})
// in seller
signUpSellerButton2.addEventListener('click', function(){
    signInForm.style.display = "none";
    signUpForm.style.display = "none";
    signUpSellerForm.style.display = "block";
    signInSellerForm.style.display = "none";
})
signInSellerButton2.addEventListener('click', function(){
    signInForm.style.display = "none";
    signUpForm.style.display = "none";
    signUpSellerForm.style.display = "none";
    signInSellerForm.style.display = "block";
})

// to seller
signUpSellerButton.addEventListener('click', function(){
    signInForm.style.display = "none";
    signUpForm.style.display = "none";
    signUpSellerForm.style.display = "block";
    signInSellerForm.style.display = "none";
})
signInSellerButton.addEventListener('click', function(){
    signInForm.style.display = "none";
    signUpForm.style.display = "none";
    signUpSellerForm.style.display = "none";
    signInSellerForm.style.display = "block";
})
// to buyer
signInBuyer.addEventListener('click', function(){
    signInForm.style.display = "block";
    signUpForm.style.display = "none";
    signUpSellerForm.style.display = "none";
    signInSellerForm.style.display = "none";
})
signUpBuyer.addEventListener('click', function(){
    signInForm.style.display = "none";
    signUpForm.style.display = "block";
    signUpSellerForm.style.display = "none";
    signInSellerForm.style.display = "none";
})



const editProfile = document.getElementById('edit-profile');
const editEmail = document.getElementById('edit-email');
const editPassword = document.getElementById('edit-password');

const editProfileForm = document.getElementById('edit-profile-form');
const editEmailForm = document.getElementById('edit-email-form');
const editPasswordForm = document.getElementById('edit-password-form');

editProfile.addEventListener('click', function(){
    editProfileForm.style.display = "block";
    editEmailForm.style.display = "none";
    editPasswordForm.style.display = "none";
    
})
editEmail.addEventListener('click', function(){
    editProfileForm.style.display = "none";
    editEmailForm.style.display = "block";
    editPasswordForm.style.display = "none";
    
})
editPassword.addEventListener('click', function(){
    editProfileForm.style.display = "none";
    editEmailForm.style.display = "none";
    editPasswordForm.style.display = "block";
    
})



