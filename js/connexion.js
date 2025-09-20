document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('loginForm'),addEventListener('submit', function(event){
        var email = document.getElementById('email').value;
        var mot_de_passe = document.getElementById('mot_de_passe').value;

        if (!email || !mot_de_passe){
            document.getElementById('error').innerHTML = "Veuiller remplir tous les champs.";
            event.preventDefault();
        }
    });
}); 