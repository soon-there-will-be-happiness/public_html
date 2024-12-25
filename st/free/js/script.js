function addLoadingElement(parent) {
    const styleElem = document.head.appendChild(document.createElement("style"))
    styleElem.innerHTML = `
       #loadScreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        #loadScreen.showLoad {
            opacity: 1;
            visibility: visible;
        }

        #loadScreen .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Полупрозрачный серый фон */
            z-index: 1;
        }

        #loadScreen .content {
            position: relative;
            z-index: 2;
            color: white;
            font-size: 20px;
            font-family: Arial, sans-serif;
            text-align: center;
            background: linear-gradient(135deg, #ffa600, #ff8562); /* Градиент для текста */
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            transition: transform 0.5s ease, opacity 0.5s ease;
            opacity: 0;
        }

        #loadScreen.showLoad .content {
            transform: scale(1);
            opacity: 1;
        }
    `

    const loadElem = document.createElement('div')
    loadElem.innerHTML = '<div class="background"></div> <div class="content">Идёт загрузка, подождите</div>'
    loadElem.setAttribute('id', 'loadScreen')
    parent.appendChild(loadElem)
}

function showLoadingScreen() {
    const loadScreen = document.getElementById('loadScreen');
    if (loadScreen) loadScreen.classList.add('showLoad');
}

function hideLoadingScreen() {
    const loadScreen = document.getElementById('loadScreen');
    if (loadScreen) loadScreen.classList.remove('showLoad');
}

function getFormData(form) {
    const formData = new FormData(form)
    const transformedData = { buy: "buy" }
    formData.forEach((value, key) => {
        transformedData[key] = value
    })
    return transformedData
}
function addEventBuyBtn(btn){
    btn.addEventListener('click', event => {
        showLoadingScreen()
    })
}


document.querySelector('.buy-btn').addEventListener('click', function () {
    document.getElementById('popup').classList.add('show'); // Додаємо клас "show", який відображає попап
});

// Закриття попапу через кнопку "закриття"
document.querySelector('.close-btn').addEventListener('click', function () {
    document.getElementById('popup').classList.remove('show'); // Приховуємо попап
});

// Закриття попапу при кліці поза його межами
window.addEventListener('click', function (event) {
    const popup = document.getElementById('popup');
    if (event.target === popup) {
        popup.classList.remove('show'); // Приховуємо попап при кліці поза ним
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let popup = document.getElementById('popup')
    addLoadingElement(popup)
    let btn = document.getElementById('buy')
    addEventBuyBtn(btn)
    var input = document.querySelector("#phone");
    var iti = window.intlTelInput(input, {
        initialCountry: "ru",
        onlyCountries: ["ru"],
        separateDialCode: true,
        allowDropdown: false,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });
});

document.querySelector('.promo-link').addEventListener('click', function (e) {
    e.preventDefault(); // Останавливаем стандартное поведение ссылки
    var promoBlock = document.querySelector('.promo-block');

    // Убираем или добавляем класс hidden и active для управления анимацией
    if (promoBlock.classList.contains('active')) {
        promoBlock.classList.remove('active');
        setTimeout(function () {
            promoBlock.classList.add('hidden');
        }, 500); // Ждём завершения анимации перед скрытием через display: none
    } else {
        promoBlock.classList.remove('hidden');
        setTimeout(function () {
            promoBlock.classList.add('active');
        }, 10); // Небольшая задержка для активации анимации
    }
});


function toggleFields() {
    var telegramInput = document.getElementById('telegram');

    if (telegramInput.style.display === 'none') {
        telegramInput.style.display = 'block';
    } else {
        telegramInput.style.display = 'none';
    }
}


