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

// DOM interaction to validate inputs and block form submission
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const accountNumberInput = document.getElementById('account-number');
    const innInput = document.getElementById('inn');
    const accountNumberError = document.createElement('span');
    const innError = document.createElement('span');

    accountNumberError.style.color = 'red';
    innError.style.color = 'red';

    accountNumberInput.parentNode.insertBefore(accountNumberError, accountNumberInput.nextSibling);
    innInput.parentNode.insertBefore(innError, innInput.nextSibling);

    function validateInn() {
        const inn = innInput.value;
        if (!checkInn10(inn) && !checkInn12(inn)) {
            innError.textContent = 'ИНН неверный. Должно быть 10 или 12 цифр.';
            return false;
        } else {
            innError.textContent = '';
            return true;
        }
    }

    function validateAccountAndInn() {
        const accountNumber = accountNumberInput.value;
        const inn = innInput.value;
        try {
            if (!validateAccountInn(accountNumber, inn)) {
                accountNumberError.textContent = 'ИНН не соответствует номеру счёта.';
                return false;
            } else {
                accountNumberError.textContent = '';
                return true;
            }
        } catch (error) {
            accountNumberError.textContent = error.message;
            return false;
        }
    }

    innInput.addEventListener('input', () => {
        validateInn();
    });

    accountNumberInput.addEventListener('input', () => {
        validateAccountAndInn();
    });

    form.addEventListener('submit', (event) => {
        const isInnValid = validateInn();
        const isAccountValid = validateAccountAndInn();

        if (!isInnValid || !isAccountValid) {
            event.preventDefault();
        }
    });
});
