// Відкриття попапу
document.querySelector('.buy-btn').addEventListener('click', function() {
    document.getElementById('popup').classList.add('show'); // Додаємо клас "show", який відображає попап
  });
  
  // Закриття попапу через кнопку "закриття"
  document.querySelector('.close-btn').addEventListener('click', function() {
    document.getElementById('popup').classList.remove('show'); // Приховуємо попап
  });
  
  // Закриття попапу при кліці поза його межами
  window.addEventListener('click', function(event) {
    const popup = document.getElementById('popup');
    if (event.target === popup) {
      popup.classList.remove('show'); // Приховуємо попап при кліці поза ним
    }
  });

  document.addEventListener("DOMContentLoaded", function() {
    var input = document.querySelector("#phone");
    var iti = window.intlTelInput(input, {
        initialCountry: "ru",
        onlyCountries: ["ru"],
        separateDialCode: true,
        allowDropdown: false,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });
});

function toggleFields() {
  var telegramInput = document.getElementById('telegram');

  if (telegramInput.style.display === 'none') {
    telegramInput.style.display = 'block';
  } else {
    telegramInput.style.display = 'none';
    }
}


