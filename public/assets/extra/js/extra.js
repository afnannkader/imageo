(function($) {
    "use strict";

    let cookies = document.querySelector('.cookies');
    if (cookies) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                cookies.classList.add('show');
            }, 1000);
        });
    }

    let acceptCookie = $('#acceptCookie'),
        cookieDiv = $('.cookies');
    acceptCookie.on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: getConfig.baseURL + '/cookie/accept',
            type: 'get',
            dataType: "JSON",
        });
        cookieDiv.remove();
    });

    let loadModalBtn = document.querySelector('#loadModalBtn');
    if (loadModalBtn) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                loadModalBtn.click();
                loadModalBtn.remove();
            }, 1000);
        });
        document.querySelector('#load-modal .btn-close').onclick = () => {
            $.ajax({
                url: getConfig.baseURL + '/popup/close',
                type: 'get',
                dataType: "JSON",
                success: function() {
                    $('load-modal').remove();
                },
            });
        };
    }

})(jQuery);