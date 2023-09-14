var vue = new Vue({
	el: '#app',
	data: {
		Lists: [],
		type: 'lever',
		swiperSlide: function () { },
		datas: {},
		types: 0,
		url: 'tradeAccount.html?id=',
		changeCount: 0,
		leverCount: 0,
		microCount: 0,
		show: true,
		hideit: '*****',
		legalCount:0,



	},
	mounted: function () {
		let that = this;
		let text = '';
		that.listAjax(text);
		that.swipers();
	},
	filters: {
		toFixedTwo: function (value) {
			var vals = iTofixed(value,5)
			return vals;
		}
	},
	methods: {
		// 头部轮播图
		search() {
			let that = this;
			let text = $('.search_text').val();
			that.listAjax(text);
		},
		listAjax(texts) {
			let that = this;
			initDataTokens({
				url: 'wallet/list',
				data: {
					currency_name: texts
				},
				type: 'post'
			}, function (res) {
				if (res.type == 'ok') {
					if (texts == '') {
						that.datas = res.message;
					}
					that.changeCount = res.message.change_wallet.usdt_totle;
					that.leverCount = res.message.lever_wallet.usdt_totle;
					that.microCount = res.message.micro_wallet.usdt_totle;
					that.legalCount = res.message.legal_wallet.usdt_totle;
					if (that.type == 'lever') {
						that.Lists = res.message.lever_wallet.balance;
						that.types = 0;
					} else if (that.type == 'micro') {
						that.Lists = res.message.micro_wallet.balance;
						that.types = 1;
					} else if (that.type == 'match') {
						that.Lists = res.message.change_wallet.balance;
						that.types = 2;
					}else if (that.type == 'legal') {
						that.Lists = res.message.legal_wallet.balance;
						that.types = 3;
					}
				}

			})
				// initDataTokens({
				// 	url: 'update_balance',
				// }, function (res) {
				// 	if (res.type == 'ok') {

				// 	}

				// })
		},
		// 头部轮播图切换
		swipers() {
			let that = this;
			that.swiperSlide = new Swiper('.mycontainer', {
				slidesPerView: 'auto',
				on: {
					transitionEnd: function () {
						$('.search_text').val('');
						current = that.swiperSlide.snapIndex;
						i = current;
						if (current == 0) {
							that.types = 0;
							that.type = 'lever';
							that.Lists = that.datas.lever_wallet.balance;
						} else if (current == 1) {
							that.types = 1;
							that.Lists = that.datas.micro_wallet.balance;
							that.type = 'micro';
						} else if (current == 2) {
							that.types = 2;
							that.Lists = that.datas.change_wallet.balance;
							that.type = 'match';
						}else if (current == 3) {
							that.types = 3;
							that.Lists = that.datas.legal_wallet.balance;
							that.type = 'legal';
						}


					},
				},
			});
		},
		tabClick(options) {
			$('.search_text').val('');
			let that = this;
			that.type = options;
			if (options == 'lever') {
				that.swiperSlide.slideTo(0);
				that.types = 0;
				that.Lists = that.datas.lever_wallet.balance;
			} else if (options == 'micro') {
				that.swiperSlide.slideTo(1);
				that.types = 1;
				that.Lists = that.datas.micro_wallet.balance;
			} else if (options == 'match') {
				that.swiperSlide.slideTo(2);
				that.types = 2;
				that.Lists = that.datas.change_wallet.balance;
			}else if (options == 'legal') {
				that.swiperSlide.slideTo(3);
				that.types = 3;
				that.Lists = that.datas.legal_wallet.balance;
			}
		},
		// 链接跳转
		links(options) {
			let that = this;
			if (that.type == 'lever') {
				window.location.href = 'leverAccount.html?id=' + options + '&type=3';
			} else if (that.type == 'micro') {
				window.location.href = 'microAccount.html?id=' + options + '&type=4';
			} else if (that.type == 'match') {
				window.location.href = 'matchAccount.html?id=' + options + '&type=2';
			}else if (that.type == 'legal') {
				window.location.href = 'legalAccount.html?id=' + options + '&type=1';
			}
		},
		hide() {
			this.show = !this.show;
		}

	}
});

