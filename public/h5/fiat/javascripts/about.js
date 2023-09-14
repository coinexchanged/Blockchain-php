$(function () {
	// FastClick.attach(document.body);
	$(".relation").click(function () {
		window.location.href = 'order.html';
	})
	$(".policy").click(function () {
		window.location.href = 'policy.html';
	})

	
	let phones = getlocal_storage('phone');
	// $('.relation a').attr('href', phones);
	// $('.relation a').text(phones);
	$('input').blur(function () {
		setTimeout(function () {
			document.body.scrollTop = document.body.scrollHeight;
		}, 300);
	})
	$('select').change(function () {
		setTimeout(function () {
			document.body.scrollTop = document.body.scrollHeight;
		}, 300);
	})
})