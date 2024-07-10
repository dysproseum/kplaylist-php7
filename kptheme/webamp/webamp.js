let webAmp;

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
    x[i].href = x[i].href.replace('index.php?sid', 'index.php?streamsid');
  }
  return x;
}

// Get last streams
// * needs $cfg['livestreamajax'] = 0;
// * or else the changes will get replaced.
function getLastStreams() {
  var x = document.querySelectorAll("#streams a.wtext");
  for(i=0; i<x.length; i++) {
    x[i].href = x[i].href.replace('index.php?sid', 'index.php?streamsid');
  }
  return x;
}

// Get randomizer tracks.
function getRandomizerTracks() {
  var x = document.querySelector("form.randomizer #selids");
  return x.children;
}

window.addEventListener("load", function() {
  // Play Album.
  var p = document.getElementsByName("psongsall");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getAllTracks();
      webAmp.setTracksToPlay(tracks);
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
      if (tracks.length > 0) {
        webAmp.setTracksToPlay(tracks);
      }
      return false;
    });
  }

  // Randomizer.
  // @todo needs js include in form[name=randomizer].
  var p = document.getElementsByName("input[name=playselected]");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getRandomizerTracks();
      var sid = tracks[0].value;
      var title = tracks[0].innerText;

      webAmp.setTracksToPlay(tracks);

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
      webAmp.setTracksToPlay(tracks);

      return false;
    });
  }

  // Last streams.
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
      webAmp.setTracksToPlay(tracks);

      return false;
    });
  }

  const app = document.getElementById("app");
  webAmp = new Webamp({
    windowLayout: {
      main: {
        position: { top: 0, right: 0 },
        shadeMode: false,
        closed: false,
      },
      equalizer: {
        position: { top: 0, left: 0 },
        shadeMode: false,
        closed: true,
      },
      playlist: {
        position: { top: 0, right: 0 },
        shadeMode: false,
        size: { extraHeight: 1, extraWidth: 1 },
        closed: false,
      },
      milkdrop: {
        position: { top: 0, right: 0 },
        closed: true,
      },
    },
    __butterchurnOptions: {
      importButterchurn: () => Promise.resolve(window.butterchurn),
      getPresets: () => {
        const presets = window.butterchurnPresets.getPresets();
        return Object.keys(presets).map((name) => {
          return {
            name,
            butterchurnPresetObject: presets[name],
          };
        });
      },
      butterchurnOpen: true,
    },
    enableHotkeys: true,
  });

  var webAmpPromise = webAmp.renderWhenReady(app);

  // on promise.
  webAmpPromise.then(function(result) {
    setTimeout(function() {

      // Set window positions.
      webAmp.store.dispatch({
        absolute: true,
        positions: {
          "main": {
            x: window.innerWidth - 275,
            y: window.innerHeight - 116,
          },
          "equalizer": {
            x: window.innerWidth - 550,
            y: window.innerHeight - 116,
          },
          "playlist": {
            x: window.innerWidth - 275,
            y: window.innerHeight - 232,
          },
          "milkdrop": {
            x: window.innerWidth - 275,
            y: window.innerHeight - 348,
          },
        },
        type: "UPDATE_WINDOW_POSITIONS",
      });
  
      // Unhide player.
      var x = document.getElementById("webamp");
      x.style = "display: unset";
    }, 100);
  });

});
