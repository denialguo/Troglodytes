function toIndex() {
    window.location.href = '../Index/index.html';
}
//BUG: it works alright after hovering over the image and then off of it, but you do it again and it doesn't work. it seems to work every alternate time i move my mouse into and out of the image.
function imgHover() {
    const img = document.getElementById("irithyll");
    img.classList.toggle("fadeInImgAnimation");
    console.log("on the image");
}
function imgNoHover() {
    const img = document.getElementById("irithyll");
    img.classList.toggle("fadeOutImgAnimation");
    console.log("off the image");
}