;(function() {
  var inline = function(e) {
    e.preventDefault()

    var postNum = this.textContent.slice(2)

    var $clone = $('#inline_' + postNum)
    if ($clone.length)
      return $clone.remove()

    var OP = location.pathname.match(/(\d+).html/)[1]
    var selector = postNum === OP
      ? '.op .body'
      : '#reply_' + postNum + ' .body'

    var link = {
      postNum: postNum,
      node: this
    }
    var $target = $(selector)
    if ($target.length)
      add(link, $target)
    else
      $.get(this.pathname, function(data) {
        var $target = $(data).find(selector)
        add(link, $target)
      })
  }

  var add = function(link, $target) {
    var $clone = $target.clone(true)
    $clone.attr({
      "class": 'inline',
      id: 'inline_' + link.postNum
    })
    $clone.insertAfter(link.node)
  }

  $('head').append(
    '<style>' +
      '.inline {' +
      '  border: 1px dashed black;' +
      '  margin-left: 1em;' +
      '  padding: 1em;' +
      '}' +
    '</style>')

  $('.body a').click(inline)

})()
