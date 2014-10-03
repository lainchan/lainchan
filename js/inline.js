;(function() {
  var cache = {}

  var inline = function(e) {
    e.preventDefault()

    var $root = $(this).closest('.post')
    var postNum = this.textContent.slice(2)

    var $clone = $root.find('#inline_' + postNum)
    if ($clone.length)
      return $clone.remove()

    var postOP = this.pathname.match(/(\d+).html/)[1]
    var selector = postNum === postOP
      ? '.op .body'
      : '#reply_' + postNum

    var node = this.className
      ? $root.find('> .intro')
      : $(this)

    var link = {
      node: node,
      postNum: postNum
    }

    var OP = $('input[name="thread"]').val()
    if (OP === postOP) {
      // XXX post hover adds fetched threads to the DOM
      selector = '#thread_' + OP + ' ' + selector
      // XXX bypass the `(OP)` text
      link.node = link.node.next()
      var $target = $(selector)
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
    $clone.attr({
      "class": 'inline post',
      id: 'inline_' + link.postNum
    })
    $clone.insertAfter(link.node)
  }

  $('head').append(
    '<style>' +
      '.inline { border: 1px dashed black; white-space: normal }' +
    '</style>')

  $('.body a, .mentioned a')
    .attr('onclick', null)// XXX disable highlightReply
    .click(inline)
})()
