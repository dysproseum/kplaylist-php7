<html>
<head>
<script type="text/javascript">
childCallbacks = [];

// Player calls this.
function registerChild(callback){
  console.log('Registering child callback');
  childCallbacks.push(callback);
}

// Index calls this.
function getCallbacks() {
  return childCallbacks;
}

</script>
</head>
<body>
<iframe id="player" src="player.php" style="position:absolute; border: 0 none; bottom: 0; right: 0;" width=275 height=232></iframe>
<iframe id="index" src="index.php" style="display:block; float:left; border: 0 none" width=100% height=100%></iframe>
</html>
