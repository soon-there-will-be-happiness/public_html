function setProgress(progress) {
    document.getElementById("progressInt").innerText = progress;
    document.getElementById("progressbar").style.width = progress + "%";
}