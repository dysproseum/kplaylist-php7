// Make the DIV element draggable

var elmntOffsetWidth = 20;
var elmntOffsetHeight = 20;

function dragElement(elmnt) {
  var offsetLeft = 0, offsetTop = 0;
  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
  var elmntHeader = elmnt.querySelector(".titlebar");

  if (elmntHeader) {
    // if present, the header is where you move the DIV from:
    elmntHeader.onmousedown = dragMouseDown;
  } else {
    // otherwise, move the DIV from anywhere inside the DIV:
    elmnt.onmousedown = dragMouseDown;
  }

  function dragMouseDown(e) {
    if (e.button == 2) {
      return;
    }

    // target could be div or h1
    var target = e.target.parentElement;
console.log(target);
    if (!target.classList.contains("browser")) {
      target = target.parentElement;
console.log(target);
    }

    e = e || window.event;
    e.preventDefault();
    // get the mouse cursor position at startup:
    pos3 = e.clientX;
    pos4 = e.clientY;
    offsetLeft = elmnt.offsetLeft - elmntOffsetWidth;
    offsetTop = elmnt.offsetTop - elmntOffsetHeight;
    // call a function whenever the cursor moves:
    document.onmouseup = closeDragElement;
    document.onmousemove = elementDrag;

    elmnt.classList.add('dragging');

    // Set window layer.
    windowManager.setWindowLayer(target);
  }

  function elementDrag(e) {
    e = e || window.event;
    e.preventDefault();
    // calculate the new cursor position:
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    // set the element's new position:
    elmnt.style.left = (offsetLeft - pos1) +  "px";
    elmnt.style.top = (offsetTop - pos2) + "px";
  }

  function closeDragElement() {
    // stop moving when mouse button is released:
    document.onmouseup = null;
    document.onmousemove = null;

    elmnt.classList.remove('dragging');
  }
}
