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
    margin-right: 4px;
    margin-top: 4px;
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
    cursor: default;
    user-select: none;
  }
  .highlight .caption {
    background: navy;
    border: 1px dotted transparent;
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
  let theme = '';
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

  const initIframeBrowser = function(browser) {
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
      animation.src = "images/netscape.gif";
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
      browser.style.zIndex = 0;
    });
    document.addEventListener("mouseup", function() {
      index.style.pointerEvents = "all";
      index.style.userSelect = "none";
    });

    // Attempt to move window to foreground.
    browser.addEventListener("click", function() {
      this.style.zIndex = 1000;
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
        index.src = proxyUrl + address.value;
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
      index.contentWindow.location.reload();
    });
    home.addEventListener("click", function() {
      animation.src = "images/netscape.gif";
      index.src = baseUrl;
    });

    setTimeout(function() {
      init(index);
    }, 1000);

    dragElement(browser);

  };

  // Page load listener on iframe parent.
  window.addEventListener("load", function() {
    // Allow for multiple browsers.
    browsers = document.querySelectorAll(".browser");
    for (const browser of browsers) {
      initIframeBrowser(browser);
    }

    // Icon.
    let body = document.querySelector("body");
    let icons = document.querySelectorAll(".icon");
    for (const icon of icons) {
      icon.addEventListener("click", function(e) {
        this.classList.add("highlight");
      });
      icon.addEventListener("dblclick", function(e) {
console.log("double click");
        // @todo make new browser iframe.
        var div = document.querySelector('.default').cloneNode(true);
        div.hidden = false;
        div.classList.remove('default');
        initIframeBrowser(div);
        body.append(div);
      });
    }

    // Desktop (body).
    body.addEventListener("click", function(e) {
      if (e.target.tagName != "SPAN" && e.target.tagName != "IMG") {
        for (const icon of icons) {
          icon.classList.remove("highlight");
        }
      }
    });
  });
</script>
</head>
<body>

<?php print $player_body; ?>

<div class="icon">
  <img src="images/netscape.jpg" width=32" />
  <span class="caption">Dysproseum Navigator</span>
</div>

<?php $browsers = [
  "kplaylist" => "index.php",
  "default" => "/vplaylist",
]; ?>

<?php foreach ($browsers as $index => $url): ?>
  <div class="browser <?php print $index; ?>" <?php if ($index == "default") print "hidden"; ?>>
    <div class="titlebar">
      <h1>Dysproseum Navigator</h1>
    <a href="#" class="close"><img src="images/x-icon.png" /></a>
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
