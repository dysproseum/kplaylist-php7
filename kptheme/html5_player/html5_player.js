/**
 * External JS file for kPlaylist.
 *
 * Provides HTML5 player.
 */

var player;
var current;
var listener = false;
var checkedOnly = true;

// Find next playable link in playlist.
function findNextSong(obj) {
  var this_tr = obj.parentElement.parentElement;
  var next_tr = this_tr.nextElementSibling;
  var td;
  var link;
  while(next_tr) {
    td = next_tr.children[0];

    if (checkedOnly) {
      checked = td.querySelector("input[type=checkbox]:checked");
      if (checked.length == 0) {
        continue;
      }
    }

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
    console.log("Event: playback ended");
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

  return false;
}

// Get selected tracks.
function getSelectedTracks() {
  var x = document.querySelectorAll('form[name=psongs] input[type=checkbox]:checked');
  var tracks = [];
  for(i=0; i<x.length; i++) {
    var y = x[i].nextElementSibling;
    var a = y.nextElementSibling;
    if (a.href.indexOf('index.php?pwd') != -1) {
      a = y.nextElementSibling.nextElementSibling;
    }
    tracks.push(a);
  }
  return tracks;
}

// Get songs and replace link with stream url.
function getAllTracks() {
  var x = document.querySelectorAll("form[name=psongs] a:has(> span)");
  for(i=0; i<x.length; i++) {
    x[i].href = x[i].href.replace('index.php?sid', 'index.php?streamsid');
  }
  return x;
}

window.addEventListener("load", function() {
  player = document.getElementById('html5player');

  // Play Album.
  var p = document.getElementsByName("psongsall");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getAllTracks();

      checkedOnly = false;
      playlist(tracks[0], player);
      player.pause();
      player.hidden = false;
      player.src = tracks[0].href;
      var playPromise = player.play();
      setActive(tracks[0]);

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

  // Play selected.
  var r = document.getElementsByName("psongsselected");
  var s = r[0];
  s.addEventListener("click", function(e) {
    e.preventDefault();

    var tracks = getSelectedTracks();
    if (tracks.length > 0) {
      checkedOnly = true;
      playlist(tracks[0], player);
      player.pause();
      player.hidden = false;
      player.src = tracks[0].href;
      var playPromise = player.play();
      setActive(tracks[0]);

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
    }

    return false;
  });

  // Override song links to start player.
  var x = getAllTracks();
  for(i=0; i<x.length; i++) {
    x[i].addEventListener("click", function(e) {
      e.preventDefault();

      checkedOnly = false;
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