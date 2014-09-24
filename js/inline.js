;(function() {
  $('.body a').click(inline)

  $('head').append(
    '<style>' +
      '.inline {' +
      '  border: 1px dashed black;' +
      '  margin-left: 1em;' +
      '  padding: 1em;' +
      '}' +
    '</style>')
})()

function inline(e) {
  e.preventDefault()
  var postNum = parseInt(this.textContent.slice(2))

  var cloneID = 'inline_' + postNum
  var $clone = $('#' + cloneID)
  if ($clone.length)
    return $clone.remove()

  var OP = location.pathname.match(/(\d+).html/)[1]
  var selector = postNum === OP
    ? '.op .body'
    : '#reply_' + postNum + ' .body'

  $clone = $(selector).clone(true)
  $clone.attr({
    className: 'inline',
    id: cloneID
  })
  $clone.insertAfter(this)
}
