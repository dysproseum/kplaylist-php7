<html>
<head>
<title>| kPlaylist</title>
<style type="text/css">
  body {
    margin: 0;
  }
</style>
<script type="text/javascript">
  let theme = '';
  let player;
  let index;
  let playerCallbacks = [];
  let indexCallbacks = [];

  // Player calls these.
  function registerPlayerChild(callback){
    playerCallbacks.push(callback);
  }

  function getIndexCallbacks() {
    return indexCallbacks;
  }

  // Index calls these.
  function registerIndexChild(callback){
    indexCallbacks.push(callback);
  }

  function getPlayerCallbacks() {
    return playerCallbacks;
  }

  /*
   * Init theme and player iframe.
   * Set player iframe src dynamically based on theme.
   *
   * Login:
   * - First page load iframe.php
   * - The theme js is not loaded on index frame login page.
   * - And contentWindow.getTheme is not callable.
   * - The index frame refreshes and player src is loaded.
   *
   * Logout:
   * - Can no longer call getTheme.
   * - Unset theme and unset player iframe src.
   */
  function init() {
    player = document.getElementById("player");

    // Unable to get current user theme.
    if (!index.contentWindow.getTheme) {
      console.log("Unable to get theme setting");
    }

    // Detect logout.
    if (theme != '' && !index.contentWindow.getTheme) {
      console.log("Logout detected");
      theme = '';
      player.src = '';
      return;
    }

    // Logged out.
    if (theme == '' && !index.contentWindow.getTheme) {
      return;
    }

    // No theme set, set current value.
    if (theme == '') {
      theme = index.contentWindow.getTheme();
      console.log("Loaded " + theme + " theme");
    }

    // Init player iframe if theme is set and src is not set.
    if (player.src == '' || player.src.indexOf('iframe.php') != -1) {
      player.src = "kptheme/" + theme + "/player.php";
    }

    // Set iframe sizes.
    if (theme == "html5_player") {
      player.width = '100%';
      player.height = '54px';
    }
  }

  // Page load listener on iframe parent.
  window.addEventListener("load", function() {
    index = document.getElementById("index");
    index.addEventListener("load", function(e) {
      setTimeout(function() {
        init();
      }, 1000);
    });

    setTimeout(function() {
      init();
    }, 1000);
  });
</script>
</head>
<body>
  <iframe id="player" style="position:absolute; border: 0 none; bottom: 0; right: 0;" width=275 height=232></iframe>
  <iframe id="index" src="index.php" style="display:block; float:left; border: 0 none" width=100% height=100%></iframe>
</body>
</html>
