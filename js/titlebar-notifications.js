var orig_title = document.title;

$(function(){
  orig_title = document.title;
});

update_title = function() {
  var updates = 0;
  for(var i in title_collectors) {
    updates += title_collectors[i]();
  }
  document.title = (updates ? "("+updates+") " : "") + orig_title;
};

var title_collectors = [];
add_title_collector = function(f) {
  title_collectors.push(f);
};
