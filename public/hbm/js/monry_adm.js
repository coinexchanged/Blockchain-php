$(function () {
    // 整体切换
    var current = 0;
    window.onscroll = function(){
        var y = window.scrollY;
        if (y != 0) {
          $('header').addClass('header-scroll');
        } else {
          $('header').removeClass('header-scroll');
        }
    }
    $('.level_con').eq(current).show().siblings('.level_con').hide();
    var mySwiper = new Swiper(".swiper-container", {
        slidesPerView: "auto",
        centeredSlides: !0,
        watchSlidesProgress: !0,
        pagination: ".swiper-pagination",
        onProgress: function (a) {
          var b, c, d;
          for (b = 0; b < a.slides.length; b++) c = a.slides[b],
            d = c.progress,
            scale = 1 - Math.min(Math.abs(.2 * d), 1),
            es = c.style,
            //透明度的改变，原代码如下   es.opacity = 1 - Math.min(Math.abs(d / 2), 1),
            es.opacity = 1,
            es.webkitTransform = es.MsTransform = es.msTransform = es.MozTransform = es.OTransform = es.transform =
            "translate3d(0px,0," + -Math.abs(150 * d) + "px)"
        },
        onSetTransition: function (a, b) {
            for (var c = 0; c < a.slides.length; c++) es = a.slides[c].style,
                es.webkitTransitionDuration = es.MsTransitionDuration = es.msTransitionDuration = es.MozTransitionDuration =
                es.OTransitionDuration = es.transitionDuration = b + "ms"
        },
        onTransitionEnd: function (swiper) {		
            $(".level_con").hide();
            $(".level_con").eq(swiper.activeIndex).show();
        },
        onSlideChangeEnd: function (swiper) {
            current = swiper.activeIndex;
            $('.level_con').eq(current).show().siblings('.level_con').hide();
            $('.top_tab span').eq(current).addClass('active').siblings().removeClass()
        }
    });
    $('.top_tab span').click(function() {
        current = $(this).index();
        $(this).addClass('active').siblings().removeClass();
        mySwiper.slideTo(current);
        $('.level_con').eq(current).show().siblings('.level_con').hide();
    })
    // 整体切换----end


    // 数据接口
    

});