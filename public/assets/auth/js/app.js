(function($) {
    "use strict";

    document.querySelectorAll('[data-year]').forEach(function(el) {
        el.textContent = new Date().getFullYear();
    });

    let inputNumeric = document.querySelectorAll('.input-numeric');
    if (inputNumeric) {
        inputNumeric.forEach((el) => {
            el.oninput = () => {
                el.value = el.value.replace(/[^0-9]/g, '').substr(0, 6);
            };
        });
    }

    let clipboardBtn = document.querySelectorAll(".btn-copy");
    if (clipboardBtn) {
        clipboardBtn.forEach((el) => {
            let clipboard = new ClipboardJS(el);
            clipboard.on("success", () => {
                toastr.success(getConfig.copiedToClipboardSuccess);
            });
        });
    }

    let actionConfirm = $('.action-confirm');
    if (actionConfirm.length) {
        actionConfirm.on('click', function(e) {
            if (!confirm(getConfig.actionConfirm)) {
                e.preventDefault();
            }
        });
    }

    var dropdown = document.querySelectorAll('[data-dropdown]');
    if (dropdown) {
        dropdown.forEach(function(el) {
            window.addEventListener("click", function(e) {
                if (el.contains(e.target)) {
                    el.classList.toggle("active");
                    setTimeout(function() {
                        el.classList.toggle("animated");
                    }, 10);
                } else {
                    el.classList.remove("active");
                    el.classList.remove("animated");
                }
            });
        });
    }

    function changeCaptchaTheme(theme) {
        let grecaptcha = $('.g-recaptcha'),
            cfturnstile = $('.cf-turnstile'),
            hcaptcha = $('.h-captcha');

        if (grecaptcha.length) {
            grecaptcha.attr("data-theme", theme);
        } else if (cfturnstile.length) {
            cfturnstile.attr("data-theme", theme);
        } else if (hcaptcha.length) {
            hcaptcha.attr("data-theme", theme);
        }
    }

    let themeBtn = document.querySelector(".btn-theme"),
        logoDark = document.querySelector(".logo-dark"),
        logoLight = document.querySelector(".logo-light");
    if (themeBtn) {
        themeBtn.onclick = () => {
            document.body.classList.toggle("dark");
            if (document.body.classList.contains("dark")) {
                document.cookie = "Theme=dark; expires=31 Dec 2080 12:00:00 GMT; path=/";
                logoDark.classList.add("d-none");
                logoLight.classList.remove("d-none");
                changeCaptchaTheme('dark');
            } else {
                document.cookie = "Theme=light; expires=31 Dec 2080 12:00:00 GMT; path=/";
                logoLight.classList.add("d-none");
                logoDark.classList.remove("d-none");
                changeCaptchaTheme('light');
            }
        };
    }

    if (document.cookie.indexOf("Theme=dark") != -1) {
        document.body.classList.add("dark");
        logoDark.classList.add("d-none");
        logoLight.classList.remove("d-none");
        changeCaptchaTheme('dark');
    } else if (document.cookie.indexOf("Theme=light") != -1) {
        document.body.classList.remove("dark");
        logoLight.classList.add("d-none");
        logoDark.classList.remove("d-none");
        changeCaptchaTheme('light');
    } else {
        if (config.themeMode == "auto") {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.body.classList.add("dark");
                logoDark.classList.add("d-none");
                logoLight.classList.remove("d-none");
                changeCaptchaTheme('dark');
            } else {
                document.body.classList.remove("dark");
                logoLight.classList.add("d-none");
                logoDark.classList.remove("d-none");
                changeCaptchaTheme('light');
            }
        } else if (config.themeMode == "dark") {
            document.body.classList.add("dark");
            logoDark.classList.add("d-none");
            logoLight.classList.remove("d-none");
            changeCaptchaTheme('dark');
        } else {
            document.body.classList.remove("dark");
            logoLight.classList.add("d-none");
            logoDark.classList.remove("d-none");
            changeCaptchaTheme('light');
        }
    }

})(jQuery);