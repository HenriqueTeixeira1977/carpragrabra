AOS.init({once:true,duration:800});

// voltar ao topo
const topBtn=document.getElementById('toTop');
window.addEventListener('scroll',()=>topBtn.style.display=window.scrollY>300?'flex':'none');
topBtn.onclick=()=>window.scrollTo({top:0,behavior:'smooth'});

// validação bootstrap
(()=>{"use strict";const forms=document.querySelectorAll('.needs-validation');Array.from(forms).forEach(f=>{f.addEventListener('submit',e=>{if(!f.checkValidity()){e.preventDefault();e.stopPropagation()}f.classList.add('was-validated')},!1)})})();


  // Adiciona/remova a classe 'scrolled' ao rolar a página
  window.addEventListener('scroll', () => {
    const navbar = document.getElementById('mainNavbar');
    if (window.scrollY > 100) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});