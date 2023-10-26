function toIshaan() {
    window.location.href = '../ishaan/ishaan.html';
}
function toLucas() {
    window.location.href = '../lucas/lucas.html';
}
function toRajdeep() {
    window.location.href = '../rajdeep/rajdeep.html';
}
function toAkash() {
    window.location.href = '../akash/akash.html';
}
function toDaniel() {
    window.location.href = ("../daniel/Daniel.html");
}

function toIndex() {
    window.location.href = ("../Index/index.html");
}
function toProjects() {
    window.location.href = ("../projects/projects.html");
}

function toSantiago() {
    window.location.href = '../santiago/san.html';
}
function redirectTo(name) {
    let urlMapping = {
        'ishaan': '../ishaan/ishaan.html',
        'lucas': '../lucas/lucas.html',
        'rajdeep': '../rajdeep/rajdeep.html',
        'akash': '../akash/akash.html',
        'daniel': '../daniel/Daniel.html',
        'santiago': '../santiago/san.html'
    };

    if (urlMapping[name]) {
        window.location.href = urlMapping[name];
    } else {
        console.error('Invalid name provided.');
    }
}

function createanAccount() {
    window.location.replace("../login/signup.php");
}

function logIn() {
    window.location.replace("../login/login.php");
}

function OverlayOn() {
    document.getElementById('MenuOverlay').style.display = 'flex';
}
function OverlayOff() {
    document.getElementById("MenuOverlay").style.display = "none";
}



