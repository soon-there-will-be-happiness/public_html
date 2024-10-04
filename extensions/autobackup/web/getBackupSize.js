class BackupTask {

    constructor() {
        this.enabled = {
            "db": document.getElementById("backupEnableDb").checked,
            "files": document.getElementById("backupEnableFiles").checked,
            "clients": document.getElementById("backupEnableClients").checked,
        }
        this.exclude = {
            "folders": [],
            "files": [],
        }
        this.getFilesExclude();
        this.getFoldersExclude();
        this.setEventListeners();
        this.requestToServer();
    }

    //Получить выбранные значения из селекта "Исключить папки"
    getFoldersExclude() {
        this.exclude.folders = [];
        let select = document.getElementById("backupFolderExclude");
        for (let i = 0; i < select.options.length; i++) {
            //если опция выбрана - добавим её в массив
            if (select.options[i].selected)
                this.exclude.folders.push(select.options[i].value);
        }
    }

    //Получить массив исключенных файлов из строки
    getFilesExclude() {
        let filesExcludeField = document.getElementById("filesExcludeField");
        this.exclude.files = filesExcludeField.value.trim().split('\n');
    }

    //Установить обработчики событий
    setEventListeners() {
        document.getElementById("backupEnableFiles").addEventListener("input", event => this.updateEnable(event, this));
        document.getElementById("backupDisableFiles").addEventListener("input", event => this.updateEnable(event, this));
        document.getElementById("backupEnableClients").addEventListener("input", event => this.updateEnable(event, this));
        document.getElementById("backupDisableClients").addEventListener("input", event => this.updateEnable(event, this));
        document.getElementById("backupEnableDb").addEventListener("input", event => this.updateEnable(event, this));
        document.getElementById("backupDisableDb").addEventListener("input", event => this.updateEnable(event, this));
        document.getElementById("filesExcludeField").addEventListener("input", event => this.updateEnable(event, this));
        $('#backupFolderExclude').on('change', event => this.updateEnable(event, this));

        document.getElementById("backupForm").addEventListener("submit", event => this.submitForm(event, this));
    }

    //Обновить данные
    updateEnable(event, obj) {
        obj.enabled = {
            "db": document.getElementById("backupEnableDb").checked,
            "files": document.getElementById("backupEnableFiles").checked,
            "clients": document.getElementById("backupEnableClients").checked,
        }
        obj.getFilesExclude();
        obj.getFoldersExclude();
        obj.requestToServer();
    }

    //Получить данные о размере и показать их
    async requestToServer() {
        this.onLoadingAnimation();

        let request = await fetch('/admin/autobackup/api/getbackupsize?json=' + this.toJson());
        let responseCode = request.status;

        if (responseCode !== 201) {
            console.log(request, await request.text());
            alert("Произошла ошибка подсчета размера бекапа");
            return false;
        }

        let response = await request.json();

        this.offLoadingAnimation();
        this.showBackupSizeText(response.files, response.db, response.clients);
    }

    //Показать размер бекапа
    showBackupSizeText(files, db, clients) {
        let allSize = (files + db + clients) / 1000 / 1000;
        let filesSize = files / 1000 / 1000;
        let dbSize = db / 1000 / 1000;
        let clientsSize = clients / 1000 / 1000;
        document.getElementById("backupAllSize").innerText = Math.ceil(allSize + 1) + " мб";
        document.getElementById("backupFilesSize").innerText = Math.ceil(filesSize) + " мб";
        document.getElementById("backupDbSize").innerText = Math.ceil(dbSize) + " мб";
        document.getElementById("backupClientsSize").innerText = Math.ceil(clientsSize) + " мб";
    }

    //Включить анимацию загрузки
    onLoadingAnimation() {
        document.getElementById("backupAllSize").classList.add("loading");
        document.getElementById("backupFilesSize").classList.add("loading");
        document.getElementById("backupDbSize").classList.add("loading");
        document.getElementById("backupClientsSize").classList.add("loading");
    }
    //Выключить анимацию загрузки
    offLoadingAnimation() {
        document.getElementById("backupAllSize").classList.remove("loading");
        document.getElementById("backupFilesSize").classList.remove("loading");
        document.getElementById("backupDbSize").classList.remove("loading");
        document.getElementById("backupClientsSize").classList.remove("loading");
    }

    submitForm(event, obj) {
        let selected = document.getElementById("backup_selected_storages");
        if (selected.value === "") {
            event.preventDefault();
            alert("Не выбраны хранилища");
        }
    }

    toJson() {
        return JSON.stringify(this);
    }
}
new BackupTask();