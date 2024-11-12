function checkInn10(inn) {
    if (inn.length !== 10 || !/^\d+$/.test(inn)) {
        return false;
    }

    // Weight coefficients for 10-digit INN
    const weights = [2, 4, 10, 3, 5, 9, 4, 6, 8, 0];
    let sum = 0;

    // Calculate checksum
    for (let i = 0; i < 9; i++) {
        sum += inn[i] * weights[i];
    }

    // Calculate control number
    let controlNumber = sum % 11;
    if (controlNumber > 9) {
        controlNumber = controlNumber % 10;
    }

    // Compare control number with 10th character of INN
    return controlNumber == inn[9];
}
function checkInn12(inn) {
    if (inn.length !== 12 || !/^\d+$/.test(inn)) {
        return false;
    }

    // Weight coefficients for the first 11 digits
    const weights1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0];
    let sum1 = 0;

    // Calculate checksum for the first 11 digits
    for (let i = 0; i < 11; i++) {
        sum1 += inn[i] * weights1[i];
    }

    // Calculate control number 1
    let controlNumber1 = sum1 % 11;
    if (controlNumber1 > 9) {
        controlNumber1 = controlNumber1 % 10;
    }

    // Weight coefficients for all 12 digits
    const weights2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0];
    let sum2 = 0;

    // Calculate checksum for all 12 digits
    for (let i = 0; i < 12; i++) {
        sum2 += inn[i] * weights2[i];
    }

    // Calculate control number 2
    let controlNumber2 = sum2 % 11;
    if (controlNumber2 > 9) {
        controlNumber2 = controlNumber2 % 10;
    }

    // Check control number 1 with 11th character and control number 2 with 12th character
    return controlNumber1 == inn[10] && controlNumber2 == inn[11];
}
function validateAccountInn(accountNumber, inn) {
    // Determine recipient type based on account number
    const recipientType = getRecipientTypeByAccount(accountNumber);

    // Check INN validity for each recipient type
    switch (recipientType) {
        case 'IP_resident':
            return /^\d{12}$/.test(inn); // INN 12 digits
        case 'UL_resident_or_IP_general':
            return /^\d{10}$|^\d{12}$/.test(inn); // INN 10 or 12 digits
        case 'FL_resident':
            return /^\d{12}$/.test(inn); // INN 12 digits
        case 'FL_nonresident':
            return /^\d{12}$/.test(inn); // INN 12 digits
        case 'UL_nonresident':
            return /^\d{10}$/.test(inn); // INN 10 digits
        case 'UL_nonresident_40807':
            return /^\d{5}$|^\d{10}$/.test(inn); // INN 5 or 10 digits
        case 'other':
            return /^\d{10}$/.test(inn); // INN 10 digits for all others
        default:
            throw new Error("Invalid account type for verification.");
    }
}
function getRecipientTypeByAccount(accountNumber) {
    // Ensure account number contains only digits
    if (!/^\d+$/.test(accountNumber)) {
        throw new Error("Invalid account number format.");
    }

    // Determine account prefix by the first 5 digits
    const accountPrefix = accountNumber.substring(0, 5);
    const accountPrefixFl = accountNumber.substring(0, 3);

    // Prefix arrays for validation
    const ipResident = ['40802', '42108', '42109', '42110', '42111', '42112', '42113', '42114'];
    const ulResidentOrIpGeneral = ['30232', '40821', '40822', '47422', '47423', '45814', '45815', '45817', '45914', '45915', '45917', '60308', '454', '455', '457'];
    const flResident = ['408', '423'];
    const flNonResident = ['40803', '40813', '40820', '426'];
    const ulNonResident = ['30111', '30114', '30122', '30123', '30231'];
    const ulNonResident40807 = ['40807'];

    // Match prefix to recipient type
    if (ipResident.includes(accountPrefix)) {
        console.log('IP_resident');
        return 'IP_resident';
    } else if (ulResidentOrIpGeneral.includes(accountPrefix)) {
        console.log('UL_resident_or_IP_general');
        return 'UL_resident_or_IP_general';
    } else if (flNonResident.includes(accountPrefix)) {
        console.log('FL_nonresident');
        return 'FL_nonresident';
    } else if (ulNonResident.includes(accountPrefix)) {
        console.log('UL_nonresident');
        return 'UL_nonresident';
    } else if (ulNonResident40807.includes(accountPrefix)) {
        console.log('UL_nonresident_40807');
        return 'UL_nonresident_40807';
    } else if (flResident.includes(accountPrefixFl)) {
        console.log('FL_resident');
        return 'FL_resident';
    } else {
        return 'other';
    }
}

//  ==========================================================================

let root = 'https://dev.кемстать.рф/'

let addSuccesMessage = (text)=>{
    let container = document.getElementsByClassName("maincol")[0]
    const successMessage = document.createElement('div');
    successMessage.className = 'success_message';
    successMessage.innerHTML = `${text}!`

    container.insertBefore(successMessage, container.children[1]);
    setTimeout(() => {
        successMessage.style.transition = 'opacity 0.2s ease';
        successMessage.style.opacity = '0';
        setTimeout(() => {
            successMessage.remove();
        }, 200);
    }, 4000);
}
let getFormData = () => {
    const form = document.querySelector(".requisites > form")
    const formData = new FormData(form)
    const transformedData = { save_req: "save_req", req: {} }
    formData.forEach((value, key) => {
        if (key.includes("req[rs]")) {
            const subKey = key.match(/\[rs\]\[([^\]]+)\]/)[1]
            transformedData.req.rs = transformedData.req.rs || {}
            transformedData.req.rs[subKey] = value
        } else {
            transformedData.req[key] = value
        }
    })
    return transformedData
}
let validator = ()=>{
    const accountNumberInput = $('#account-number');
    const innInput = $('#inn');
    const accountNumberError = $('<span>').css('color', 'red');
    const innError = $('<span>').css('color', 'red');
    const submitButton = $('.submit-btn');
    accountNumberInput.after(accountNumberError);
    innInput.after(innError);

    function validateInn() {
        const inn = innInput.val();
        if (!checkInn10(inn) && !checkInn12(inn)) {
            innError.text('ИНН неверный. Должно быть 10 или 12 цифр.');
            return false;
        } else {
            innError.text('');
            return true;
        }
    }
    function validateAccountAndInn() {
        const accountNumber = accountNumberInput.val();
        const inn = innInput.val();
        try {
            if (!validateAccountInn(accountNumber, inn)) {
                accountNumberError.text('ИНН не соответствует номеру счёта.');
                return false;
            } else {
                accountNumberError.text('');
                return true;
            }
        } catch (error) {
            accountNumberError.text(error.message);
            return false;
        }
    }

    return (validateInn() && validateAccountAndInn())
}
let checkInput = (btn)=>{
    if (validator()) {
        btn.style.backgroundColor = '#3250ea';  
        btn.style.color = '#fff';            
        btn.style.cursor = 'pointer';   
        btn.disabled = false; 
    } else {
        btn.style.backgroundColor = '#ccc';  
        btn.style.color = '#666';            
        btn.style.cursor = 'not-allowed'
        btn.disabled = true;
    }   
}
let addInput = (btn) =>{
    let deleteSpan = () =>{
        const elem = document.querySelector(".form-section_two > span")
        const elem2 = document.querySelector(".form-section_second > span")
        if(elem)
            elem.remove()
        if(elem2)
            elem2.remove()
    }
    deleteSpan()
    checkInput(btn)
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', (event) => {
            checkInput(btn)
        });
    });
}
let addBtnEvent = (btn) => {
    btn.removeAttribute('type');
    btn.addEventListener('click', e => {
        e.preventDefault()
        formDataObject = getFormData()
        $.ajax({
            url: root + "lk/aff",
            type: 'POST',
            data: formDataObject,
            dataType: 'text',
            success: function (response) {
                if (response.includes('success')) {
                    addSuccesMessage("Сохранено")
                } else {
                    addSuccesMessage("Ошибка")
                }
            },
            error: function (xhr, status, error) {
                addSuccesMessage("Ошибка")
                console.log(status)
                console.log(error)
            }
        });
    })
}
let findLink = () =>{
    let links = document.querySelectorAll('.table-responsive a');
    links.forEach(link => {
        link.addEventListener('click',(e)=>{
            e.preventDefault()
            $.ajax({
                url: e.target.href,
                type: 'GET',
                data: null,
                dataType: 'text',
                success: function (response) {
                    const container = document.evaluate('//*[@id="lk"]/div/div/div/div/div[3]', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                    const doc = document.implementation.createHTMLDocument('New Document'); // Создаем новый HTML-документ
                    doc.documentElement.innerHTML = response;
                    let containerNew = doc.evaluate('//*[@id="lk"]/div/div/div/div/div[3]', doc, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                    container.innerHTML = containerNew.innerHTML
                    findLink()
                },
                error: function (xhr, status, error) {
                    addSuccesMessage("Ошибка")
                    console.log(status)
                    console.log(error)
                }
            });
        })
    });
}
document.addEventListener('DOMContentLoaded', ()=> {
    let btn = document.getElementsByName('save_req')[0];
    addInput(btn)
    addBtnEvent(btn)
    findLink()
})



// <div class="success_message" style="display: block;">Сохранено!</div>

