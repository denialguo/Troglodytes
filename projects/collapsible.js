var coll = document.getElementsByClassName("collapsible");
var i;

const openCollapsible = function() {
  this.classList.toggle("collapsible-active");
  var content = this.nextElementSibling;
  if (content.style.maxHeight){
    content.style.maxHeight = null;
  } else {
    content.style.maxHeight = content.scrollHeight + "px";
  }
}


for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("collapsible-active");
    var content = this.nextElementSibling;
    if (content.style.maxHeight){
      content.style.maxHeight = null;
    } else {
      content.style.maxHeight = content.scrollHeight + "px";
    }
  });
}
coll[0].classList.toggle("collapsible-active");
coll[0].nextElementSibling.style.maxHeight = coll[0].nextElementSibling.scrollHeight + "px";