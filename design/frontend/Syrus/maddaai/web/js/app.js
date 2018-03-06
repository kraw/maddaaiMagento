require(['jquery', 'popper', 'tether'], function($, Popper, Tether) {
    window.Popper = Popper; // re-attach to global scope
    window.Tether = Tether;
    require(['bootstrap'], function() {
        $(function() {
            // This function is needed (even if empty) to force RequireJS to load Twitter Bootstrap and its Data API.
            // You can make calls to bootstrap functions here.
        });
    });
});
