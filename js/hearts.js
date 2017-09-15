/*
    Script: Floating Hearts using Canvas and native JS
    Author: Abhinav Rastogi
    Date: Feb 14, 2013

    Sample Usage:

    floatingLove({
            'minSpeed': 1.5,        //Minimum vertical speed
            'maxSpeed': 2,          //Maximum vertical speed
            'minAmplitude': 0.5,    //Minimum amplitude (>0)
            'maxAmplitude': 1.5,    //Maximum amplitude (>0)
            'minFrequency': 0.08,    //Maximum Frequency (>0)
            'maxFrequency': 0.1,    //Maximum Frequency (>0)
            'minAlpha': 0.7,        //Minimum opacity (0-1)
            'maxAlpha': 0.8,        //Maximum opacity (0-1)
            'minScale': 0.2,        //Minimum size multiplier (0-1)
            'maxScale': 0.8,        //Maximum size multiplier (0-1)
            'interval': 1000,       //Time gap between each heart
            'delay': 1000           //Starting delay from initialization
        }).init();
*/

var floatingLove = function(settings) {
	var canvas, context;	
	var res_heart, body;
    var pool = new Array();
	var arr_hearts = new Array();

	window.requestAnimFrame = (function(callback) {
        return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame ||
        function(callback) {
          window.setTimeout(callback, 1000/60);
        };
    })();

    var generateRandomHeart = function() {
        var obj_heart;
        if(pool.length>0) {
            obj_heart=pool.pop();
        } else {
            obj_heart={};
            obj_heart.image=res_heart;
        }

        obj_heart.x = (window.innerWidth-100)*Math.random();
        obj_heart.y = window.innerHeight;
        obj_heart.speed = settings.minSpeed + (settings.maxSpeed-settings.minSpeed)*Math.random();
        obj_heart.amplitude = settings.minAmplitude + (settings.maxAmplitude - settings.minAmplitude) * Math.random();
        obj_heart.frequency = settings.minFrequency + (settings.maxFrequency - settings.minFrequency) * Math.random();
        obj_heart.alpha = settings.minAlpha + (settings.maxAlpha - settings.minAlpha)*Math.random();
        obj_heart.size = (res_heart.height * settings.minScale) + (res_heart.height*(settings.maxScale - settings.minScale)) * Math.random();
        obj_heart.width = res_heart.width;
        obj_heart.height = res_heart.height;

        arr_hearts.push(obj_heart);
        setTimeout(generateRandomHeart, settings.interval);
    };

    var animate = function() {
    	requestAnimFrame(function() {
          animate();
        });
        context.clearRect(0,0,canvas.width,canvas.height);
        var heart;
        for(var i=0; i<arr_hearts.length; i++) {
        	heart = arr_hearts[i];
        	context.globalAlpha = heart.alpha;
        	context.drawImage(heart.image, heart.x, heart.y, heart.size, heart.size * heart.height/heart.width);	
	    	heart.y-=heart.speed;
	    	heart.x = heart.x + heart.amplitude*Math.sin(heart.y*heart.frequency);	
    	}
    	for(var i=0; i<arr_hearts.length; i++) {
    		if(arr_hearts[i].y<-100) {
    			pool.push(arr_hearts.splice(i,1)[0]);
    			break;
    		}
    	}	
    };	

	var init = function() {
		canvas = document.createElement('canvas');
		canvas.height = window.innerHeight || html.clientHeight;
        canvas.width = window.innerWidth || html.clientWidth;
        var canvasStyle = canvas.style;
        canvasStyle.position = 'fixed';
        canvasStyle.top = 0;
        canvasStyle.left = 0;
        canvasStyle.zIndex = 1138;
        canvasStyle['pointerEvents'] = 'none';
        body = document.getElementsByTagName('body')[0];
        body.appendChild(canvas);

        res_heart = new Image();
        res_heart.onload = function() {
        	animate();	
        };	
        res_heart.src="/static/3rdbdayballoon2_75.png";

        setTimeout(generateRandomHeart, settings.delay);
        context = canvas.getContext('2d');
	};

	return ({
		'init': init
	});
};
