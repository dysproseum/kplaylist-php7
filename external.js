/**
 * External JS file for kPlaylist
 */

var html5player = false;
var player_type;
var current;
var listener = false;
var listener_count = 0;

function playlist(obj, player) {
  current = obj;
  player_type = obj.className;

  listener = function () {
    console.log("Event: playback ended, listener count: " + listener_count);
    var this_tr = current.parentElement.parentElement.parentElement;
    var tbody = this_tr.parentElement;
    var next_tr = false;
    for(i=0; i < tbody.children.length; i++) {
      if (tbody.children[i] == this_tr) {
        next_tr = tbody.children[i + 1];
      }
    }
    if (!next_tr || next_tr.children.length == 0) {
      return;
    }
    var td = next_tr.children[0];
    var span = false;
    for(i=1; i < td.children.length; i++) {
      if (td.children[i].className == "file " + player_type) {
        span = td.children[i];
      }
    }
    var link = span.children[0];
    player.src=link.href;
    var playPromise = player.play();

    // In browsers that don’t yet support this functionality,
    // playPromise won’t be defined.
    if (playPromise !== undefined) {
      playPromise.then(function() {
        // Automatic playback started!
      }).catch(function(error) {
        // Automatic playback failed.
        // Show a UI element to let the user manually start playback.
        console.log(error);
      });
    }
    current = link;
  };

  player.addEventListener('ended', listener);
  listener_count++;

  return false;
}

function play_html5audio(obj) {
  var video;
  if (html5player && player_type == 'html5video') {
    video = document.getElementById('html5video');
    if (video) {
      video.pause();
      video.hidden = true;
    }
  }
  html5player = true;
  var audio = document.getElementById('html5audio');
  if (listener) {
    if (player_type == 'html5audio') {
      audio.removeEventListener('ended', listener);
    }
    else if (player_type == 'html5video') {
      video.removeEventListener('ended', listener);
    }
    listener_count--;
  }
  audio.pause();
  audio.hidden = false;
  audio.src = obj.href;
  var playPromise = audio.play();

  // In browsers that don’t yet support this functionality,
  // playPromise won’t be defined.
  if (playPromise !== undefined) {
    playPromise.then(function() {
      // Automatic playback started!
      //audio.removeEventListener('ended');
      playlist(obj, audio);
    }).catch(function(error) {
      // Automatic playback failed.
      // Show a UI element to let the user manually start playback.
      console.log(error);
    });
  }

  return false;
}

function play_html5video(obj) {
  var audio;
  if (html5player) {
    audio = document.getElementById('html5audio');
    if (audio) {
      audio.pause();
      audio.hidden = true;
    }
  }
  html5player = true;
  var video = document.getElementById("html5video");
  if (listener) {
    if (player_type == 'html5audio') {
      audio.removeEventListener('ended', listener);
    }
    else if (player_type == 'html5video') {
      video.removeEventListener('ended', listener);
    }
    listener_count--;
  }
  video.pause();
  video.hidden = false;
  video.src=obj.href;
  var playPromise = video.play();

  // In browsers that don’t yet support this functionality,
  // playPromise won’t be defined.
  if (playPromise !== undefined) {
    playPromise.then(function() {
      // Automatic playback started!
      playlist(obj, video);
    }).catch(function(error) {
      // Automatic playback failed.
      // Show a UI element to let the user manually start playback.
      console.log(error);
    });
  }

  return false;
}
