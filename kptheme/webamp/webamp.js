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

  // Initialize webamp.
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
    availableSkins: [
      {
        url: "kptheme/webamp/skins/311-AMP_by_Jamie_2.wsz",
        name: "311 Amp",
      },
      {
        url: "kptheme/webamp/skins/Blue_Sacrem.wsz",
        name: "Blue Sacrem",
      },
      {
        url: "kptheme/webamp/skins/Breedamp_-_Organica.wsz",
        name: "Breedamp - Organica",
      },
      {
        url: "kptheme/webamp/skins/cute_penguin_skin.wsz",
        name: "Cute Penguin Skin",
      },
      {
        url: "kptheme/webamp/skins/Ferrari_Winamp_Skin_v_02_03.wsz",
        name: "Ferrari",
      },
      {
        url: "kptheme/webamp/skins/FlyingCircle2000Worldedition.wsz",
        name: "Flying Circle",
      },
      {
        url: "kptheme/webamp/skins/FreeFall.wsz",
        name: "FreeFall",
      },
      {
        url: "kptheme/webamp/skins/Frequency.wsz",
        name: "Frequency",
      },
      {
        url: "kptheme/webamp/skins/Game_Boy_Amp.wsz",
        name: "Game Boy Amp",
      },
      {
        url: "kptheme/webamp/skins/glass_one.wsz",
        name: "Glass One",
      },
      {
        url: "https://archive.org/cors/winampskin_Green-Dimension-V2/Green-Dimension-V2.wsz",
        name: "Green Dimension V2",
      },
      {
        url: "kptheme/webamp/skins/HelstegtPattegris.wsz",
        name: "Helstegt Pattegris",
      },
      {
        url: "kptheme/webamp/skins/isaac_kearns.wsz",
        name: "Isaac Kearns",
      },
      {
        url: "kptheme/webamp/skins/Knotty_Skin_By_Void.wsz",
        name: "Knotty Skin",
      },
      {
        url: "kptheme/webamp/skins/LINK2PST.wsz",
        name: "Link to the Past",
      },
      {
        url: "https://archive.org/cors/winampskin_mac_os_x_1_5-aqua/mac_os_x_1_5-aqua.wsz",
        name: "Mac OSX v1.5 (Aqua)",
      },
      {
        url: "kptheme/webamp/skins/MEDITERRANEO.wsz",
        name: "Mediterraneo",
      },
      {
        url: "kptheme/webamp/skins/Milermog.wsz",
        name: "Milermog",
      },
      {
        url: "kptheme/webamp/skins/NoOneLivesFFULL.wsz",
        name: "No One Lives",
      },
      {
        url: "kptheme/webamp/skins/Nucleo_NLog_v102_.wsz",
        name: "Nucleo",
      },
      {
        url: "kptheme/webamp/skins/Ohm.wsz",
        name: "Ohm",
      },
      {
        url: "kptheme/webamp/skins/Paper-Amp.wsz",
        name: "Paper Amp",
      },
      {
        url: "kptheme/webamp/skins/RatchetsGame.wsz",
        name: "Ratchets",
      },
      {
        url: "kptheme/webamp/skins/SD_-_White_Edition.wsz",
        name: "SD - White Edition",
      },
      {
        url: "kptheme/webamp/skins/SFERA.wsz",
        name: "SFERA",
      },
      {
        url: "kptheme/webamp/skins/sonympfx3lcdv111.wsz",
        name: "Sony MP3 FX",
      },
      {
        url: "kptheme/webamp/skins/SummerBreeze.wsz",
        name: "Summer Breeze",
      },
      {
        url: "kptheme/webamp/skins/Template_Amp.wsz",
        name: "Template Amp",
      },
      {
        url: "kptheme/webamp/skins/The_Universes_Beauty.wsz",
        name: "The Universe's Beauty",
      },
      {
        url: "kptheme/webamp/skins/VItalIz0r_0rAngE.wsz",
        name: "VItalIz0r 0rAngE",
      },
      {
        url: "kptheme/webamp/skins/Waiora_3000.wsz",
        name: "Waiora 3000",
      },
      {
        url: "kptheme/webamp/skins/WEEZER.wsz",
        name: "Weezer",
      },
      {
        url: "kptheme/webamp/skins/Winamp_For_Windows_XP.wsz",
        name: "Windows XP",
      },
      {
        url: "kptheme/webamp/skins/Winamp_XP_SP1_Olive.wsz",
        name: "Windows XP SP1 Olive",
      },
      {
        url: "kptheme/webamp/skins/Winamp_XP_SP1_Silver.wsz",
        name: "Windows XP SP1 Silver",
      },
      {
        url: "kptheme/webamp/skins/Winamp_XP.wsz",
        name: "Winamp XP",
      },
      {
        url: "kptheme/webamp/skins/WINTENDO.wsz",
        name: "Wintendo",
      },
      {
        url: "kptheme/webamp/skins/winXP.wsz",
        name: "WinXP",
      },
      {
        url: "kptheme/webamp/skins/XPAmp11.wsz",
        name: "XPAmp11",
      },
      {
        url: "kptheme/webamp/skins/zeus_v2_anoxia.wsz",
        name: "Zeus V2 Anoxia",
      },
    ],
    initialTracks: [
      {
        metaData: {
          artist: "DJ Mike Llama",
          title: "Llama Whippin' Intro",
        },
        // NOTE: Your audio file must be served from the same domain as your HTML
        // file, or served with permissive CORS HTTP headers:
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
        url: "https://cdn.jsdelivr.net/gh/captbaritone/webamp@43434d82cfe0e37286dbbe0666072dc3190a83bc/mp3/llama-2.91.mp3",
        duration: 5.322286,
      },
    ],
    enableHotkeys: true,
  });

  var webAmpPromise = webAmp.renderWhenReady(app);
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
