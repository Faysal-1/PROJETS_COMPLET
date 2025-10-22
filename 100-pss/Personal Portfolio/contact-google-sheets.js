// contact-google-sheets.js
// Remplace 'TON_WEBHOOK_HERE' par l'URL de ton Apps Script déployé
const scriptURL = 'https://script.google.com/macros/s/AKfycbz2DNDI6rTxKWTNNgvjD2JxzREJOC747H2Q8Bita80hFBNk2wKRBxRYY056jSINGQfy/exec';
window.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('contactForm');
  const msg = document.getElementById('formMessage');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = {
      name: document.getElementById('name').value,
      email: document.getElementById('email').value,
      message: document.getElementById('message').value
    };
    fetch(scriptURL, {
      method: 'POST',
      body: JSON.stringify(data)
    })
    .then(response => {
      msg.innerText = "✅ Message envoyé avec succès !";
      msg.style.color = "green";
      form.reset();
    })
    .catch(error => {
      msg.innerText = "❌ Erreur lors de l'envoi.";
      msg.style.color = "red";
    });
  });
});
