const backupTypes = {
    "db" : "БД",
    "file" : "Файлы",
    "clients": "Клиенты в csv",
}



///////////////////////////////////////
//// Восстановление
///////////////////////////////////////

/**
 * Установить стандартные настройки
 *
 */
function setDefaults() {
    document.getElementById("restore_type").innerHTML = "<option value='0'>Все</option>";

    document.getElementById("from_bucket_type_0").classList.add("hidden");
    document.getElementById("from_bucket_type_1").classList.add("hidden");
    document.getElementById("from_bucket_type_2").classList.add("hidden");
    document.getElementById("from_bucket_type_3").classList.add("hidden");
    document.getElementById("from_bucket_type_4").classList.add("hidden");

    document.getElementById("restore_copy_task_id").value = "";
    document.getElementById("restore_copy_date").value = "";
}


document.addEventListener("DOMContentLoaded", function () {
    //Обработка события отправки формы
    document.getElementById("restore_form").addEventListener("submit", startRestore)

    //Обработка закрытия окна
    document.getElementById("BackupRestoreAlert").addEventListener("click", function () {
        setDefaults();
        document.getElementById("BackupRestoreAlert").classList.add("hidden");
    })

    //Клик на окно не вызывает его закрытия
    document.getElementById("inner_block_task_alert").addEventListener("click", function (e) {
        e.stopPropagation();
    })

});

/**
 * Обработчик события клика на кнопку "восстановить"
 * @param block_id
 */
function showBackupRestoreAlert(block_id) {
    let backupBtn = document.getElementById(block_id);
    let copyData = JSON.parse(backupBtn.dataset.copy);
    showAlert(copyData);
}

/**
 * Показать диалоговое окно
 * @param copyData
 */
function showAlert(copyData) {
    document.getElementById("BackupRestoreAlert").classList.remove("hidden");

    copyData.bucketTypes.forEach(bucketType => {
        showBucketType(bucketType)
    });

    copyData.types.forEach(backupType => {
        addOptionToType(backupType)
    });

    document.getElementById("restore_copy_task_id").value = copyData.task_id;
    document.getElementById("restore_copy_date").value = copyData.date;
}

/**
 * Показать источник в форме
 * @param type
 */
function showBucketType(type) {
    document.getElementById("from_bucket_type_" + type).classList.remove("hidden");
}

/**
 * Добавить элемент в поле "Что восстанавливать?"
 *
 * @param type
 * @returns {boolean}
 */
function addOptionToType(type) {
    if (type === "clients") {
        return true;
    }

    let name = backupTypes[type];
    if (!name) {
        throw new Error("Тип бекапа " + type + " не существует")
    }

    document.getElementById("restore_type").innerHTML += "<option value='" + type + "'>" + name + "</option>"
}

/**
 * Обработчик отправки формы
 *
 * @param e
 * @returns {Promise<void>}
 */
async function startRestore(e) {
    e.preventDefault();
    let form = new FormData(document.getElementById("restore_form"));
    form = Object.fromEntries(form);

    await sendStartRequest(form);
}

/**
 * Отправить запрос на старт восстановления
 *
 * @param data
 * @returns {Promise<void>}
 */
async function sendStartRequest(data) {
    data = encodeURIComponent(JSON.stringify(data));
    document.getElementById("loaderBackupTask").classList.remove("hidden");
    let response = await fetch("/admin/autobackup/api/restore/start/?request=" + data);
    document.getElementById("loaderBackupTask").classList.add("hidden");

    if (!response.ok) {
        alert("Ошибка. Проверьте консоль (F12)");
        return console.log(await response, "Ответ сервера: " + await response.text());
    }

    let result = await response.json();

    document.location.href = "/admin/autobackup/restore/progress/" + result.uid + "?data=" + data;
}


//////////////////////////////////////
//// Скачивание
//////////////////////////////////////


document.addEventListener("DOMContentLoaded", function () {
    //Обработка закрытия окна
    document.getElementById("BackupRestoreDownload").addEventListener("click", function () {
        setDownloadDefaults();
        document.getElementById("BackupRestoreDownload").classList.add("hidden");
    })

    //Клик на окно не вызывает его закрытия
    document.getElementById("inner_block_download_copy").addEventListener("click", function (e) {
        e.stopPropagation();
    })
});

function showBackupDownloadAlert(block_id) {
    let backupBtn = document.getElementById(block_id);
    let copyData = JSON.parse(backupBtn.dataset.copy);
    showDownloadAlert(copyData);
}

function showDownloadAlert(copyData) {
    copyData.types.forEach(backupType => {
        showDownloadLink(backupType, copyData.date, copyData.task_id);
    });
    document.getElementById("BackupRestoreDownload").classList.remove("hidden");
}

function showDownloadLink(backup_type, date, task_id) {
    let name = backupTypes[backup_type];
    if (!name) {
        throw new Error("Тип бекапа " + backup_type + " не существует")
    }

    document.getElementById("backup_download_" + backup_type).classList.remove("hidden");
    document.getElementById("backup_download_" + backup_type).href = "/admin/autobackup/copys/download/?date=" + date + "&task_id=" + task_id + "&type=" + backup_type;
}

function setDownloadDefaults() {
    document.getElementById("backup_download_file").classList.add("hidden");
    document.getElementById("backup_download_db").classList.add("hidden");
    document.getElementById("backup_download_clients").classList.add("hidden");
}