/**
 * @file
 * Attach behaviors for kPlaylist wrapper player.
 */
var audio;
var current;

$(document).ready(function() {

  var $form = $('form#searchform');
  $form.submit(function() {
    var options = {
      beforeSubmit:  function() {
        $('#results').fadeOut('fast', function() {
          $('#results').html('<img src="loading.gif" />');
        });
      },
      success: function(data) {
        $('#results').fadeOut('fast', function() {
          $('#results').html(data);

          // Remove unnecessary elements.
          $('#results title').remove();
          $('#results link').remove();
          $('#results meta').remove();
          $('#results script').remove();
          $('#results .finfo').remove();
          $('#results a span.file').remove();
          $('#results > table:first').remove();
          $('#results > table:last').remove();
          $('#results > table:last').remove();
          $('#results #html5container').remove();
          $('#results table tbody tr td:first').remove();
          $('#results input[type=checkbox]').remove();
          $('#results').fadeIn('fast');
          $('#results td.bbox').parent().parent().parent().parent()
            .parent().parent().parent().parent().parent().parent().parent().hide();

          // Set up playback click handlers.
          var $links = $('#results a[onclick]');
          $links.attr('onclick', null).addClass('playable');
          $links.click(function() {
            // Set data attributes for marking now-playing.
            $links.attr('data-index', null);
            $(this).attr('data-index', 0);

            var audios = [];
            audios.push(new Audio(this.href));
            var $next = $(this).parent().parent().next("tr").find("a.playable");
            while ($next.length !== 0) {
              $next.attr('data-index', audios.length);
              audios.push(new Audio($next.attr('href')));
              $next = $next.parent().parent().next("tr").find("a.playable");
            }
            play_sound_queue(audios);

            return false;
          });

          // Add padding to links.
          $links.each(function(i, obj) {
            var $row = $(obj).parent();
            $row.addClass('link-row');
            $row.children().hide();
            var title = $row.find('.playable span').text();
            $row.find('.playable').html(title).show();
          })
        })
      }
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // Auto-search text box with delay.
  var timeout;
  $('#searchtext').keyup(function() {
    clearTimeout(timeout);
    timeout = setTimeout(function() {
      $form.submit();
    }, 500);
  });

  $('#player-pause').click(function() {
    if (audio.paused) {
      audio.play();
    }
    else {
      audio.pause();
    }
    return false;
  });

});
// End document.ready

// Auto-search text box.
$('#searchtext').keyup(function() {
  $form.submit();
});

// Keyboard functionality.
$(document).keypress(function(event) {
  if ($(event.target).is('input')) {
    return;
  }
  if (event.which == 47) {
    // Slash key focuses search box.
    $('#searchtext').focus().select();
    return false;
  }
  else if (event.which == 32) {
    // Spacebar toggles playback.
    if (audio.paused == false) {
      audio.pause();
    }
    else {
      audio.play();
    }
    return false;
  }
});

/*****************************
         AUDIO
*****************************/

function str_pad_left(string,pad,length) {
  return (new Array(length+1).join(pad)+string).slice(-length);
}

function play(audio_item, callback) {
  console.log('play() ' + audio_item.src);
  audio = audio_item;

  var seekbar = document.getElementById('seekbar');
  var volume = document.getElementById('volume');
  audio.volume = volume.value;
  audio.play();

  // Time update
  audio.addEventListener("timeupdate", function() {
    var currentTime = Math.floor(audio.currentTime);
    var minutes = Math.floor(currentTime / 60);
    var seconds = currentTime - minutes * 60;
    currentTime = minutes+':'+str_pad_left(seconds,'0',2);
    var duration = Math.floor(audio.duration);
    var minutes = Math.floor(duration / 60);
    var seconds = duration - minutes * 60;
    duration = minutes+':'+str_pad_left(seconds,'0',2);

    $('#currentTime').text(currentTime);
    $('#duration').text(duration);
    seekbar.value = audio.currentTime;
    seekbar.min = 0;
    seekbar.max = audio.duration;
  });

  // Load status.
  $('#status').text('Loading...');
  $('#status').attr('style', 'color:black');
  audio.addEventListener("canplaythrough", function() {
    $('#status').text('canplaythrough');
    $('#status').attr('style', 'color:green');
  });
  audio.addEventListener("error", function() {
    console.log(audio.error);
  });

  seekbar.onchange = function() {
    audio.currentTime = seekbar.value;
  };

  volume.onchange = function() {
    audio.volume = volume.value;
  };

  if (callback) {
    //When the audio object completes it's playback, call the callback
    //provided
    audio.addEventListener('ended', callback);
  }
}

function play_sound_queue(sounds) {
  if (audio && audio.paused == false) {
    audio.pause();
  }
  var index = 0;
  function recursive_play() {
    $('.now-playing').removeClass('now-playing');
    var $now = $('a.playable[data-index=' + index + ']');
    $now.addClass('now-playing');
    $('#title').text($now.text());
    //If the index is the last of the table, play the sound
    //without running a callback after
    if (index + 1 === sounds.length) {
      play(sounds[index], null);
    } else {
      //Else, play the sound, and when the playing is complete
      //increment index by one and play the sound in the
      //indexth position of the array
      play(sounds[index], function() {
        index++;
        recursive_play();
        current = index;
      });
    }
  }
  recursive_play();
}

