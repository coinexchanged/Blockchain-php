$(function(){
    var address = $("#user_info").data("address");
    if (address != ""){
        layer_loading();
        $.ajax({
            type: "post",
            url: laravel_api + "/account/list",
            data: {address:address},
            dataType: "json",
            success: function(data){
                layer_close();
                console.log(data);
                if (data.type == "ok"){
                    if(data.message.data.length > 0){
                     $(".black_png").addClass("hide");
                      render_list(data.message)
                    }else{
                        $(".black_png").removeClass("hide"); 
                    }
                } else{
                    layer_msg(data.message)
                    return false;
                }
            }
        });
    }

    $(window).scroll(function(){
        var scrollTop = $(this).scrollTop();
        var scrollHeight = $(document).height();
        var windowHeight = $(this).height();
        var params = get_all_params() || {};
        if(scrollTop + windowHeight == scrollHeight){
            var next_page = parseInt($('#data_ul').data("page")) + 1;
            console.log($('#data_ul').data("page"));
            params.address = address;
            params.page = next_page;
            console.log(params)
            if (address != ""){
            $.ajax({
                type: "POST",
                url: laravel_api + "/account/list",
                data: params,
                dataType: "json",
                success: function(data){
                    if (data.type == "ok"){
                        if(data.message.data.length > 0){
                            $('#data_ul').data("page",data.message.page);
                            render_list(data.message);
                        }else{
                            layer.open({
                                content: "没有更多数据"
                                ,skin: 'msg'
                                ,time: 2 
                            });
                        }                            
                    } 
                }
            })   
          }      
        }
    }) 

    function render_list(list){
        console.log(list);
        var item = list.data;
        var user_id =list.user_id;
        if(item && item.length>0){
            var html = "";
            for(i in item){
                console.log(i);
                html += '<li >'
                html += '<a href="javascript:;">'
                html += '<div class="currency_box">'
                html += '<div style="width:70%;">'
                html += '<p class="fontc_3 line1 ">'
                html += '<span class="fontc_6 font16">'+item[i].info+'</span>'
                html += '</p>'
                html += '<p class="font12 fontc_9">'+item[i].created_time+'</p>'
                html += '</div></div>'
                html += '<div class="fontc_3 mgr10">'
                html += '<span>'+item[i].value+'</span>'
                html += '</div>'
                
                html += '</a>'
                html += '</li>'
            }
            $("#data_ul").append(html);
        }
    }
});

