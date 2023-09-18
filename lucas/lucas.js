let loadTime = Date.now();
function update() {
  document.querySelector("#countup").innerText = Math.floor((Date.now() - loadTime)/1000);
}

setInterval(update, 1000)

function alertStuff() {
  alert("bruh")
}

function refreshColor() {
    let r = Math.floor(Math.random() * 256)
    let g = Math.floor(Math.random() * 256)
    let b = Math.floor(Math.random() * 256)
    document.body.style.backgroundColor = `rgb(${r},${g},${b})`
}