let webAmp;
let childCallbacks = [];

function getAllTracks() {
  var x = document.querySelectorAll("form[name=psongs] a:has(> span)");
  var tracks = [];
  for(i=0; i<x.length; i++) {
    var track = {
      metaData: {
        title: x[i].innerText,
        artist: x[i].title,
      },
      url: x[i].href,
    };
    tracks.push(track);
  }
  return tracks;
}

function getSelectedTracks() {
  var x = document.querySelectorAll('form[name=psongs] input[type=checkbox]:checked');
  var tracks = [];
  for(i=0; i<x.length; i++) {
    var y = x[i].nextElementSibling;
    var a = y.nextElementSibling;
    if (a.href.indexOf('index.php?pwd') != -1) {
      a = y.nextElementSibling.nextElementSibling;
    }

    var track = {
      metaData: {
        title: a.innerText,
        artist: a.title,
      },
      url: a.href,
    };
    tracks.push(track);
  }
  return tracks;
}

// Get songs and replace link with stream url.
function getSongListing() {
  var x = document.querySelectorAll("form[name=psongs] a:has(> span)");
  for(i=0; i<x.length; i++) {
    x[i].href = x[i].href.replace('index.php?sid', 'index.php?seek_stream');
  }
  return x;
}

// Get last streams.
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

          var tracks = [];
          var track = {
            metaData: {
              title: this.innerText,
              artist: this.title,
            },
            url: this.href,
          };
          tracks.push(track);
          for (var i=0; i < childCallbacks.length; i++){
            var callback = childCallbacks[i];
            callback(tracks);
          }

          return false;
        });
      }
    }
  }
};

window.addEventListener("load", function() {
  childCallbacks = parent.getCallbacks();
  // Play Album.
  var p = document.getElementsByName("psongsall");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getAllTracks();
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        callback(tracks);
      }
      return false;
    });
  }

  // Play selected.
  var r = document.getElementsByName("psongsselected");
  var s = r[0];
  if (s) {
    s.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getSelectedTracks();
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        if (tracks.length > 0) {
          callback(tracks);
        }
      }
      return false;
    });
  }

  // Override song links to start player.
  var x = getSongListing();
  for(i=0; i<x.length; i++) {
    x[i].addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = [];
      var track = {
        metaData: {
          title: this.innerText,
          artist: this.title,
        },
        url: this.href,
      };
      tracks.push(track);
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        if (tracks.length > 0) {
          callback(tracks);
        }
      }

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

});

// Randomizer.
window.playerParentFunction = function(tracks) {
  for (var i=0; i < childCallbacks.length; i++) {
    var callback = childCallbacks[i];
    if (tracks.length > 0) {
      callback(tracks);
    }
  }
}
