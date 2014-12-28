require.config({
    baseUrl: './source/modules/shouse/template/js',
    paths: {
        'jquery': 'jquery-1.11.1.min',
        'FlexSlider': 'FlexSlider',
        'template': 'template.min',
        'template1': 'template',
        'bootstrap': 'bootstrap.min',
        'jquery.cookie' : 'jquery.cookie.min',
        'WeixinApi' : 'WeixinApi.min'
    },
    shim: {
　　　　'lazyload': {
　　　　　　deps: ['jquery'],
　　　　　　exports: '$.fn.scroll'
　　　　},
　　　　'bootstrap': {
　　　　　　deps: ['jquery'],
　　　　　　exports: 'bootstrap'
　　　　}
    }
});