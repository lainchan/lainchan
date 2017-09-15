  $(document).on("ready", function() {
	function cycle() {
    setTimeout(function() {
	createFirework(100,200,8,7,null,null,null,null,true,true);
        cycle();
    }, 1000 + Math.floor(Math.random() * 8000));
    }
cycle();
  }
);

