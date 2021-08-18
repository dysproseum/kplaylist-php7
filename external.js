/**
 * External JS file for kPlaylist
 */

var html5player = false;
var current;

function playlist(obj, player) {
  current = obj;
  var player_type = obj.className;
  player.addEventListener('ended', function () {
    var this_tr = current.parentElement.parentElement.parentElement;
    var tbody = this_tr.parentElement;
    var next_tr = false;
    for(i=0; i < tbody.children.length; i++) {
      if (tbody.children[i] == this_tr) {
        next_tr = tbody.children[i + 1];
      }
    }
    var td = next_tr.children[0];
    var span = false;
    for(i=1; i < td.children.length; i++) {
      if (td.children[i].className == "file " + player_type) {
        span = td.children[i];
      }
    }
    var link = span.children[0];
    player.pause();
    player.src=link.href;
    player.play();
    current = link;
  })
  return false;
}

function play_html5audio(obj) {
  if (html5player) {
    var video = document.getElementById('html5video');
    video.pause();
    video.hidden = true;
  }
  html5player = true;
  var audio = document.getElementById('html5audio');
  audio.pause();
  audio.hidden = false;
  audio.src = obj.href;
  audio.play();
  playlist(obj, audio);
  return false;
}


function play_html5video(obj) {
  if (html5player) {
    var audio = document.getElementById('html5audio');
    audio.pause();
    audio.hidden = true;
  }
  html5player = true;
  var video = document.getElementById("html5video");
  video.pause();
  video.hidden = false;
  video.src=obj.href;
  video.play();
  playlist(obj, video);

  return false;
}
