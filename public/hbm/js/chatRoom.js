$(function(){
    var user_id = $("#user_info").data("address")
    var account_number = ""
    var user_id_db = ""
    var head_portrait = ""
    if (user_id != ""){
        layer_loading()
        $.ajax({
            type: "POST",
            url: laravel_api + "/user/chatlist",
            data: {
                user_id:user_id
            },
            dataType: "json",
            success: function(data){
                layer_close();
                if (data.type == "ok"){
                    account_number = data.message.user.account_number
                    head_portrait = data.message.user.head_portrait
                    user_id_db = data.message.user.id
                    var chat = data.message.chat_list

                    for(i in chat.data){
                            var html = ""
                            if (chat.data[i].from_user_id == user_id_db){
                                html = html + '<div class="rightd">'
                                html = html + '<span class="rightd_h">'
                                html = html + '<img src="'+chat.data[i].from_avatar+'" />'
                                html = html + '</span>'
                                html = html + '<div class="speech_boxr ">'
                                html = html + '<p class="speech_name" style="text-align:right;">'+chat.data[i].from_nickname+'</p>'
                                html = html + '<div class="speech right" >'+chat.data[i].content+'</div>'
                                html = html + '</div>'
                                html = html + '</div>'
                            } else{
                                html = html + '<div class="leftd">'
                                html = html + '<span class="leftd_h">'
                                html = html + '<img src="'+chat.data[i].from_avatar+'" />'
                                html = html + '</span>'
                                html = html + '<div class="speech_boxl">'
                                html = html + '<p class="speech_name">'+chat.data[i].from_nickname+'</p>'
                                html = html + '<div class="speech left" >'+chat.data[i].content+'</div>'
                                html = html + '</div>'
                                html = html + '</div>'
                            }
                            $("#msg_content").append(html)
                    }
                    $('.chatRoom').scrollTop( $('.chatRoom')[0].scrollHeight );
                    console.log(chat.data)
                } else{
                    layer_msg(data.message)
                    return false;
                }
            }
        });
    }

    var socket = io(socket_api);

    var uid = user_id;
    // socket连接后以uid登录
    socket.on('connect', function(){
        socket.emit('login', uid);
    });
    // 后端推送来消息时
    socket.on('new_msg', function(msg){
        if (msg.user_id != user_id_db){
            var html = ""
            html = html + '<div class="leftd">'
            html = html + '<span class="leftd_h">'
            html = html + '<img src="'+msg.head_portrait+'" />'
            html = html + '</span>'
            html = html + '<div class="speech_boxl">'
            html = html + '<p class="speech_name">'+msg.user_name+'</p>'
            html = html + '<div class="speech left" >'+msg.content+'</div>'
            html = html + '</div>'
            html = html + '</div>'
            $("#msg_content").append(html)
        }
        console.log(msg);
    });
    // 后端推送来在线数据时
    socket.on('update_online_count', function(online_stat){
        $("#chat_num").html("聊天室("+online_stat+")")
        console.log(online_stat)
    });

    $("#send_msg").click(function () {
        var content = $("#content").val()
        if (content == ""){
            layer_msg("请填写内容");
            return false;
        }
        var timestamp = Date.parse(new Date());
        var new_div = "div_"+timestamp
        var html = ""
        html = html + '<div class="rightd">'
        html = html + '<span class="rightd_h">'
        html = html + '<img src="'+head_portrait+'" />'
        html = html + '</span>'
        html = html + '<div class="speech_boxr loading_icon '+new_div+'">'
        html = html + '<p class="speech_name" style="text-align:right;">'+account_number+'</p>'
        html = html + '<div class="speech right" >'+content+'</div>'
        html = html + '</div>'
        html = html + '</div>'

        $("#msg_content").append(html)

        $("#content").val("")
        $('.chatRoom').scrollTop( $('.chatRoom')[0].scrollHeight );


        $.ajax({
            type: "POST",
            url: laravel_api + "/user/chat",
            data: {
                user_id:user_id,
                content:content
            },
            dataType: "json",
            success: function(data){
                $("."+new_div).removeClass("loading_icon")
                if (data.type =="ok"){

                }else{
                    $("."+new_div).addClass("gan_icon")
                    layer_msg(data.message)
                }
            }
        });
    })
});