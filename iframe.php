<html>
<head>
<script type="text/javascript">
  playerCallbacks = [];
  indexCallbacks = [];

  // Player calls these.
  function registerPlayerChild(callback){
    console.log('Registering player callback');
    playerCallbacks.push(callback);
  }

  function getIndexCallbacks() {
    return indexCallbacks;
  }

  // Index calls these.
  function registerIndexChild(callback){
    console.log('Registering index callback');
    indexCallbacks.push(callback);
  }

  function getPlayerCallbacks() {
    return playerCallbacks;
  }

  var width, height;

  window.addEventListener("load", function() {
    var theme = document.getElementById("index").contentWindow.getTheme();
    console.log(theme);
    var player = document.getElementById("player");
    if (theme == "html5_player") {
      width="100%";
      height="54px";
      player.width=width;
      player.height=height;
    }
    player.src = "kptheme/" + theme + "/player.php";
  });

</script>
</head>
<body>
  <iframe id="player" style="position:absolute; border: 0 none; bottom: 0; right: 0;" width=275 height=232></iframe>
  <iframe id="index" src="index.php" style="display:block; float:left; border: 0 none" width=100% height=100%></iframe>
</body>
</html>
