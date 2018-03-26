var config = {
    paths: {
        'popper': 'js/popper.min',
        'tether' : "js/tether.min",
        'bootstrap': 'js/bootstrap.min'
    },
    shim: {
        'popper': {
            'deps': ['jquery'],
            'exports': 'Popper'
        },
        "tether": ["jquery"],

        'bootstrap': {
            'deps': ['jquery', 'popper']
        }
    },

};