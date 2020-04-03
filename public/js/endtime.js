(function ($) {
    $.fn.downCount = function (options, callback) {
        var settings = $.extend({
            date: null,
            currentDate:null
        }, options);

        if (!settings.date) {
            $.error('Date is not defined.');
        }
        if (!settings.currentDate) {
            $.error('currentDate is not defined.');
        }
        if (!Date.parse(settings.date)) {
            $.error('Incorrect date format, it should look like this, 12/24/2012 12:00:00.');
        }

        var container = this;
        var star_date = new Date(settings.date).getTime(), //开始时间
            current_date = new Date(settings.currentDate).getTime(); // 当前时间
        var difference = star_date - current_date;

        function countdown(difference) {

            // console.log(difference);
            if (difference < 0) {
                // 停止计时
                clearInterval(interval);

                if (callback && typeof callback === 'function') callback();

                return;
            }

            // basic math variables·
            var _second = 1000,
                _minute = _second * 60,
                _hour = _minute * 60,
                _day = _hour * 24;

            // calculate dates
            var days = Math.floor(difference / _day),
                hours = Math.floor((difference % _day) / _hour),
                minutes = Math.floor((difference % _hour) / _minute),
                seconds = Math.floor((difference % _minute) / _second);

            // fix dates so that it will show two digets
            days = (String(days).length >= 2) ? days : '0' + days;
            hours = (String(hours).length >= 2) ? hours : '0' + hours;
            minutes = (String(minutes).length >= 2) ? minutes : '0' + minutes;
            seconds = (String(seconds).length >= 2) ? seconds : '0' + seconds;

            if(days==0&&hours==0&&minutes==0){

                container.html(seconds+'秒');
            }else{
                container.html('<span class="assemble-time-content-info">'+days+'</span><span class="assemble-time-content-item">天</span>'+'<span class="assemble-time-content-info">'+ hours+'</span><span class="assemble-time-content-item">时</span>'+'<span class="assemble-time-content-info">'+minutes+'</span><span class="assemble-time-content-item">分</span>'+'<span class="assemble-time-content-info">'+seconds+'</span><span class="assemble-time-content-item">秒</span>');
            }
        };
        // start
        var interval = setInterval(
            function(){
                countdown(difference);
                difference=difference-1000;
            },
            1000
        );
    };

})(jQuery);
