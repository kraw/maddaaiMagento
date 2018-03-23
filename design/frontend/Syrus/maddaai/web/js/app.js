require(['jquery', 'popper', 'tether'], function($, Popper, Tether) {
    window.Popper = Popper; // re-attach to global scope
    window.Tether = Tether;
    require(['bootstrap'], function() {
        $(function() {
            // This function is needed (even if empty) to force RequireJS to load Twitter Bootstrap and its Data API.
            // You can make calls to bootstrap functions here.
        });
    });


    $( document ).ready(function() {
        if ( $('#bid-time').length )
            // update every second
            setInterval(displayProductsTimer, 1000);
        else if ( $('#bid-time').length )
            setInterval(displayProductTimer, 1000);
    });


    function displayProductTimer() {
        var prod = document.getElementById('bid-timer');
        var end = new Date(prod.dataset.bidend);
        displayTimer(prod, end);
    }

    function displayProductsTimer() {
        $('#bid-time [id^="bid-timer-"]').each(function () {
            var id = this.id;
            var val = $(this).data("bidend").split(" ");
            var end = new Date(val[0]);
            displayTimer(id, end);
        });
    }

    function displayTimer(id, end){
        'strict mode'
        var second = 1000;
        var minute = second * 60;
        var hour = minute * 60;
        var day = hour * 24;
        var now = new Date();
        var remain = end - now;
        if (remain < 0) {
            clearInterval(timer);
                document.getElementById(id).innerHTML = 'Concluso!';
            return;
        }
        var days = Math.floor(remain / day);
        var hours = Math.floor((remain % day) / hour);
        var minutes = Math.floor((remain % hour) / minute);
        var seconds = Math.floor((remain % minute) / second);

        document.getElementById(id).innerHTML =
            days + ' G ' + hours + ' h ' + minutes + ' m ' + seconds + ' s';

    }

});

