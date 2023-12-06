function changeSlide(slideshow, n) {
    const imgs = document.getElementById(slideshow).querySelectorAll('.slider img');
    const dots = document.querySelectorAll('.dot');

    for (var i = 0; i < imgs.length; i++) { // reset
      imgs[i].style.opacity = 0;
      dots[i].className = dots[i].className.replace(' active', '');
    }
  
    currentImg = n;
  
    imgs[currentImg].style.opacity = 1;
    dots[currentImg].className += ' active';
}