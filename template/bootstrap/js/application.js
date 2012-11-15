!function (jQuery) {

jQuery(function(){

	jQuery('div.media > img').each(function(){
		var img = jQuery(this);
		var onclick = img.attr('onclick');
		if(!onclick){
			return;
		}
		var id = onclick.substring(onclick.indexOf(".com', '") + 8, onclick.indexOf("', this"));
		if(!id){
			return;
		}
		jQuery.get('./extend/video-screenshot.php?id=' + id, function(url){
			if(url.indexOf('http') == 0){
				img.attr('src', url);
				img.after('<img src="./extend/video-play.gif" alt="点击播放" title="点击播放" class="video-play" onclick="video_play(this)" /><img src="./extend/arrow-up.gif" alt="收起" title="收起" class="video-play hide" onclick="video_close(this)" />');
			}
		});
	});
	
	var hottags_box = jQuery('#hottags-box').data('num', 0);
	jQuery('.hottags a.btn').each(function(){
		var _this = jQuery(this);
		var plus = _this.find('i');
		if(plus.length < 1){
			plus = _this.append('<i class="icon-plus-sign"></i>').find('i');
		}
		plus.click(function(e){
			var num = hottags_box.data('num');
			if(jQuery(this).hasClass('icon-plus-sign')){
				if(num > 2){
					alert('最多三个组合标签');
					//jQuery('#globaltips').html();
				} else {
					hottags_box.val(hottags_box.val() + ' ' + _this.text()).data('num', num + 1);
					jQuery(this).removeClass('icon-plus-sign').addClass('icon-minus-sign');
				}
			} else {
				hottags_box.val(hottags_box.val().replace(' ' + _this.text(), '')).data('num', num - 1);
				jQuery(this).removeClass('icon-minus-sign').addClass('icon-plus-sign');
			}
			e.preventDefault();
		});
	});
	
	jQuery('#seccodewrap').html('<img id="img_seccode" src="do.php?ac=seccode&rand='+Math.random()+'" align="absmiddle">');
})

}(window.jQuery)

function video_play (obj){
	jQuery(obj).prev().click();
	jQuery(obj).hide().next().show();
}
function video_close (obj){
	jQuery(obj).hide().prev().show().prev().prev().click();
}