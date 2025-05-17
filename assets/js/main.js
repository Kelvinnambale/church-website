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
  const filterButtons = document.querySelectorAll('.filter-btn');
  const eventCards = document.querySelectorAll('.event-card');

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      // Remove active class from all buttons
      filterButtons.forEach(btn => btn.classList.remove('active'));
      // Add active class to clicked button
      button.classList.add('active');

      const filter = button.getAttribute('data-filter');

      eventCards.forEach(card => {
        if (filter === 'all') {
          card.style.display = 'block';
        } else {
          // Check if card's data-category matches filter
          if (card.getAttribute('data-category') === filter) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        }
      });
    });
  });
});
// Add this to your assets/js/main.js file or create a new JS file

document.addEventListener('DOMContentLoaded', function() {
  // Get the contact form element
  const contactForm = document.getElementById('contact--Form');
  
  // Check if the form exists on the page
  if (contactForm) {
      // Use existing div for displaying form messages
      const messageDiv = document.getElementById('formMessage');
      
      // Handle form submission
      contactForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          // Show loading indicator
          messageDiv.innerHTML = '<p class="sending-message">Sending your message...</p>';
          messageDiv.style.display = 'block';
          
          // Collect form data
          const formData = new FormData(contactForm);
          
          // Send form data using fetch API
          fetch('./forms/contact_formprocess.php', {
              method: 'POST',
              body: formData
          })
          .then(response => {
              // Check if the response is a redirect (non-AJAX response)
              if (response.redirected) {
                  window.location.href = response.url;
                  return;
              }
              return response.json();
          })
          .then(data => {
              if (data && data.success) {
                  // Show success message
                  messageDiv.innerHTML = '<p class="success-message">' + data.message + '</p>';
                  // Reset the form
                  contactForm.reset();
                  // Scroll to message
                  messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
              } else {
                  // Show error message
                  messageDiv.innerHTML = '<p class="error-message">' + (data ? data.message : 'An error occurred. Please try again.') + '</p>';
                  // Scroll to top of page
                  window.scrollTo({ top: 0, behavior: 'smooth' });
              }
          })
          .catch(error => {
              console.error('Error:', error);
              messageDiv.innerHTML = '<p class="error-message">An error occurred. Please try again later.</p>';
              // Scroll to top of page
              window.scrollTo({ top: 0, behavior: 'smooth' });
          });
      });
      
      // Check URL parameters for status messages on page load
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');
      const msg = urlParams.get('msg');
      
      if (status === 'success') {
          messageDiv.innerHTML = '<p class="success-message">Thank you for your message! We will get back to you soon.</p>';
          messageDiv.style.display = 'block';
          messageDiv.scrollIntoView({ behavior: 'smooth' });
      } else if (status === 'error' && msg) {
          messageDiv.innerHTML = '<p class="error-message">' + decodeURIComponent(msg) + '</p>';
          messageDiv.style.display = 'block';
          messageDiv.scrollIntoView({ behavior: 'smooth' });
      }
  }
});
