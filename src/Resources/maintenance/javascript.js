var check = setInterval(function(){ checkForMaintentance() }, 30000);

function checkForMaintentance() {
    if (document.title.indexOf("maintenance") == -1) {
        clearInterval(check);
        location.reload(true);
    }
}
