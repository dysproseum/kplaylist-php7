/**
 * External JS file for kPlaylist.
 *
 * Provides HTML5 player.
 */

var player;
var current;
var listener = false;
var checkedOnly = true;
var trackListing = [];

// Get session value for playback.
function getCookie(name) {
  var x = document.cookie.split(';');
  for(i=0; i<x.length; i++) {
    var parts = x[i].split('=');
    if (parts[0] == name) {
      return parts[1];
    }
  }
}

// Find next playable link in playlist.
function findNextSong(obj) {
  // Randomizer.
  if (trackListing.length > 0) {
    for (i=0; i<trackListing.length; i++) {
      if (trackListing[i] == obj) {
        if (trackListing[i + 1]) {
          return trackListing[i + 1];
        }
      }
    }
    return false;
  }

  // Song listing.
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
  var active = document.querySelectorAll("span.filemarked, a.filemarked");
  for (i=0; i < active.length; i++) {
    active[i].classList.remove("filemarked");
    active[i].classList.add("file");
  }
  if (link && link.children) {
    var target = link.children[0];
    if (target) {
      target.classList.add("filemarked");
    }
    else {
      link.classList.add("filemarked");
    }
  }
}

// Behavior to play next song in playlist.
function playlist(obj, player) {
  current = obj;

  listener = function () {
    console.log("Event: playback ended");
    var link = findNextSong(current);
    if (link.href) {
      player.src=link.href;
      var playPromise = player.play();
      setActive(link);
    }
    else if (link) {
      player.src=link;
      var playPromise = player.play();
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
    x[i].href = x[i].href.replace('index.php?sid', 'index.php?seek_stream');
  }
  return x;
}

// Get last streams
function getLastStreams() {
  var x = document.querySelectorAll("#streams a.wtext, #streams a.filemarked");
  for(i=0; i<x.length; i++) {
    x[i].href = x[i].href.replace('index.php?sid', 'index.php?seek_stream');
  }
  return x;
}

// Callback function to execute when mutations are observed.
const callback = (mutationList, observer) => {
  for (const mutation of mutationList) {
    if (mutation.type === "childList") {
      x = getLastStreams();
      for(i=0; i<x.length; i++) {
        x[i].addEventListener("click", function(e) {
          e.preventDefault();

          checkedOnly = false;
          player.pause();
          player.src = this.href;
          player.hidden = false;
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
    }
  }
};

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

  // Last streams.
  const targetNode = document.getElementById("streams");
  const config = { attributes: true, childList: true, subtree: true };
  const observer = new MutationObserver(callback);
  observer.observe(targetNode, config);

  // Later, you can stop observing
  // observer.disconnect();

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

// Randomizer.
window.playerParentFunction = function(tracks) {
  var cookie = getCookie('kplaylist');

  for(i=0; i<tracks.length; i++) {
   var url ='index.php?seek_stream=' + tracks[i].value + '&c=' + cookie;
   trackListing.push(url);
  }

  playlist(trackListing[0], player);
  player.pause();
  player.hidden = false;
  player.src=trackListing[0];
  var playPromiise = player.play();

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
