$(function(){
	//=================收藏==========================
	$('.collect').click(function(){
		if($(this).hasClass('act') == false){
			$(this).addClass('act');
			$(this).attr('src','images/collect1.png');
		}else{
			$(this).removeClass('act');
			$(this).attr('src','images/collect.png');
		}
	})
    //================限价与额度=============================		
        $('.limitedprice').click(function(){
            $('#mask1').show();
            // $('#Limited>ul>li').eq(0).html('<p class="price">限价</p><p class="price">市价</p>');
            // $('#Limited>ul>li>p').eq(0).css('color','#00c087');
           
        });
        $('#Limited').on('click','p',function(){
            var i = $(this).index();
            $(this).css('color','#00c087').siblings().css('color','#ccc');
            $('#mask1').hide();
            $('.limitedprice').html($(this).html() +'<img src="images/pulldown.png"/>');
            if(i==0){
                $('.price_num').attr('disabled',false);
            }else{
                $('.price_num').val($('.new_price').text());
                $('.price_num').attr('disabled',true);
            }	
        })	
        $('.limit').click(function(){
            $('#mask1').show();
            $('#Limited>ul>li').eq(0).html('<p>1</p><p>2</p><p>3</p><p>4</p><p>5</p>');
            $('#Limited>ul>li>p').eq(0).css('color','#00c087');            	
            $('#Limited>ul>li>p').click(function(){
                $(this).css('color','#00c087').siblings().css('color','#ccc');	
                $('#mask1').hide();
                $('.limit').html('深度'+ $(this).html()+'<img src="images/pulldown.png"/>');
            });    
        });



        $('.cancel').click(function(){
            $('#mask1').hide(10);
        });
        
        
        
        
        //==============限价数量===================
        $('.Btc>li').eq(0).find('span').addClass('span').find('i').addClass('cc');
        $('.Btc>li').click(function(){
            $('.absolute').removeClass('span').find('i').removeClass('cc').parent().prev('a').removeClass('back');
            var index = $(this).index();
            $(".Btc>li:lt("+(index+1)+")").find('span').addClass('span').find('i').addClass('cc').parent().prev('a').addClass('back');
        
        });
        
        
        //================BTC====================
        
        // $('.deal>span').eq(0).addClass('bor').find('a').addClass('col').siblings('em').addClass('borl');
        $('.buy').click(function(){
            $(this).addClass('bor').find('a').addClass('col').siblings('em').removeClass('borr2').addClass('borl').parent().next().removeClass('bor2').find('a').removeClass('col2');
             $('.transactionAmount').find('button').html(getlg('buy')+'<span class="btn_name ">'+$('.legal_name').text()+'</span>').removeClass('activesell');  

        });
        $('.sell').click(function(){
            $(this).addClass('bor2').find('a').addClass('col2').parent().prev().removeClass('bor').find('a').removeClass('col').siblings('em').removeClass('borl').addClass('borr2');
             $('.transactionAmount').find('button').html(getlg('sell')+'<span class="btn_name ">'+$('.legal_name').text()+'</span>').addClass('activesell')     
        })
        
        //=================买入卖出====================
        var num = Number($('.Price>input').eq(0).val()) ;
        $('.subtract').click(function(){
            num -=1;
            if(num<0){
                num = 0;
            }
            $('.Price>input').val(num);
        });
        $('.plus').click(function(){
            num +=1;
            $('.Price>input').val(num);
        });	
        
    
    
        $('.sideOpen').click(function(){    ``
            $('body').css('overflow','hidden');
            $('#Limited').hide();
            $('#mask1').show();
            $('#sideColumn').animate({
                left:'0px'
            },1000);
        })
        $('.sideclose').click(function(){
            $('body').css('overflow','auto');
            $('#Limited').show();
            $('#mask1').hide();
            $('#sideColumn').animate({
                left:'-80%'
            },1000);
        })
        $('.ul').eq(1).show();
        $('#sideColumn>ol>li').click(function(){
            $(this).addClass('side').siblings().removeClass('side');
            var index = $(this).index();
            $('.ul').hide().eq(index).show();
        })	
    })
    
$('.bt li').click(function () {
	var that = this;
	$(".current .tab-sj").each(function(index,value){
			if($(that).index()==index){
				$(value).show();
				$(value).siblings().hide();
				$(that).addClass("border");
				$(that).siblings().removeClass("border");
			}
		})
});
//渲染数据
$(document).ready(function(){
	$
});