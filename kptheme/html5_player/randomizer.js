/* kPlaylist: html5_player theme supplemental include */

// Get randomizer tracks.
function getRandomizerTracks() {
  var x = document.querySelectorAll("form[name=randomizer] #selids option");
  return x;
}

// Set up randomizer behavior.
window.addEventListener("load", function() {
  var p = document.getElementsByName("playselected");
  var q = p[0];
  if (q) {
    q.addEventListener("click", function(e) {
      e.preventDefault();

      var tracks = getRandomizerTracks();
      window.opener.playerParentFunction(tracks);

      return false;
    });
  }
});

/* kPlaylist default external.js follows */

<!--
		
	function openwin(name, url) 
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,width=675,height=320,left=150,top=270');
		if (popupWin) popupWin.focus();
	}
	
	var jwflvwin = null;

	function openJWFLV(theFile, url, height, width)
	{
		jwflvwin = open("", "jwflvwin",  'width='+width+',height='+height);
		if (!jwflvwin || jwflvwin.closed || !jwflvwin.createPlayer) 
		{
			jwflvwin = window.open(url, "jwflvwin", 'width='+width+',height='+height);
		} else jwflvwin.focus();
		
		jwflvwin.loadXMLDoc(theFile);
	}

	function newwinscroll(name, url, height, width)
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');
		if (popupWin) popupWin.focus();
	}
	
	function newwin(name, url, height, width) 
	{
		popupWin = window.open(url, name, 'resizable=yes,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');
		if (popupWin) popupWin.focus();
	}

	function flashwin(name, url, height, width)
	{
		flashpop = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');		
		if (flashpop) flashpop.focus();
	}

	function flashwinsharedplaylist(name, url, height, width)
	{
		d = document.getElementById('sel_shplaylist'); 
		if (d) url = url + "&plid=" + d.value;
		flashpop = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');		
		if (flashpop) flashpop.focus();
	}

	function flashwinuserplaylist(name, url, height, width)
	{
		d = document.getElementById('sel_playlist');
		if (d) url = url + "&plid=" + d.value;
		flashpop = window.open(url, name, 'resizable=no,scrollbars=no,status=no,toolbar=no,menubar=no,width='+width+',height='+height+',left=250,top=270');		
		if (flashpop) flashpop.focus();
	}

	function savescrolly()
	{
		var scrOfY = 0;
		if( typeof( window.pageYOffset ) == 'number' )
		{
			scrOfY = window.pageYOffset;
		} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop))
		{
			scrOfY = document.body.scrollTop;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop))
		{
			scrOfY = document.documentElement.scrollTop;
		}
		
		sy = document.getElementById('scrolly');
		if (sy) sy.value = scrOfY; 
	}

	function kptoggle() 
	{
		for(var i=0;i<document.psongs.elements.length;i++) 
		{
			if(document.psongs.elements[i].type == "checkbox")
			{
				if (document.psongs.elements[i].checked == false) document.psongs.elements[i].checked = true; 
					else
				if (document.psongs.elements[i].checked == true) document.psongs.elements[i].checked = false;
			}
		}
	}	

	function selectall() 
	{
		for(var i=0;i<document.psongs.elements.length;i++) 
			if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == false) document.psongs.elements[i].checked = true; 
	}	

	function disselectall() 
	{
		for(var i=0;i<document.psongs.elements.length;i++) 
			if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == true) document.psongs.elements[i].checked = false; 
	}	

	function anyselected()
	{
		for(var i=0;i<document.psongs.elements.length;i++) if(document.psongs.elements[i].type == "checkbox") if (document.psongs.elements[i].checked == true) return true;
		return false;
	}

	function chopener(loc, par)
	{
		var ret = "";
		var url = String(loc);
		var i = url.indexOf('?', 0);
		if (i != -1)
		{
			ret = url.substring(0, i) + par;			
		} else ret = url + par;

		window.opener.location = ret;
	}

	function chhttp(where) { 
		document.location = where;
	}
	//-->
