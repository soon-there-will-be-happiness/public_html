// Відкриття попапу
document.querySelector('.buy-btn').addEventListener('click', function() {
    document.getElementById('popup').classList.add('show'); // Додаємо клас "show", який відображає попап
  });
  
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли в URL якорь "#pay" при загрузке страницы
    if (window.location.hash === '#pay') {
        document.getElementById('popup').classList.add('show');
    }
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

document.querySelector('.promo-link').addEventListener('click', function(e) {
  e.preventDefault(); // Останавливаем стандартное поведение ссылки

  var promoBlock = document.querySelector('.promo-block');
  
  // Убираем или добавляем класс hidden и active для управления анимацией
  if (promoBlock.classList.contains('active')) {
      promoBlock.classList.remove('active');
      setTimeout(function() {
          promoBlock.classList.add('hidden');
      }, 500); // Ждём завершения анимации перед скрытием через display: none
  } else {
      promoBlock.classList.remove('hidden');
      setTimeout(function() {
          promoBlock.classList.add('active');
      }, 10); // Небольшая задержка для активации анимации
  }
});


