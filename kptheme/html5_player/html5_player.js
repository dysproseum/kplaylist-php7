/**
 * External JS file for kPlaylist.
 *
 * Provides HTML5 player.
 */

var html5player = false;
var player_type;
var current;
var listener = false;
var listener_count = 0;

// Find next playable link in playlist.
function findNextSong(obj) {
  var this_tr = obj.parentElement.parentElement;
  var next_tr = this_tr.nextElementSibling;
  var td;
  var link;
  while(next_tr) {
    td = next_tr.children[0];
    link = td.querySelector("a:has(span)");
    if (link) {
      return link;
    }
    next_tr = next_tr.nextElementSibling;
  }
}

// Set visual indication of playing song.
function setActive(link=false) {
  var active = document.querySelectorAll("span.filemarked");
  for (i=0; i < active.length; i++) {
    active[i].classList.remove("filemarked");
    active[i].classList.add("file");
  }
  if (link) {
    var target = link.children[0];
    target.classList.add("filemarked");
  }
}

// Behavior to play next song in playlist.
function playlist(obj, player) {
  current = obj;

  listener = function () {
    console.log("Event: playback ended, listener count: " + listener_count);
    var link = findNextSong(current);
    if (link) {
      player.src=link.href;
      var playPromise = player.play();
      setActive(link);
    }
    else {
      setActive();
    }

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
  setActive(obj);

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

// Get songs and replace link with stream url.
function getAllTracks() {
  var x = document.querySelectorAll("form[name=psongs] a:has(> span)");
  for(i=0; i<x.length; i++) {
    x[i].href = x[i].href.replace('sid', 'seek_stream');
  }
  return x;
}

// Override song links to start player.
window.addEventListener("load", function() {
  var x = getAllTracks();
  var player = document.getElementById('html5audio');
  for(i=0; i<x.length; i++) {
    x[i].addEventListener("click", function(e) {
      e.preventDefault();

      playlist(this, player);
      player.pause();
      player.hidden = false;
      player.src = this.href;
      var playPromise = player.play();
      setActive(this);

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

      return false;
    });
  }
});
