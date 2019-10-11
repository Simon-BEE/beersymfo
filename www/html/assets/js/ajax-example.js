function onClickLink (event){
    event.preventDefault();
    const url = this.href; // this = element declencheur de l'event
    const spanCount = this.querySelector('span.js-likes');
    const icon = this.querySelector('i');

    $.get(url, {}, function(data){
        
        if (data.code == 200) {
            spanCount.innerHTML = data.likes;
            if (icon.classList.contains('fas')) {
                icon.classList.replace('fas', 'far');
            }else{
                icon.classList.replace('far', 'fas');
            }
        }else if (data.code == 403) {
            alert(data.message)
        }else{
            alert('Erreur');
        }
    })
}

const classes = document.getElementsByClassName('js-like');
console.log(classes);

for (let i = 0; i <= classes.length; i++) {
    classes[i].addEventListener('click', onClickLink);
}