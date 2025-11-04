window.onload = ()=>{
    let imgGaleria = document.querySelector("#main-product-img")
    let imgs= document.querySelectorAll(".thumb")
    let btnTallas = document.querySelector(".active")
    let btnsize = document.querySelectorAll(".size-btn")
    let decBtn = document.querySelector("#decrease");
    let incBtn = document.querySelector("#increase");
    let num = document.querySelector("#quantity");
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
    for(let i= 0;i<btnsize.length;i++){
        btnsize[i].addEventListener('click',(evt)=>{
            btnsize.forEach(item=>{
                item.classList.remove('active')
            });
             evt.target.classList.add('active');

            console.log("Tamaño seleccionado:", evt.target.txtContent);
        })
    }
    incBtn.addEventListener('click',()=>{
        let value =preseInt(quantityInput.value);
        quantityInput.value = value + 1;
    });
    decBtn.addEventListener('click',(evt)=>{
        let value = preseInt(quantityInput.value);
        if(value > 1){
            quantityInput.value=value-1
        }
    });
}
 