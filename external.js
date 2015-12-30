/**
 * External JS file for kPlaylist
 */

var audio = new Audio();
function song(obj) {
  audio.pause(); audio = new Audio(obj.href); audio.play();
  return false;
}

function video(obj) {
  var parent = document.getElementById("html5container");
  var child = document.getElementById("html5video");
  if (child) {
    parent.removeChild(child);
    var newvideo = document.createElement("video");
    newvideo.id = "html5video";
    newvideo.onclick = function() {
      this.play();
    };
    parent.appendChild(newvideo); 
  }
  var arVideos = document.getElementsByTagName("video");
  var video = arVideos[0];
  video.setAttribute("style","display:block");
  video.src=obj.href; video.play();
  return false;
}
