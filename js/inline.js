;(function() {
  var App = {
    cache: {},
    get: function(url, cb) {
      var $page = App.cache[url]
      if ($page)
        return cb($page)

      $.get(url, function(data) {
        var $page = $(data)
        App.cache[url] = $page
        cb($page)
      })
    },
    options: {
      add: function(key, description, tab) {
        tab || (tab = 'general')

        var checked = App.options.get(key)
        var $el = $(
          '<div>' +
            '<label>' +
              '<input type="checkbox">' +
              description +
            '</label>' +
          '</div>')

        $el
          .find('input')
          .prop('checked', checked)
          .on('change', App.options.check(key))

        window.Options.extend_tab(tab, $el)
      },
      get: function(key) {
        if (localStorage[key])
          return JSON.parse(localStorage[key])
      },
      check: function(key) {
        return function(e) {
          var val = this.checked
          localStorage[key] = JSON.stringify(val)
        }
      }
    }
  }

  var inline = function(e) {
    e.preventDefault()

    var $root = $(this).closest('.post')
    var targetNum = this.textContent.slice(2)

    var $clone = $root.find('#inline_' + targetNum)
    if ($clone.length)
      return $clone.remove()

    var srcOP = $root.closest('[id^=thread]').attr('id').match(/\d+/)[0]

    var node, targetOP
    if (this.className) {// backlink
      node = $root.find('> .intro')
      targetOP = srcOP
    } else {
      node = $(this)
      targetOP = this.pathname.match(/(\d+).html/)[1]
    }

    var link = {
      node: node,
      targetNum: targetNum
    }

    var selector = targetNum === targetOP
      ? '#op_' + srcOP
      : '#reply_' + targetNum

    if (srcOP === targetOP) {
      if (targetNum === targetOP)
        link.node = link.node.next()// bypass `(OP)`

      var $target = $(selector)
      if ($target.length)
        return add(link, $target)
    }

    App.get(this.pathname, function($page) {
      var $target = $page.find(selector)
      add(link, $target)
    })
  }

  var add = function(link, $target) {
    var $clone = $target.clone(true)
    $clone.find('.inline').remove()
    $clone.attr({
      "class": 'inline post',
      id: 'inline_' + link.targetNum,
      style: null// XXX remove post hover styling
    })
    $clone.insertAfter(link.node)
  }

  App.options.add('inline', 'Inline quoted posts')

  if (!App.options.get('inline'))
    return

  $('head').append(
    '<style>' +
      '.inline {' +
        'border: 1px dashed black;' +
        'white-space: normal;' +
        'overflow: auto;' + // clearfix
      '}' +
    '</style>')

  // don't attach to outbound links
  $('.body a:not([rel]), .mentioned a')
    .attr('onclick', null)// XXX disable highlightReply
    .click(inline)
})()
