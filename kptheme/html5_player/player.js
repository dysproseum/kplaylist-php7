  let player;
  let childCallbacks = [];
  // Store tracks in player iframe in case the index page changes.
  let trackListing = [];
  let current;

  // Player callback.
  // tracks: trackListing array.
  // num: optional pointer to track number.
  function playerFrame(tracks, num=0) {
    trackListing = tracks;
    current = tracks[num];
    player.pause();
    player.src=tracks[num];
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
  }

  // Register callback.
  window.onload = function(){
    if (parent && parent.registerPlayerChild){
      parent.registerPlayerChild(playerFrame);
    }
  };

  window.addEventListener("load", function() {
    childCallbacks = parent.getIndexCallbacks();
    player = document.getElementById("html5player");

    player.addEventListener("ended", function() {
      var nextSong;

      if (trackListing.length > 0) {
        for (i=0; i<trackListing.length; i++) {
          if (trackListing[i] == current) {
            if (trackListing[i + 1]) {
              nextSong = trackListing[i + 1];
            }
            else {
              return false;
            }
          }
        }
      }

      current = nextSong;
      player.pause();
      player.src = nextSong;
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
    });
  });
