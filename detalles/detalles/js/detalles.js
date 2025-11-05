window.onload = ()=>{
    let imgGaleria = document.querySelector("#main-product-img")
    let imgs= document.querySelectorAll(".thumb")
    let btnsize = document.querySelectorAll('.size-btn')
    let decBtn = document.getElementById('decrease');
    let incBtn = document.getElementById('increase');
    let num = document.getElementById('quantity');
    let precioBase = 20.00
    
    for(let i=0; i<imgs.length; i++){
        imgs[i].addEventListener('click',(evt)=>{
            console.log(evt.target)
            imgGaleria.src=evt.target.src.replace("thumbs/","")
            imgs.forEach(item=>{
                item.classList.remove('active')
            })
            evt.target.classList.add('active')
        })
    }
    
    for(let i= 0; i<btnsize.length; i++){
        btnsize[i].addEventListener('click',(evt)=>{
            btnsize.forEach(item=>{
                item.classList.remove('active')
            });
            evt.target.classList.add('active');
            console.log("Tamaño seleccionado:", evt.target.textContent);
            
            if(evt.target.textContent === '50ml'){
                precioBase = 18.75
            } else {
                precioBase = 20.00
            }
            calcularTotal()
        })
    }
    
    function calcularTotal(){
        let cantidad = parseInt(num.value)
        let descuento = 0
        if(cantidad > 10){
            descuento = 0.20
        } else if (cantidad > 5){
            descuento = 0.10
        } else {
            descuento = 0.20
        }
        let precioFinal = precioBase * (1 - descuento)
        document.querySelector('.price .current').textContent = '$' + precioFinal.toFixed(2)
        document.querySelector('.price .old').textContent = '$' + precioBase.toFixed(2)
        document.querySelector('.price .discount').textContent = (descuento * 100) + '% OFF'
    }
    
    incBtn.addEventListener('click',()=>{
        let value = parseInt(num.value);
        if(value < 15){
            num.value = value + 1;
            calcularTotal()
        }
    });
    
    decBtn.addEventListener('click',()=>{
        let value = parseInt(num.value);
        if(value > 1){
            num.value = value - 1
            calcularTotal()
        }
    });
       num.addEventListener('keypress',(evt)=>{
        if(evt.key==='Enter'){
            let valor =num.value

            if(valor=== ''|| valor === null){
                alert('Ingrese un numero valido')
                num.value=1
            } else {
                let numero = parseInt(valor)

                if(numero<1){
                    num.value = 1
                } else if(numero>15){
                    num.value=15
                }else{
                    num.value=numero
                }
            }
            calcularTotal()
        }
    })
     let reviewsContainer = document.getElementById('reviews-container')

    function generarEstrellas(rating) {
        let estrellas = ''
        let estrellasCompletas = Math.floor(rating)
        
        for(let i = 0; i < estrellasCompletas; i++) {
            estrellas += '⭐'
        }
        
        return estrellas + ' (' + rating.toFixed(1) + ')'
    }

    function cargarComentarios() {
        let comentarios = localStorage.getItem('comentarios')
        
        if(comentarios) {
            comentarios = JSON.parse(comentarios)
        } else {
            comentarios = []
        }
        
        reviewsContainer.innerHTML = ''
        
        for(let i = 0; i < comentarios.length; i++) {
            let comentario = comentarios[i]
            let reviewDiv = document.createElement('div')
            reviewDiv.className = 'review-item'
            reviewDiv.innerHTML = `
                <div class="review-header">
                    <strong>${comentario.nombre}</strong>
                    <span class="review-rating">${generarEstrellas(comentario.rating)}</span>
                </div>
                <p>${comentario.comentario}</p>
                <small>${comentario.fecha}</small>
                <button class="delete-review" data-index="${i}">Eliminar</button>
            `
            reviewsContainer.appendChild(reviewDiv)
        }

        let deleteBtns = document.querySelectorAll('.delete-review')
        for(let i = 0; i < deleteBtns.length; i++) {
            deleteBtns[i].addEventListener('click', (evt) => {
                let index = evt.target.getAttribute('data-index')
                eliminarComentario(index)
            })
        }
    }

    function eliminarComentario(index) {
        let comentarios = JSON.parse(localStorage.getItem('comentarios'))
        comentarios.splice(index, 1)
        localStorage.setItem('comentarios', JSON.stringify(comentarios))
        cargarComentarios()
    }

    let formHTML = `
        <div class="review-form">
            <h3>Deja tu comentario</h3>
            <input type="text" id="nombre-review" placeholder="Tu nombre">
            <textarea id="comentario-review" placeholder="Tu comentario" rows="4"></textarea>
            <button id="submit-review">Enviar Comentario</button>
        </div>
    `
    reviewsContainer.insertAdjacentHTML('beforebegin', formHTML)
    
    let submitBtn = document.getElementById('submit-review')
    submitBtn.addEventListener('click', () => {
        let nombre = document.getElementById('nombre-review').value.trim()
        let comentario = document.getElementById('comentario-review').value.trim()
        
        if(nombre !== '' && comentario !== '') {
            let rating = Math.random() * 4 + 1 
            
            let nuevoComentario = {
                nombre: nombre,
                comentario: comentario,
                rating: rating,
                fecha: new Date().toLocaleDateString()
            }

            let comentarios = localStorage.getItem('comentarios')
            if(comentarios) {
                comentarios = JSON.parse(comentarios)
            } else {
                comentarios = []
            }
            
            comentarios.push(nuevoComentario)
            localStorage.setItem('comentarios', JSON.stringify(comentarios))

            document.getElementById('nombre-review').value = ''
            document.getElementById('comentario-review').value = ''

            cargarComentarios()
        } else {
            alert('Por favor completa todos los campos')
        }
    })
    cargarComentarios()
    
    calcularTotal()
}

