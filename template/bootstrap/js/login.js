function setBodyBG() {
	var H = $(window).height();
	var W = $(window).width();
	var imgH = W*0.4369;
	var body = $('#body');
	if(imgH + 40 < H) {
		body.css({'background-size': '100% 100%','height': imgH + 'px' });
	} else {
		body.css({'background-size': '100% '+ imgH + 'px','height': imgH + 'px' });
	}
}
function isEmailValid(e) {
	var sReg = /^(?:\w+\.?)*\w+@@(?:\w+\.?)*\w+$/;
	if(!sReg.test(e)) {
		return false;
	}
	return true;
}
function showErr(msg) {
	/*
	var error = $('#error');
	error.html(msg).slideDown(500,function(){
		setTimeout(function() { error.slideUp(); },3000);
	});*/
	$('.alert').alert();
}
function isOk(e, p) {
	if(e == '' || !isEmailValid(e)) {
		showErr('邮箱不正确');
		return false;
	}
	if(p == '' || p.length < 6){
		showErr('密码不正确');
		return false;
	}
	return true;
}
function isFormValid() {
	var e = $.trim($('#e').val()),
		p = $.trim($('#p').val());
	if(isOk(e, p)){
		$('#f').submit();
	}
}
$(window).resize(function(){
	setBodyBG();
})
$(document).ready(function(){
	setBodyBG();
	$('#body').fadeIn();

})