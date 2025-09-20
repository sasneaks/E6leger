document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('signupForm'),addEventListener('submit', function(event){
        var nom = document.getElementById('nom').value;
        var email = document.getElementById('email').value;
        var mot_de_passe = document.getElementById('mot_de_passe').value;

        if (!nom || !email || !mot_de_passe){
            document.getElementById('error').innerHTML = "Veuiller remplir tous les champs.";
            event.preventDefault();
        }
    });
}); 