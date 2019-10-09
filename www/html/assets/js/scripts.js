
document.getElementById("delete").addEventListener("click", function(e) {
    if (confirm("Etes-vous sur de vouloir supprimer ce produit ?")) {
        console.log('ok');
    }else{
        e.preventDefault();
    }
});

document.getElementsByClassName("deleted").addEventListener("click", function (e) {
    if (confirm("Etes-vous sur de vouloir supprimer ce produit de votre panier ?")) {
        console.log('ok');
    }else{
        e.preventDefault();
    }
})