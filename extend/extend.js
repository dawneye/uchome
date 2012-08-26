var JQ = jQuery.noConflict();
JQ(document).ready(function () {
	JQ('div.media > img').each(function(){
		var img = JQ(this);
		var onclick = img.attr('onclick');
		if(!onclick){
			return;
		}
		var id = onclick.substring(onclick.indexOf(".com', '") + 8, onclick.indexOf("', this"));
		if(!id){
			return;
		}
		JQ.get('./extend/video-screenshot.php?id=' + id, function(url){
			if(url.indexOf('http') == 0){
				img.attr('src', url);
			}
		});
	});
});