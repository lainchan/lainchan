if (active_page == 'index' || active_page == 'thread')
$(function(){

  var gallery_view = false;

  $('hr:first').before('<div id="gallery-view" style="text-align:right"><a class="unimportant" href="javascript:void(0)">-</a></div>');
  $('#gallery-view a').html(gallery_view ? _("Disable gallery mode") : _("Enable gallery mode")).click(function() {
    gallery_view = !gallery_view;
    $(this).html(gallery_view ? _("Disable gallery mode") : _("Enable gallery mode"));
    toggle_gview(document);
  });

  var toggle_gview = function(elem) {
    if (gallery_view) {
      $(elem).find('img.post-image').parent().each(function() { 
        this.oldonclick = this.onclick;
        this.onclick = handle_click;
        $(this).attr('data-galid', Math.random());
      });
    }
    else {
      $(elem).find('img.post-image').parent().each(function() {
        if (this.onclick == handle_click) this.onclick = this.oldonclick;
      });
    }
  };

  $(document).on('new_post', toggle_gview);

  var gallery_opened = false;

  var handle_click = function(e) {
    e.stopPropagation();
    e.preventDefault();

    if (!gallery_opened) open_gallery();

    gallery_setimage($(this).attr('data-galid'));
  };

  var handler, images, active, toolbar;

  var open_gallery = function() {
    $('body').css('overflow', 'hidden');

    gallery_opened = true;

    handler = $("<div id='alert_handler'></div>").hide().appendTo('body').css('text-align', 'left');

    $("<div id='alert_background'></div>").click(close_gallery).appendTo(handler);

    images = $("<div id='gallery_images'></div>").appendTo(handler);
    toolbar = $("<div id='gallery_toolbar'></div>").appendTo(handler);
    active = $("<div id='gallery_main'></div>").appendTo(handler);

    active.on('click', function() {
      close_gallery();
    });

    $('img.post-image').parent().each(function() {
      var thumb = $(this).find('img').attr('src');

      var i = $('<img>').appendTo(images);
      i.attr('src', thumb);
      i.attr('data-galid-th', $(this).attr('data-galid'));

      i.on('click', function(e) {
        gallery_setimage($(this).attr('data-galid-th'));
      });
    });

    $("<a href='javascript:void(0)'><i class='fa fa-times'></i></div>")
    .click(close_gallery).appendTo(toolbar);

    handler.fadeIn(400);
  };

  var gallery_setimage = function(a) {
    if (a == +1 || a == -1) {
      var meth = (a == -1) ? 'prev' : 'next';
      a = $('#gallery_images img.active')[meth]().attr('data-galid-th');
      if (!a) return;
    }

    $('#gallery_images img.active').removeClass('active');

    var thumb = $('#gallery_images img[data-galid-th="'+a+'"]');
    var elem = $('a[data-galid="'+a+'"]');

    thumb.addClass('active');

    var topscroll = thumb.position().top + images.scrollTop();
    topscroll -= images.height() / 2;
    topscroll += thumb.height() / 2;
    images.animate({'scrollTop': topscroll}, 300);

    var img = elem.attr('href');

    active.find('img').fadeOut(200, function() { $(this).remove(); });

    var i = $('<img>');
    i.attr('src', img);
    i.appendTo(active);
    i.hide(); 

    i.on('load', function() {
      i.css('left', 'calc(50% - '+i.width()+'px / 2)');
      i.css('top', 'calc(50% - '+i.height()+'px / 2)');
      i.fadeIn(200);
    }).on('click', function(e) {
      e.stopPropagation();
      gallery_setimage(+1);
    });
  };

  var close_gallery = function() {
    $('body').css('overflow', 'auto');

    gallery_opened = false;

    handler.fadeOut(400, function() { handler.remove(); });
  };

});
