/**
 * @file
 * Attach behaviors for kPlaylist wrapper player.
 */
(function ($) {
  'use strict';
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
            // Remove unnecessary elements.
            $('#results').html(data);

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

            // Set up playback click handler.
            var $links = $('#results a[onclick]');
            $links.attr('onclick', null).addClass('playable');
            $links.click(function() {
              $('.now-playing').removeClass('now-playing');
              $(this).addClass('now-playing');
              var arVideos = document.getElementsByTagName("audio");
              var video = arVideos[0];
              video.setAttribute("controls", "");
              video.setAttribute("preload", "auto");
              if (video.paused == false) {
                video.pause();
              }
              video.src=this.href; video.play();
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

    // Auto-search text box.
    $('#searchtext').keyup(function() {
      $form.submit();
    });

  });

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
      var arVideos = document.getElementsByTagName("audio");
      var video = arVideos[0];
      if (video.paused == false) {
        video.pause();
      }
      else {
        video.play();
      }
      return false;
    }
  });

})(jQuery);

