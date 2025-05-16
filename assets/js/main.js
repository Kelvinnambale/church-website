/*=============== SHOW MENU ===============*/
const showMenu = (toggleId, navId) =>{
   const toggle = document.getElementById(toggleId),
         nav = document.getElementById(navId)

   toggle.addEventListener('click', () =>{
       // Add show-menu class to nav menu
       nav.classList.toggle('show-menu')

       // Add show-icon to show and hide the menu icon
       toggle.classList.toggle('show-icon')
   })
}


showMenu('nav-toggle','nav-menu')

// Reveal animations with reduced duration and delay
document.addEventListener('DOMContentLoaded', function () {
  ScrollReveal().reveal('.hero', { delay: 100, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)', reset: false, viewFactor: 0 });
  ScrollReveal().reveal('.hero-title', { delay: 200, distance: '20px', origin: 'left', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)', reset: false, viewFactor: 0 });
  ScrollReveal().reveal('.hero-subtitle', { delay: 300, distance: '20px', origin: 'right', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)', reset: false, viewFactor: 0 });
  ScrollReveal().reveal('.hero-buttons', { delay: 400, distance: '20px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)', reset: false, viewFactor: 0 });
  ScrollReveal().reveal('.chango-ministries-section', { delay: 500, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)' });
  ScrollReveal().reveal('.church-container', { delay: 600, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)' });
  ScrollReveal().reveal('.events-section', { delay: 700, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)' });
  ScrollReveal().reveal('.dfnt-sermon-section', { delay: 800, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)' });
  ScrollReveal().reveal('.contact-section', { delay: 900, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)' });
  ScrollReveal().reveal('.chango-footer', { delay: 1000, distance: '50px', origin: 'bottom', duration: 600, easing: 'cubic-bezier(0.5, 0, 0, 1)' });
});



