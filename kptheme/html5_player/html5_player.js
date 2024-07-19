/* html5_player kplaylist theme */

var childCallbacks = [];

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

          for (var i=0; i < childCallbacks.length; i++){
            var callback = childCallbacks[i];
            callback([this.href]);
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
    var song = getLastStreams();
    return song;
  }
}

window.addEventListener("load", function() {
  childCallbacks = parent.getPlayerCallbacks();

  // Register callback.
  if (parent && parent.registerIndexChild){
    parent.registerIndexChild(indexFrame);
  }

  // Play Album.
  var p = document.getElementsByName("psongsall");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getAllTracks();
      var trackListing = [];
      for (var i=0; i<tracks.length; i++) {
        trackListing.push(tracks[i].href);
      }

      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        callback(trackListing);
      }
      setActive(tracks[0]);

      return false;
    });
  }

  // Play selected.
  var r = document.getElementsByName("psongsselected");
  if (r.length > 0) {
    var s = r[0];
    s.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getSelectedTracks();
      var trackListing = [];
      if (tracks.length <= 0) {
       return;
      }
      for (var i=0; i<tracks.length; i++) {
        trackListing.push(tracks[i].href);
      }

      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        callback(trackListing);
      }
      setActive(tracks[0]);

      return false;
    });
  }

  // Last streams.
  const targetNode = document.getElementById("streams");
  const config = { attributes: true, childList: true, subtree: true };
  const observer = new MutationObserver(callback);
  observer.observe(targetNode, config);

  // Later, you can stop observing
  // observer.disconnect();

  // Override song links to start player.
  var tracks = getAllTracks();
  var trackListing = [];
  for (var i=0; i<tracks.length; i++) {
    trackListing.push(tracks[i].href);
  }
  for(i=0; i<tracks.length; i++) {
    tracks[i].addEventListener("click", function(e) {
      e.preventDefault();

      var num = 0;
      // Get current spot.
      for (var j=0; j<tracks.length; j++) {
        if (this.href == trackListing[j]) {
          num = j;
        }
      }
      for (var k=0; k < childCallbacks.length; k++){
        var callback = childCallbacks[k];
        // Send trackListing and the current spot.
        callback(trackListing, num);
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

// Randomizer window calls this.
window.playerParentFunction = function(tracks) {
  trackListing = tracks;

  for (var i=0; i < childCallbacks.length; i++){
    var callback = childCallbacks[i];
    callback(trackListing);
  }
}
