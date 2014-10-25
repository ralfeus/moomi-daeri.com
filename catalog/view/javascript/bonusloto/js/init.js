/**
 * Created with JetBrains PhpStorm.
 * User: Vitaly
 * Date: 27.02.13
 * Time: 20:42
 * To change this template use File | Settings | File Templates.
 */

/* isset for javascript */
window.isset = function (v) {
    if (typeof(v) == 'object' && v == 'undefined') {
        return false;
    } else  if (arguments.length === 0) {
        return false;
    } else {
        var buff = arguments[0];
        for (var i = 0; i < arguments.length; i++){
            if (typeof(buff) === 'undefined' || buff === null) return false;
            buff = buff[arguments[i+1]];
        }
    }
    return true;
};
function rand( min, max ) {
    if( max ) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    } else {
        return Math.floor(Math.random() * (min + 1));
    }
}

function shuffle(o){
    for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
}

var Lottery = {
    loadData: function(vars) {
       var obj = {};

        $.ajax({
            type: 'POST',
            url: 'index.php?route=module/lototron/start',
            async: true,
            cache: false,
            dataType: 'json',
            data: 'action=get-config',
            success: function(answer) {
                if(isset(answer.vip)) {
			$('#lott-vip').html(answer.vip);
                }
                if(isset(answer.errors)) {
                    $.each(answer.errors, function(k,val) {
                        $.jGrowl(val, {theme: 'errors', header: answer.text_error3, life: 2000 });
                    });

                   if(isset(answer.lottery) && answer.lottery == 'off') {
                      $('.lott-timer-title').text(answer.text_error1);
		      $('.lott-priz-title').text(answer.text_error2);
                      $('#lott-timer-box ul.desc').remove();
                    }
                }
                 else {
                    if(isset(answer.winner)){
                        obj.winner = answer.winner;
                    }
                     else if(answer.start_time > answer.curr_time) {
			$('.lott-priz-description').html(answer.priz_desc);


                        $('#lott-timer').countdown({
                            startTime: answer.difference,
                            stepTime: 50,
                            digitImages: 6,
                            digitWidth: answer.images_digitWidth,
                            digitHeight: answer.images_digitHeight,
                            image: answer.images_countdown,
                            timerEnd: function() {
                                Lottery.startLototron(answer);
                            }
                        });
                    }
		    if(answer.user_vip_only =='1') {
			 $('.user-list-title').text(answer.user_vip_text);
		    }
		    obj.users_not = answer.users_not;
		    obj.text_game1 = answer.text_game1;
		    obj.text_game2 = answer.text_game2;
		    obj.text_game3 = answer.text_game3;
		    obj.text_error1 = answer.text_error1;
		    obj.text_error2 = answer.text_error2;
		    obj.text_error3 = answer.text_error3;
                    if(isset(answer.user_list) && answer.user_list.length > 0) {
                         obj.user_list = answer.user_list;
	                 
				if(answer.count_user_list =='1') {
					$('.lott-user-count').text('(' + answer.user_list.length + ')');
				}
                      }
                    Lottery.printData(obj);
                }
            }
        });
    },
    startLototron: function(vars) {
         var is, obj = {}, ok = true, ld = $.Deferred();
         is = Lottery.runEffects(vars);

     setTimeout(function() {

        $.ajax({
            type: 'POST',
            url: 'index.php?route=module/lototron/start',
            async: true,
            cache: false,
            dataType: 'json',
            data: 'action=checklototron',
            success: function(answer) {
		    obj.text_game1 = answer.text_game1;
		    obj.text_game2 = answer.text_game2;
		    obj.text_game3 = answer.text_game3;
		    obj.text_error1 = answer.text_error1;
		    obj.text_error2 = answer.text_error2;
		    obj.text_error3 = answer.text_error3;
                if(isset(answer.errors)) {
                    $.each(answer.errors, function(k,val) {
                        $.jGrowl(val, {theme: 'errors', header: answer.text_error3, life: 2000 });
                    });
                }
                 else if(isset(answer.winner) && answer.winner.length > 0) {
                    obj.winner = answer.winner;
                 }

            }
        });

    }, 7000);

        is.done(function(){
           if(isset(obj.winner)) {
              Lottery.printData(obj);
            } else {
               Lottery.startLototron(vars);
           }
        });

    },
    printData: function(vars) {

     if(isset(vars.user_list) && vars.user_list.length > 0) {
        var lines = '';
        $.each(vars.user_list, function(k,val) {
            if(isset(vars.winner) && vars.winner[0].uid == val.uid){
               lines += '<li class="li-winner"><a href="#" target="_blank">'+ val.name +'</a></li>';
            } else {
               lines += '<li><a href="#" target="_blank">'+ val.name +'</a></li>';
            }
        });
//        $('#user-list').html(lines);
       } 
//	else {
//               lines = '<li><a href="#" target="_blank">' + vars.users_not + '</a></li>';
//       }
        $('#user-list').html(lines);
     if(isset(vars.winner)){
         if(!isset(vars.user_list)){
             var uid, list = $('#user-list li');
         list.removeClass('li-winner');
         $.each(list, function(k,item) {
             uid = $(item).find('a').data('uid');
             if(vars.winner[0].uid == uid) {
                 $(item).addClass('li-winner');
             }
          });
         }
         $('.lott-timer-title').text(vars.text_game2);
         $('#lott-timer').html('<div class="winner-title">' + vars.text_game3 + '</div><div class="winner"><a href="index.php?route=information/bonusloto&bonusloto_id='+ vars.winner[0].uid +'" target="_blank">'+ vars.winner[0].name +'</a></div>')
                         .css({height:'auto',overflow:'none'});
         $('#lott-timer-box ul.desc').remove();
     }
   },
   runEffects: function(vars) {
      var c = 0, list = $('#user-list li'),
          li, r, len = list.length, sh,
          user, si,  ok = true, is = $.Deferred();

       $('.lott-timer-title').text(vars.text_game1);
       $('#lott-timer').html('<div class="winner">&nbsp;</div>')
                       .css({height:'auto',overflow:'none'});
       $('#lott-timer-box ul.desc').remove();

      si = setInterval(function() {

            sh = shuffle($('#user-list li'));
            r = rand(0, (len-1));
            li = $(sh[r]);
            list.removeClass('li-winner');
            li.addClass('li-winner');
            user = li.find('a').text();
            $('#user-list').html(sh);
            $('#lott-timer div.winner').text(user);

          ++c;
          if(c > 35) {
             clearInterval(si);
              is.resolve(ok);
           }
        }, 200);

       return is;
   }
};

$(document).ready(function() {

    Lottery.loadData({});

// Get all the thumbnail
    $('div.thumbnail-item').mouseenter(function(e) {
        // Calculate the position of the image tooltip
        x = e.pageX - $(this).offset().left;
        y = e.pageY - $(this).offset().top;

        // Set the z-index of the current item,
        // make sure it's greater than the rest of thumbnail items
        // Set the position and display the image tooltip
        $(this).css('z-index','15')
            .children("div.tooltip")
            .css({'top': y + 10,'left': x + 20,'display':'block'});
    }).mousemove(function(e) {
            // Calculate the position of the image tooltip
            x = e.pageX - $(this).offset().left;
            y = e.pageY - $(this).offset().top;

            // This line causes the tooltip will follow the mouse pointer
            $(this).children("div.tooltip").css({'top': y + 10,'left': x + 20});

        }).mouseleave(function() {

            // Reset the z-index and hide the image tooltip
            $(this).css('z-index','1')
                .children("div.tooltip")
                .animate({"opacity": "hide"}, "fast");
        });
});
