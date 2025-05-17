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
  // Get the newsletter form element
  const newsletterForm = document.getElementById('newsletterForm');
  
  // Handle contact form submission
  if (contactForm) {
      const messageDiv = document.getElementById('formMessage');
      contactForm.addEventListener('submit', function(e) {
          e.preventDefault();
          messageDiv.innerHTML = '<p class="sending-message">Sending your message...</p>';
          messageDiv.style.display = 'block';
          const formData = new FormData(contactForm);
          let formActionUrl = contactForm.getAttribute('action') || './forms/contact_formprocess.php';
          fetch(formActionUrl, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: formData
          })
          .then(response => {
              if (response.redirected) {
                  window.location.href = response.url;
                  return;
              }
              return response.json();
          })
          .then(data => {
              if (data && data.success) {
                  messageDiv.innerHTML = '<p class="success-message">' + data.message + '</p>';
                  contactForm.reset();
                  messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
              } else {
                  messageDiv.innerHTML = '<p class="error-message">' + (data ? data.message : 'An error occurred. Please try again.') + '</p>';
                  window.scrollTo({ top: 0, behavior: 'smooth' });
              }
          })
          .catch(error => {
              console.error('Error:', error);
              messageDiv.innerHTML = '<p class="error-message">An error occurred. Please try again later.</p>';
              window.scrollTo({ top: 0, behavior: 'smooth' });
          });
      });
  }
  
  // Handle newsletter form submission
  if (newsletterForm) {
      const newsletterMessageDiv = document.createElement('div');
      newsletterMessageDiv.id = 'newsletterMessage';
      newsletterForm.parentNode.insertBefore(newsletterMessageDiv, newsletterForm.nextSibling);
      
      newsletterForm.addEventListener('submit', function(e) {
          e.preventDefault();
          newsletterMessageDiv.innerHTML = '<p class="sending-message">Submitting your subscription...</p>';
          newsletterMessageDiv.style.display = 'block';
          const formData = new FormData(newsletterForm);
          let formActionUrl = newsletterForm.getAttribute('action') || './forms/newsletter-processform.php';
          fetch(formActionUrl, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: formData
          })
          .then(response => {
              if (response.redirected) {
                  window.location.href = response.url;
                  return;
              }
              return response.json();
          })
          .then(data => {
              if (data && data.success) {
                  newsletterMessageDiv.innerHTML = '<p class="success-message">' + data.message + '</p>';
                  newsletterForm.reset();
                  newsletterMessageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
              } else {
                  newsletterMessageDiv.innerHTML = '<p class="error-message">' + (data ? data.message : 'An error occurred. Please try again.') + '</p>';
                  window.scrollTo({ top: 0, behavior: 'smooth' });
              }
          })
          .catch(error => {
              console.error('Error:', error);
              newsletterMessageDiv.innerHTML = '<p class="error-message">An error occurred. Please try again later.</p>';
              window.scrollTo({ top: 0, behavior: 'smooth' });
          });
      });
  }
});
