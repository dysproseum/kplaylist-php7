<html>
<head>
<title>| kPlaylist</title>
<style type="text/css">
  body {
    margin: 0;
  }
</style>

<?php
  // @todo get theme dynamically.
  // $theme = "html5_player";
  $theme = "webamp";
  $player_head = '';
  $player_body = '';
  if ($theme == "webamp") {
    include("kptheme/$theme/player.php");
  }
?>

<?php print $player_head; ?>

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

    // Prevent underlying iframe from intercepting drag events
    // and selecting the page during fast drags.
    var overlay = document.getElementById("iframe-overlay");
     document.addEventListener("mousedown", function() {
       index.style.pointerEvents = "none";
       index.style.userSelect = "none";
     });
     document.addEventListener("mouseup", function() {
       index.style.pointerEvents = "all";
       index.style.userSelect = "none";
     });

    setTimeout(function() {
      init();
    }, 1000);
  });
</script>
</head>
<body>

<?php print $player_body; ?>
  <?php if ($theme == "html5_player"): ?>
    <iframe id="player" style="position:absolute; border: 0 none; bottom: 0; right: 0;" width=275 height=232></iframe>
  <?php endif; ?>
  <iframe id="index" src="index.php" style="display:block; float:left; border: 0 none" width=100% height=100%></iframe>
</body>
</html>
