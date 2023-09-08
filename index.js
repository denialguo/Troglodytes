function alertButton() {
    window.location.replace("daniel/Daniel.html")
}

function alertButton1() {
    const origin = window.location.origin;
    const path = window.location.pathname;

    if (path.startsWith('/thebig/')) {
        // You are in the 'thebig' folder, so navigate to the parent folder
        window.location.replace(origin + path.replace('/thebig/', '/') + 'index.html');
    } else {
        // You are in the root folder or a different subfolder, so navigate to 'thebig/index.html'
        window.location.replace(origin + '/thebig/index.html');
    }
}

function sanWeb() {
    window.location.replace("FirstWeb/san.html")
}



