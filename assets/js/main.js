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

document.addEventListener('DOMContentLoaded', function() {
    const eventsContainer = document.querySelector('.events-container');
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    const scrollDots = document.querySelectorAll('.scroll-dot');
    
    // Calculate scroll amount (width of one card + gap)
    const scrollAmount = 370; // 350px card width + 20px gap
    
    // Scroll buttons functionality
    scrollLeftBtn.addEventListener('click', function() {
        eventsContainer.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    });
    
    scrollRightBtn.addEventListener('click', function() {
        eventsContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    });
    
    // Show/hide scroll buttons based on scroll position
    eventsContainer.addEventListener('scroll', function() {
        updateScrollButtons();
        updateScrollIndicator();
    });
    
    // Initial button state
    updateScrollButtons();
    
    function updateScrollButtons() {
        const scrollLeft = eventsContainer.scrollLeft;
        const maxScrollLeft = eventsContainer.scrollWidth - eventsContainer.clientWidth;
        
        // Only show left button if we've scrolled right
        scrollLeftBtn.classList.toggle('hidden', scrollLeft <= 0);
        
        // Only show right button if we can scroll more to the right
        scrollRightBtn.classList.toggle('hidden', scrollLeft >= maxScrollLeft);
    }
    
    function updateScrollIndicator() {
        const scrollLeft = eventsContainer.scrollLeft;
        const maxScrollLeft = eventsContainer.scrollWidth - eventsContainer.clientWidth;
        
        // Calculate which dot should be active based on scroll position
        const scrollPercentage = scrollLeft / maxScrollLeft;
        const numDots = scrollDots.length;
        let activeDotIndex = 0;
        
        if (numDots > 1) {
            // If we can't scroll (content fits without scrolling), activate first dot
            if (maxScrollLeft === 0) {
                activeDotIndex = 0;
            } else {
                // Otherwise calculate which dot should be active
                activeDotIndex = Math.min(
                    Math.floor(scrollPercentage * numDots), 
                    numDots - 1
                );
            }
        }
        
        // Update active dot
        scrollDots.forEach((dot, index) => {
            dot.classList.toggle('active', index === activeDotIndex);
        });
    }
    
    // Make dots clickable to jump to positions
    scrollDots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            const maxScrollLeft = eventsContainer.scrollWidth - eventsContainer.clientWidth;
            const scrollPercentage = index / (scrollDots.length - 1);
            const scrollTo = maxScrollLeft * scrollPercentage;
            
            eventsContainer.scrollTo({
                left: scrollTo,
                behavior: 'smooth'
            });
        });
    });
    
    // Touch swipe functionality for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    eventsContainer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    eventsContainer.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeThreshold = 70; // Minimum distance for a swipe
        if (touchEndX < touchStartX - swipeThreshold) {
            // Swipe left -> scroll right
            eventsContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }
        if (touchEndX > touchStartX + swipeThreshold) {
            // Swipe right -> scroll left
            eventsContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        }
    }
    
    // Add subtle parallax effect to the event cards while scrolling
    let scrolling = false;
    eventsContainer.addEventListener('scroll', function() {
        scrolling = true;
    });
    
    // Animate cards during scroll for subtle parallax effect
    setInterval(function() {
        if (scrolling) {
            scrolling = false;
            const cards = document.querySelectorAll('.event-card');
            const scrollLeft = eventsContainer.scrollLeft;
            
            cards.forEach((card, index) => {
                const cardPos = card.offsetLeft;
                const distanceFromCenter = cardPos - scrollLeft - (eventsContainer.clientWidth / 2) + (card.offsetWidth / 2);
                const parallaxAmount = distanceFromCenter * 0.05;
                
                // Apply subtle transform for parallax effect
                card.style.transform = `translateX(${-parallaxAmount * 0.2}px) rotateY(${parallaxAmount * 0.03}deg)`;
            });
        }
    }, 20);
});

