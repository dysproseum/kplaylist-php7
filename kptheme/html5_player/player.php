<html>
<head>
<style type="text/css">
</style>
<link href="html5_player.css" rel="stylesheet" type="text/css" />
<link href='mobile.css' rel='stylesheet' media='only screen and (max-width: 768px)' type='text/css' />
<script type="text/javascript">
  // Player callback.
  function playerFrame(track) {
    player.hidden = false;
    player.src=track;
    player.play();
  }

  // Register callback.
  window.onload = function(){
    if (parent && parent.registerPlayerChild){
      console.log('Registering with parent (player)');
      parent.registerPlayerChild(playerFrame);
    }
  };
</script>
</head>
<body>

<?php
// HTML5 player type.
$player_type = "audio";
// $player_type = "video";

echo '<div id="html5container">';
echo '<' . $player_type . ' id="html5player" controls></video>';
echo '</div>';
?>

<script type="text/javascript">
  let player;
  let childCallbacks = [];

  window.addEventListener("load", function() {
    childCallbacks = parent.getIndexCallbacks();
    player = document.getElementById("html5player");

    player.addEventListener("ended", function() {
      console.log("ended");
      var nextSong;
      for (var i=0; i < childCallbacks.length; i++){
        var callback = childCallbacks[i];
        var currentSong = callback();
        var nextSong = callback(currentSong);
      }
      player.pause();
      player.src = nextSong;
      player.play();
    });

    player.addEventListener("playing", function() {
      console.log("playing");
    });
  });
</script>
</body>
</html>
