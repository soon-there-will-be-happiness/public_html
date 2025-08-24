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
    const rows = document.querySelectorAll('#week-table-body tr');
    const currentDate = new Date();
    currentDate.setHours(0, 0, 0, 0);   // сравниваем по дате без времени

    let extraShift = 0;                 // сдвиг, если недели «ушли» в прошлое
    let hasCurrent = false;

    rows.forEach((row, index) => {

        if (row.dataset.fixed === '1') return; // фиксированную дату не трогаем

        const cell = row.children[0];
        let start = new Date(startDate);
        start.setDate(start.getDate() + index * 7);
        const end = new Date(start);
        end.setDate(start.getDate() + 7);

        // снимаем выделение
        row.classList.remove('nowData');

        if (currentDate >= start && currentDate < end) {
            // «текущая неделя»
            hasCurrent = true;
            row.classList.add('nowData');
            cell.textContent = `Неделя ${index + 1} (с ${formatDate(start)})`;
        } else if (start < currentDate) {
            // неделя прошла → переносим вперёд
            extraShift++;
            start = new Date(startDate);
            const pastCount = Math.floor((currentDate - start) / 604800000); // 7*24*60*60*1000
            const shift = Math.max(pastCount, weekCount - 1) + extraShift;
            start.setDate(start.getDate() + shift * 7);
            cell.textContent = `Неделя ${index + 1} (с ${formatDate(start)})`;
        } else {
            // будущее — оставляем
            cell.textContent = `Неделя ${index + 1} (с ${formatDate(start)})`;
        }
    });

    /* если «текущей» недели не оказалось, ставим выделение первой строке,
       только если она НЕ fixed */
    if (!hasCurrent && rows.length) {
        const firstRow = rows[0];
        if (firstRow.dataset.fixed !== '1') {
            firstRow.classList.add('nowData');
            const today = new Date();
            today.setDate(today.getDate() - today.getDay()); // предыдущее воскресенье
            firstRow.children[0].textContent =
                `Неделя 1 (с ${formatDate(today)})`;
        }
    }
}

/* helper форматирования */
function formatDate(d) {
    return `${String(d.getDate()).padStart(2, '0')}.` +
           `${String(d.getMonth() + 1).padStart(2, '0')}.` +
           `${String(d.getFullYear()).slice(2)}`;
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

function printData(savedData, lessons) {
    const container = document.getElementById('week-table-body');
    container.innerHTML = '';

    // helper: 01.04.25
    const fmt = d => d.toLocaleDateString('ru-RU',
                  { day: '2-digit', month: '2-digit', year: '2-digit' });

    for (let week = 1; week <= weekCount; week++) {
        const src = savedData[week - 1] ?? { value: 'Выберите урок', date: null };

        let dateStr, isFixed = false;

        if (src.date) {                 // дата пришла из PHP/BД
            dateStr = src.date;
            isFixed = true;             // пометим как «фиксированная»
        } else {                        // даты нет → считаем
            const ds = new Date(startDate);
            ds.setDate(ds.getDate() + (week - 1) * 7);
            dateStr = fmt(ds);
        }

        const row = generateHTMLElement(week, dateStr, lessons, src);

        if (isFixed) row.dataset.fixed = '1'; // <tr data-fixed="1">
        container.appendChild(row);
    }

    makeRowsDraggable();
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