$(function () {
	// FastClick.attach(document.body);
	// 请求页面数据

	function getTypeTitle(type)
	{
		switch (type)
		{
			case '秒合约划转闪兑':
				return "Second contract transfer flash transfer";
				break;
			case '合约划转秒合约':
				return "Transfer contract to second contract";
				break;
			case '秒合约划转合约':
				return "Second contract transfer contract";
				break;
			case 'c2c划转合约':
				return "C2C transfer contract";
				break;
			case 14:
				return "";
				break;
			case 15:
				return "";//c2c划转合约
				break;
			case 16:
				return "";//合约划转秒合约
				break;
		}
	}

	initDataTokens({
		url: 'wallet/hzhistory'
	}, function (res) {
		if (res.type == 'ok') {
			var list = res.message.data;
			var html = '';
			if (list.length > 0) {
				$('.nodata').hide();
				for (let i in list) {
					html += '<li class="flex">';
					// html += '<p>' + list[i].info + '</p>';
					html += '<p>' + getTypeTitle(list[i].info) + '</p>';
					html += '<p>' + iTofixed(list[i].value,2) + '</p>';
					html += '<p>' + list[i].created_time + '</p>';
					html += '</li>';
				}
				$('.list').append(html);
			} else {
				$('.nodata').show()
			}
		}
	})
	$('.complete').click(function () {
		$('#mask1').show();
		$('#genre').animate({
			bottom: '0'
		}, 500);
	})

	$('#genre>ul>li>p').click(function () {
		$('#mask1').hide();
		$(this).addClass('p').siblings().removeClass('p');
		$('#genre').animate({
			bottom: '-40%'
		}, 500);
		$('.complete>span').html($(this).html());
	})
	$('.cancel').click(function () {
		$('#mask1').hide();
		$('#genre').animate({
			bottom: '-40%'
		}, 500);
	})
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