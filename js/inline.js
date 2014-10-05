;(function() {
  var cache = {}

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

    var url = this.pathname
    var data = cache[url]
    if (data) {
      var $target = $(data).find(selector)
      return add(link, $target)
    }

    $.get(url, function(data) {
      cache[url] = data
      var $target = $(data).find(selector)
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
