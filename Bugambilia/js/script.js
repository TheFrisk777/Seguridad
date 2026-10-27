const btnSignIn =document.getElementById('sign-in');
const btnSingUp= document.getElementById('sign-up');
const formRegister= document.querySelector(".register");
const formLogin= document.querySelector(".login");

btnSignIn.addEventListener("click", e=> {
   formRegister.classList.add("hide");
   formLogin.classList.remove("hide");
});

btnSingUp.addEventListener("click", e=> {
   formLogin.classList.add("hide");
   formRegister.classList.remove("hide");
});





