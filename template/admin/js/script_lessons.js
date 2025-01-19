// let weekCount = 40;
const startDate = new Date(2024, 10, 10); // Начало: 10 ноября 2024 года (месяцы нумеруются с 0)

function generateHTMLElement(weekCount, formattedDate, lessons, data) {
    const row = document.createElement('tr');
    row.setAttribute('draggable', 'true');
    const datalistId = `lessons-${weekCount}`;
    row.innerHTML =  `
        <td>Неделя ${weekCount} (с ${formattedDate})</td>
        <td>
            <input 
                list="${datalistId}" 
                id="lesson-${weekCount}" 
                name="lessons[${weekCount}]" 
                value="${data.value?data.value:"Выберите урок"}"
                onfocus="this.value='';" 
                onblur="handleBlur(this, '${datalistId}')"
            >
            <datalist id="${datalistId}">
                 <option value="Выберите урок">
                ${lessons.map(e => `<option value="${e}">`).join('')}
            </datalist>
            <button type="button" class="delete-week-btn" onclick="deleteSpecificWeek(this)">-</button>
        </td>
    `;
    return row
}
function handleBlur(input, datalistId) {
    if (!input.value.trim()) {
        const datalist = document.getElementById(datalistId);
        const firstOption = datalist.querySelector('option');
        input.value = firstOption ? firstOption.value : '';
    } else {
        const datalist = document.getElementById(datalistId);
        const matchingOption = Array.from(datalist.querySelectorAll('option')).find(option =>
            option.value.toLowerCase().startsWith(input.value.toLowerCase())
        );
        if (matchingOption) {
            input.value = matchingOption.value;
        }
    }
}



function addWeek(lessons) {
    weekCount++;
    const tbody = document.getElementById('week-table-body');
    const weekStartDate = new Date(startDate);
    weekStartDate.setDate(weekStartDate.getDate() + (weekCount - 1) * 7);
    const formattedDate = `${String(weekStartDate.getDate()).padStart(2, '0')}.${String(weekStartDate.getMonth() + 1).padStart(2, '0')}.${String(weekStartDate.getFullYear()).slice(2)}`;
    tbody.appendChild(generateHTMLElement(weekCount, formattedDate, lessons, 'Выберите урок'));
    makeRowsDraggable();
    updateWeekLabels();
}

function deleteSpecificWeek(button) {
    const row = button.parentElement.parentElement;
    row.parentElement.removeChild(row);
    weekCount--;
    updateWeekLabels();
}

function updateWeekLabels() {
    console.clear()
    const rows = document.querySelectorAll('#week-table-body tr');
    const currentDate = new Date();
    currentDate.setHours(0, 0, 0, 0); // Устанавливаем время в полночь для точного сравнения
    let extraWeeksCount = 0;
    let WeekMoreNow = false

    rows.forEach((row, index) => {
        let weekStartDate = new Date(startDate);
        weekStartDate.setDate(weekStartDate.getDate() + index * 7);
        let weekEndDate = new Date(weekStartDate);
        weekEndDate.setDate(weekStartDate.getDate() + 7);
        row.classList.remove('nowData')
        if (currentDate >= weekStartDate && currentDate < weekEndDate) {
            // Если неделя сейчас
            WeekMoreNow = true
            row.classList.add('nowData')
            row.children[0].textContent = `Неделя ${index + 1} (с ${formatDate(weekStartDate)})`;
        } else if (weekStartDate < currentDate) {
            // Если неделя уже прошла, пересчитываем её дату на будущее
            extraWeeksCount++;
            weekStartDate = new Date(startDate);
            let calcWc = Math.floor((currentDate - weekStartDate) / (7 * 24 * 60 * 60 * 1000));
            let wc =  calcWc >= weekCount? calcWc:weekCount-1
            weekStartDate.setDate(weekStartDate.getDate() + (wc + extraWeeksCount) * 7);
            row.children[0].textContent = `Неделя ${index + 1} (с ${formatDate(weekStartDate)})`;
        } else {
            // Будущие недели остаются неизменными
            row.children[0].textContent = `Неделя ${index + 1} (с ${formatDate(weekStartDate)})`;
        }
    });

    if(!WeekMoreNow){
        extraWeeksCount = 0
        rows.forEach((row, index) => {
            let weekStartDate = new Date(startDate);
            weekStartDate.setDate(weekStartDate.getDate() + index * 7);
            let weekEndDate = new Date(weekStartDate);
            weekEndDate.setDate(weekStartDate.getDate() + 7);
            row.classList.remove('nowData')
            if(index == 0){
                const today = new Date();
                let lastWeekSunday = new Date(today);
                lastWeekSunday.setDate(lastWeekSunday.getDate() - lastWeekSunday.getDay());
                row.classList.add('nowData')
                row.children[0].textContent = `Неделя ${index + 1} (с ${formatDate(lastWeekSunday)})`;
            }else{
                // Если неделя уже прошла, пересчитываем её дату на будущее
                extraWeeksCount++;
                weekStartDate = new Date(startDate);
                let calcWc = Math.floor((currentDate - weekStartDate) / (7 * 24 * 60 * 60 * 1000));
                let wc =  calcWc >= weekCount? calcWc:weekCount-1
                weekStartDate.setDate(weekStartDate.getDate() + (wc + extraWeeksCount) * 7);
                row.children[0].textContent = `Неделя ${index + 1} (с ${formatDate(weekStartDate)})`;
            }
        })
    }
}

function formatDate(date) {
    return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${String(date.getFullYear()).slice(2)}`;
}

function makeRowsDraggable() {
    const rows = document.querySelectorAll('#week-table-body tr');
    rows.forEach(row => {
        row.addEventListener('dragstart', handleDragStart);
        row.addEventListener('dragover', handleDragOver);
        row.addEventListener('dragleave', handleDragLeave);
        row.addEventListener('drop', handleDrop);
        row.addEventListener('dragend', handleDragEnd);
    });
}

let draggedRow = null;

function handleDragStart(event) {
    draggedRow = this;
    setTimeout(() => this.classList.add('hidden'), 0);
    this.classList.add('dragged');
}

function handleDragOver(event) {
    event.preventDefault();
    const bounding = this.getBoundingClientRect();
    const offset = event.clientY - bounding.top;
    const height = bounding.height / 2;

    if (offset < height) {
        this.classList.add('drag-over-top');
        this.classList.remove('drag-over-bottom');
    } else {
        this.classList.add('drag-over-bottom');
        this.classList.remove('drag-over-top');
    }
}

function handleDragLeave() {
    this.classList.remove('drag-over-top', 'drag-over-bottom');
}

function handleDrop() {
    this.classList.remove('drag-over-top', 'drag-over-bottom');
    if (this !== draggedRow) {
        const tbody = document.getElementById('week-table-body');
        const rows = Array.from(tbody.children);
        const draggedIndex = rows.indexOf(draggedRow);
        const targetIndex = rows.indexOf(this);

        if (draggedIndex < targetIndex) {
            this.after(draggedRow);
        } else {
            this.before(draggedRow);
        }
    }
    updateWeekLabels();
}

function handleDragEnd() {
    this.classList.remove('hidden');
    this.classList.remove('dragged');
    updateWeekLabels();
}

const printData = (savedData,lessons ) => {
    const container = document.getElementById('week-table-body');
    container.innerHTML = ''
    const formatDate = (date) =>
        date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: '2-digit' });

    for(let week = 1; week <= weekCount; week ++){
        const weekStartDate = new Date(startDate);
        weekStartDate.setDate(startDate.getDate() + (week * 7));
        const formattedDate = formatDate(weekStartDate);
        let data = savedData[week-1]?savedData[week-1]:"Выберите урок"
        container.appendChild( generateHTMLElement(week, formattedDate, lessons, data))
    }
}

function submitForm(form) { 
    
    function getFormattedDateFromInput(inputElement) { 
        const cellText = inputElement.closest('tr').querySelector('td').textContent; 
        const dateMatch = cellText.match(/с (\d{2}\.\d{2}\.\d{2})/); 
        return dateMatch ? dateMatch[1] : null; 
    } 
 
    form.addEventListener('submit', function (event) { 
        const lessonsData = []; 
        Array.from(form.elements).forEach(element => { 
            if (element.name.startsWith("lessons[")) { 
                try { 
                    lessonsData.push({ 
                        name: element.name, 
                        value: element.value, 
                        date: getFormattedDateFromInput(element) 
                    });    
                } catch (error) {} 
            } 
        }); 
        let hiddenInput = form.querySelector('input[name="lessonsData"]'); 
        if (!hiddenInput) { 
            hiddenInput = document.createElement('input'); 
            hiddenInput.type = 'hidden'; 
            hiddenInput.name = 'lessonsData'; 
            form.appendChild(hiddenInput); 
        } 
        hiddenInput.value = JSON.stringify(lessonsData); 
        this.submit(); 
    }); 
}

document.addEventListener('DOMContentLoaded', () => { 
    document.getElementsByName('savetraining')[0].addEventListener('click', function () { 
        const form = this.closest('form'); 
        if (form) { 
            form.submit(); 
        } 
    }); 
    submitForm(document.getElementById('1'));  
    printData(saveData, lessons);
    makeRowsDraggable();    
    updateWeekLabels();
});