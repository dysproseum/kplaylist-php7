/**
 * External JS file for kPlaylist.
 *
 * Provides HTML5 player.
 */

var current;
var listener = false;
var checkedOnly = true;
var trackListing = [];
var childCallbacks = [];

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
function playlist(obj) {
  current = obj;
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
          for (var i=0; i < childCallbacks.length; i++){
            var callback = childCallbacks[i];
            callback(this.href);
          }
          setActive(this);

          return false;
        });
      }
    }
  }
};

// Player callback.
function indexFrame(obj) {
  // First call may pass null argument.
  if (!obj) {
    return current;
  }
  else {
    var song = findNextSong(obj);
    playlist(song);
    setActive(song);
    return song;
  }
}

window.addEventListener("load", function() {
  childCallbacks = parent.getPlayerCallbacks();

  // Register callback.
  if (parent && parent.registerIndexChild){
    console.log('Registering with parent (index)');
    parent.registerIndexChild(indexFrame);
  }

  // Play Album.
  var p = document.getElementsByName("psongsall");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getAllTracks();

      checkedOnly = false;
      playlist(tracks[0]);
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        callback(tracks[0].href);
      }
      setActive(tracks[0]);

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
      playlist(tracks[0]);
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        callback(tracks[0].href);
      }
      setActive(tracks[0]);

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
      playlist(this);
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        callback(this.href);
      }
      setActive(this);

      return false;
    });
  }

});

// Get theme.
window.getTheme = function() {
  return theme;
}

// Randomizer.
window.playerParentFunction = function(tracks) {
  trackListing = tracks;

  for (var i=0; i < childCallbacks.length; i++){
    var callback = childCallbacks[i];
    callback(trackListing[0]);
    playlist(trackListing[0]);
  }
}
