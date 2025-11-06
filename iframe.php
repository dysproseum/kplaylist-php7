<html>
<head>
<title>kPlaylist</title>
<style type="text/css">
  body, input, button {
    font-family: Arial, monospace, sans-serif;
    font-size: 14px;
  }
  body {
    margin: 0;
    background: teal;
    box-sizing: border-box;
    user-select: none;
    float: left;
    width: 100%;
    height: 100%;
    overflow: hidden;
  }
  .wait, .wait div {
    cursor: wait;
  }
  .browser {
    position: absolute;
    margin: 20px;
    width: 75%;
    height: 75%;
    border: 4px outset;
    overflow: hidden;
    padding-bottom: 64px;
    resize: both;
  }
  .browser .titlebar {
    background: blue;
    color: white;
    display: block;
    float: left;
    width: 100%;
    height: 24px;
    line-height: 40px;
  }
  .browser .titlebar h1 {
    float: left;
    margin: 0px 0px 0px 6px;
    font-size: 14px;
    line-height: 24px;
    cursor: default;
  }
  .browser .titlebar a {
    float: right;
    margin-right: 5px;
    margin-top: 5px;
  }
  .browser .titlebar .close {
    background: url(images/x-icon.png);
    width: 15px;
    height: 14px;
  }
  .browser .titlebar .close:active {
    transform: rotateZ(180deg);
   }
  .browser .addressbar {
    background: lightgray;
    display: block;
    float: left;
    width: 100%;
    height: 40px;
    line-height: 40px;
    user-select: none;
  }
  .browser .addressbar button {
    height: 32px;
    float: left;
    margin-top: 4px;
    margin-right: 4px;
    font-size: 11px;
    padding: 2px;
  }
  .browser .addressbar label {
    display: block;
    float: left;
    margin-top: 8px;
    line-height: 22px;
    margin-left: 10px;
    margin-right: 0px;
  }
  .browser .addressbar input {
    display: block;
    float: left;
    margin-top: 8px;
    margin-left: 10px;
    margin-right: 0px;
  }
  .browser .addressbar input[type=text]:focus-visible {
    outline-offset: 10px;
    outline: unset;
  }
  .browser .addressbar .animation {
    float: right;
    margin-right: 4px;
    margin-top: 4px;
  }
  .browser iframe {
    display: block;
    float: left;
    width: 100%;
    height: 100%;
    border: 0;
    background: white;
  }
  #selection {
    border: 1px dotted lightgray;
    position: absolute;
  }
  .icon {
    width: 92px;
    text-align: center;
    margin-top: 17px;
  }
  .icon .caption {
    font-family: "MS Sans Serif", Segoe UI, sans-serif;
    margin-top: 6px;
    font-size: 11px;
    color: white;
    -webkit-font-smoothing: none;
    border: 1px dotted transparent;
    cursor: default;
  }
  .highlight .caption {
    background: navy;
    border-color: white;
  }
</style>

<?php
  // @todo get theme dynamically.
  // can get theme in js from index iframe
  // and can load css with js
  // uninstall or refresh if switching themes
  $theme = isset($_GET['theme']) ? $_GET['theme'] : 'webamp';
  $player_head = '';
  $player_body = '';
  include("kptheme/$theme/player.php");
?>

<?php print $player_head; ?>

<script type="text/javascript" src="include/drag.js"></script>
<script type="text/javascript">
  // window placement and z-index
  const windowManager =  {
    windows: [],
    z: 1000,
    testFunction: () => {
      console.log("test");
    },
    openIframeWindow(type, id, url) {
      //var win = document.querySelectorAll("div[class=browser]");
      if (!this.isWindowOpen(id)) {
        var div = this.initIframeDiv(url);
        this.initIframeBrowser(div);
        this.windows.push(div);
        body.append(div);
      }
      this.makeWindowActive(id);
    },
    initIframeDiv(url) {
      // Make new browser iframe.
      var div = document.querySelector('.default').cloneNode(true);
      var iframe = div.querySelector('iframe');
      iframe.src = url;
      div.hidden = false;
      div.classList.remove('default');
  
      // Set window location & size.
      div.style.left = 230;
      div.style.top = 100;
      div.style.width = 768;
      div.style.height = 480;

      // Set window layer.
      let tmpZ = 0;
      for (browser of this.windows) {
console.log(this.z);
        if (browser.style.zIndex > tmpZ) {
          tmpZ = browser.style.zIndex;
        }
      }
      tmpZ++;
      div.style.zIndex = tmpZ;
  
      return div;
    },
    initIframeBrowser(browser) {
      let index = browser.querySelector("iframe");
      let titlebar;
      let close;
      let back;
      let forward;
      let reload;
      let home;
      let address;
      let animation;
      let form;
      titlebar = browser.querySelector(".browser .titlebar h1");
      close = browser.querySelector(".titlebar a.close");
      back = browser.querySelector(".back");
      forward = browser.querySelector(".forward");
      reload = browser.querySelector(".reload");
      home = browser.querySelector(".home");
      address = browser.querySelector(".address");
      form = browser.querySelector(".navigate");
      animation = browser.querySelector(".animation");
  
      // Update address bar if same-origin.
      index.addEventListener("load", function(e) {
        console.log(e);
        animation.src = "images/netscape.jpg";
        if (index.contentWindow && index.contentWindow.location) {
          address.value = index.contentWindow.location.href.replace(proxyUrl, '');
        }
        else {
          address.value = index.src.replace(proxyUrl, '');
        }
  
        if (index.contentDocument && index.contentDocument.title) {
          titlebar.innerHTML = index.contentDocument.title + " - " + title;
        }
  
        setTimeout(function() {
          init(index);
          animation.src = "images/netscape.jpg";
        }, 2000);
      });
  
      // Set address bar.
      address.value = index.src;
  
      // Prevent underlying iframe from intercepting drag events
      // and selecting the page during fast drags.
      document.addEventListener("mousedown", function() {
        index.style.pointerEvents = "none";
        index.style.userSelect = "none";
      });
      document.addEventListener("mouseup", function() {
        index.style.pointerEvents = "all";
        index.style.userSelect = "none";
      });
  
      close.addEventListener("click", function() {
        this.parentElement.parentElement.remove();
      });
  
      // Browser buttons.
      const goNavigate = function(e) {
        e.preventDefault();
        animation.src = "images/netscape.gif";
        if (address.value.startsWith(window.location.origin)) {
          index.src = address.value;
        }
        else {
          // @todo add option to use proxy.
          // index.src = proxyUrl + address.value;
          index.src = address.value;
        }
        return false;
      };
      form.addEventListener("submit", goNavigate);
      back.addEventListener("click", function(e) {
        // try and prevent reloading page when navigating all the way back.
        e.preventDefault();
        animation.src = "images/netscape.gif";
        index.contentWindow.history.go(-1);
        return false;
      });
      forward.addEventListener("click", function() {
        animation.src = "images/netscape.gif";
        index.contentWindow.history.go(1);
      });
      reload.addEventListener("click", function() {
        animation.src = "images/netscape.gif";
        // May not have cross-origin permission to
        // index.contentWindow.location.reload();
        index.src = index.src;
      });
      home.addEventListener("click", function() {
        animation.src = "images/netscape.gif";
        index.src = baseUrl;
      });
  
      setTimeout(function() {
        init(index);
      }, 1000);
  
      dragElement(browser);
  
    },
    isWindowOpen(id) {
      //type
      //id
    },
    makeWindowActive(id) {
      // windows[id].zIndex = 1000;
    }
  };

  let theme = '';
  let body;
  let playerCallbacks = [];
  let indexCallbacks = [];

  // Browser variables.
  let title = 'Dysproseum Navigator';
  let baseUrl = 'index.php';
  let proxyUrl = window.location.href;
  proxyUrl = proxyUrl.substr(0, proxyUrl.indexOf('iframe.php'));
  proxyUrl += 'proxy.php?url=';

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

  // Conversations calls these.
  function openIframeWindow(type, id, url) {
    // var div = initIframeDiv(url);
    // initIframeBrowser(div);
    // body.append(div);
    windowManager.openIframeWindow(type, id, url);
  }

  /*
   * Init theme and player iframe.
   * Setup player dynamically based on theme.
   *
   * Login:
   * - First page load iframe.php
   * - The theme js is not loaded on index frame login page.
   * - And contentWindow.getTheme is not callable.
   * - The index frame refreshes and player is loaded.
   *
   * Logout:
   * - Can no longer call getTheme.
   * - Unset theme and unset player.
   */
  function init(index) {
    // Unable to get current user theme.
    if (!index.contentWindow.getTheme) {
      console.log("Unable to get theme setting");
    }

    // Detect logout.
    if (theme != '' && !index.contentWindow.getTheme) {
      console.log("Logout detected");
      theme = '';
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

    // Set iframe sizes.
    if (theme == "html5_player") {
      // player.width = '100%';
      // player.height = '54px';
    }
  }

  // Page load listener on iframe parent.
  window.addEventListener("load", function() {
    body = document.querySelector("body");

    // Allow for multiple browsers.
    browsers = document.querySelectorAll(".browser");
    for (const browser of browsers) {
      windowManager.initIframeBrowser(browser);
    }

    // Icon.
    let icons = document.querySelectorAll(".icon");
    for (const icon of icons) {
      icon.addEventListener("click", function(e) {
        for (const icon2 of icons) {
          icon2.classList.remove("highlight");
        }
        this.classList.add("highlight");
      });
      icon.addEventListener("dblclick", function(e) {

        // Busy pointer icon.
        body.classList.add("wait");
        setTimeout(function() {
          body.classList.remove("wait");
        }, 3000);

        if (this.dataset.type == "webamp") {
          webAmp.reopen();
          return;
        }

        // Make new browser iframe.
        let type;
        let id;
        let url;
        if (this.dataset.type) {
          console.log(this.dataset.type);
          type = this.dataset.type;
        }
        if (this.dataset.url) {
          console.log(this.dataset.url);
          url = this.dataset.url;
        }
        windowManager.openIframeWindow(type, id, url);
      });
    }

    // Desktop selection rectangle.
    // https://stackoverflow.com/questions/23284429/select-area-rectangle-in-javascript
    var div = document.getElementById('selection'), x1 = 0, y1 = 0, x2 = 0, y2 = 0;
    function rectanglesOverlap(rect1, rect2) {
      return !(
        rect1.x > rect2.x + rect2.width ||
        rect2.x > rect1.x + rect1.width ||
        rect1.y > rect2.y + rect2.height ||
        rect2.y > rect1.y + rect1.height
      );
    }
    function reCalc() { //This will restyle the div
      if (div.hidden == true) {
        return;
      }

      var x3 = Math.min(x1,x2); //Smaller X
      var x4 = Math.max(x1,x2); //Larger X
      var y3 = Math.min(y1,y2); //Smaller Y
      var y4 = Math.max(y1,y2); //Larger Y
      div.style.left = x3 + 'px';
      div.style.top = y3 + 'px';
      div.style.width = x4 - x3 + 'px';
      div.style.height = y4 - y3 + 'px';

      // determine if icons were selected
      var select = {};
      select.x = x3;
      select.y = y3;
      select.width = x4 - x3;
      select.height = y4 - y3;
      for (const icon of icons) {
        icon.classList.remove("highlight");
        var rect = icon.getBoundingClientRect();
        if (rectanglesOverlap(select, rect)) {
          icon.classList.add("highlight");
        }
      }

    }
    onmousedown = function(e) {
        if (e.button == 2) {
          return;
        }
        if (e.target.tagName != "BODY") {
          return;
        }
        for (const icon of icons) {
          icon.draggable = false;
          icon.classList.remove("highlight");
        }

        div.hidden = 0; //Unhide the div
        x1 = e.clientX; //Set the initial X
        y1 = e.clientY; //Set the initial Y
        reCalc();
    };
    onmousemove = function(e) {
        x2 = e.clientX; //Update the current position X
        y2 = e.clientY; //Update the current position Y
        reCalc();
    };
    onmouseup = function(e) {
        div.hidden = 1; //Hide the div
    };
  });
</script>
</head>
<body>

<?php print $player_body; ?>

<div id="selection" hidden></div>

<div class="icon">
  <img src="images/mycomputer.png" width=32" />
  <div class="caption">My Computer</div>
</div>
<div class="icon" data-type="browser" data-url="index.php">
  <img src="images/netscape.jpg" width=32" />
  <div class="caption">Dysproseum Navigator</div>
</div>
<div class="icon" data-type="browser" data-url="/oscillator">
  <img src="images/keyboard_musical_midi.png" width=32" />
  <div class="caption">Oscillator</div>
</div>
<div class="icon" data-type="conversations_buddylist" data-url="/conversations/iframe/buddylist.php">
  <img src="images/aol_messenger.png" width=32" />
  <div class="caption">Conversations</div>
</div>
<div class="icon" data-type="webamp">
  <img src="images/webamp.png" width=32" />
  <div class="caption">Webamp</div>
</div>
<div class="icon" data-type="browser" data-url="https://jspaint.app/#local:c6c35db1cd4b28">
  <img src="images/paintbrush.png" width=32" />
  <div class="caption">JSPaint.app</div>
</div>
<div class="icon" data-type="browser" data-url="https://mrdoob.com/lab/javascript/effects/solitaire/">
  <img src="images/solitaire.png" width=32" />
  <div class="caption">Solitaire</div>
</div>

<?php $browsers = [
  // Named URL(s) to open on startup (optional).
  // "vplaylist" => "/vplaylist",
  "kplaylist" => "index.php",
  // Default URL for new windows (required).
  "default" => "index.php",
]; ?>

<?php foreach ($browsers as $index => $url): ?>
  <div class="browser <?php print $index; ?>" <?php if ($index == "default") print "hidden"; ?>>
    <div class="titlebar">
      <h1>Dysproseum Navigator</h1>
    <a href="#" class="close"></a>
    </div>
    <div class="addressbar">
      <form class="navigate">
        <button class="back" type="button">Back<br />&larrhk;</button>
        <button class="reload" type="button">Reload<br />&orarr;</button>
        <button class="forward" type="button">Forward<br />&rarrhk;</button>
        <button class="home" type="button">Home<br />&#8962;</button>
        <label>Location</label>
        <input class="address" type="text" size="75" spellcheck="false" autocomplete="off" />
        <img class="animation" src="images/netscape.gif" width="32" />
      </form>
    </div>
    <iframe src="<?php print $url; ?>"></iframe>
  </div>
<?php endforeach; ?>

</body>
</html>
