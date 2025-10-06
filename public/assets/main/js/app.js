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

    if ($('[data-aos]').length > 0) {
        AOS.init({ once: true, disable: 'mobile' });
    }

    var dropdown = document.querySelectorAll('[data-dropdown]');
    if (dropdown != null) {
        dropdown.forEach(function(el) {
            let dropdownMenu = el.querySelector(".drop-down-menu");

            function dropdownOP() {
                if (el.getBoundingClientRect().top + dropdownMenu.offsetHeight > window.innerHeight - 60 && el.getAttribute("data-dropdown-position") !== "top") {
                    dropdownMenu.style.top = "auto";
                    dropdownMenu.style.bottom = "40px";
                } else {
                    dropdownMenu.style.top = "40px";
                    dropdownMenu.style.bottom = "auto";
                }
            }
            window.addEventListener("click", function(e) {
                if (el.contains(e.target)) {
                    el.classList.toggle('active');
                    setTimeout(function() {
                        el.classList.toggle('animated');
                    }, 0);
                } else {
                    el.classList.remove('active');
                    el.classList.remove('animated');
                }
                dropdownOP();
            });
            window.addEventListener("resize", dropdownOP);
            window.addEventListener("scroll", dropdownOP);
        });
    }

    let navbar = document.querySelector(".nav-bar");
    if (navbar) {
        let navbarOp = () => {
            if (window.scrollY > 0) {
                navbar.classList.add("scrolling");
            } else {
                navbar.classList.remove("scrolling");
            }
        };
        window.addEventListener("scroll", navbarOp);
        window.addEventListener("load", navbarOp);
    }

    let navbarMenu = document.querySelector(".nav-bar-menu"),
        navbarMenuBtn = document.querySelector(".nav-bar-menu-btn");
    if (navbarMenu) {
        let navbarMenuClose = navbarMenu.querySelector(".nav-bar-menu-close"),
            navbarMenuOverlay = navbarMenu.querySelector(".overlay"),
            navUploadBtn = document.querySelector(".nav-bar-menu [data-upload-btn]");
        navbarMenuBtn.onclick = () => {
            navbarMenu.classList.add("show");
            document.body.classList.add("overflow-hidden");
        };

        navbarMenuClose.onclick = navbarMenuOverlay.onclick = () => {
            navbarMenu.classList.remove("show");
            document.body.classList.remove("overflow-hidden");
        };
        if (navUploadBtn) {
            navUploadBtn.addEventListener("click", () => {
                navbarMenu.classList.remove("show");
            });
        }
    }

    let plans = document.querySelectorAll(".plans .plans-item"),
        planSwitcher = document.querySelector(".plan-switcher");
    if (planSwitcher) {
        planSwitcher.querySelectorAll(".plan-switcher-item").forEach((el, id) => {
            el.onclick = () => {
                planSwitcher.querySelectorAll(".plan-switcher-item").forEach((ele) => {
                    ele.classList.remove("active");
                });
                el.classList.add("active");
                plans.forEach((el) => {
                    el.classList.remove("active");
                });
                plans[id].classList.add("active");
            };
        });
    }

    let lazyLoad = () => {
        let lazy = $('.lazy');
        if (lazy.length) {
            lazy.Lazy({
                afterLoad: function(element) {
                    element.addClass('loaded');
                },
            });
        }
    }

    lazyLoad();

    let avatarInput = $('#change_avatar'),
        targetedImagePreview = $('#avatar_preview');
    if (avatarInput.length) {
        avatarInput.on('change', function() {
            var file = true,
                readLogoURL;
            if (file) {
                readLogoURL = function(input_file) {
                    if (input_file.files && input_file.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            targetedImagePreview.attr('src', e.target.result);
                        }
                        reader.readAsDataURL(input_file.files[0]);
                    }
                }
            }
            readLogoURL(this);
        });
    }

    let generatorOptionsBtn = $('#generator-options-btn'),
        generatorOptions = $('.generator-options');

    generatorOptionsBtn.on('click', function() {
        if (generatorOptions.hasClass('d-none')) {
            generatorOptions.removeClass('d-none');
        } else {
            generatorOptions.addClass('d-none');
        }
    });

    let modelEngine = $('#modelEngine'),
        negativePrompt = $('#negativePrompt'),
        sizes = $('#sizes'),
        sizesSelect = sizes.find('.form-select'),
        artStyles = $('#artStyles'),
        artStylesSelect = artStyles.find('.form-select'),
        lightningStyles = $('#lightningStyles'),
        lightningStylesSelect = lightningStyles.find('.form-select'),
        moods = $('#moods'),
        moodsSelect = moods.find('.form-select'),
        samples = $('#samples'),
        samplesSelect = samples.find('.form-select');

    function loadOptions() {
        let selectedOption = modelEngine.find('option:selected');
        if (selectedOption.data('np') == 1) {
            negativePrompt.removeClass('d-none');
        } else {
            negativePrompt.addClass('d-none');
        }

        if (selectedOption.data('sz')) {
            sizesSelect.empty();
            $.each(selectedOption.data('sz'), function(index, value) {
                sizesSelect.append($('<option>', { value: value, text: value }));
            });
            sizes.removeClass('d-none');
        } else {
            sizes.addClass('d-none');
        }

        if (selectedOption.data('as')) {
            artStylesSelect.empty();
            artStylesSelect.append($('<option>', { value: '', text: '--' }));
            $.each(selectedOption.data('as'), function(index, value) {
                artStylesSelect.append($('<option>', { value: value, text: value }));
            });
            artStyles.removeClass('d-none');
        } else {
            artStyles.addClass('d-none');
        }

        if (selectedOption.data('ls')) {
            lightningStylesSelect.empty();
            lightningStylesSelect.append($('<option>', { value: '', text: '--' }));
            $.each(selectedOption.data('ls'), function(index, value) {
                lightningStylesSelect.append($('<option>', { value: value, text: value }));
            });
            lightningStyles.removeClass('d-none');
        } else {
            lightningStyles.addClass('d-none');
        }

        if (selectedOption.data('moods')) {
            moodsSelect.empty();
            moodsSelect.append($('<option>', { value: '', text: '--' }));
            $.each(selectedOption.data('moods'), function(index, value) {
                moodsSelect.append($('<option>', { value: value, text: value }));
            });
            moods.removeClass('d-none');
        } else {
            moods.addClass('d-none');
        }

        if (selectedOption.data('samples')) {
            samplesSelect.empty();
            $.each(selectedOption.data('samples'), function(index, value) {
                samplesSelect.append($('<option>', { value: value, text: value }));
            });

            if (selectedOption.data('samples').length > 1) {
                samples.removeClass('d-none');
            } else {
                samples.addClass('d-none');
            }
        } else {
            samples.addClass('d-none');
        }
    }

    loadOptions();

    modelEngine.on('change', function() {
        loadOptions()
    });

    let generatorForm = $('#generator');

    generatorForm.on('submit', function(e) {

        var reportValidity = generatorForm[0].reportValidity();

        if (reportValidity) {

            e.preventDefault();

            let action = $(this).attr('action'),
                formData = generatorForm.serializeArray(),
                generatorPromptInput = $('#generator input'),
                generatorSamples = $('#generator select[name=samples]'),
                generatorImagesSize = $('#generator select[name=size]'),
                generatorBtn = $('#generator button'),
                generatorProcessing = $('.processing');

            let defaultImages = $('#default-images'),
                generatedImages = $('#generated-images'),
                viewAllImagesButton = $('#viewAllImagesButton'),
                faqs = $('#faqs'),
                blogArticles = $('#blogArticles');

            if (generatorPromptInput.val() === '') {
                toastr.error(getConfig.generatorPromptError);
            } else if (generatorSamples.val() === null) {
                toastr.error(getConfig.generatorSamplesError);
            } else if (generatorImagesSize.val() === null) {
                toastr.error(getConfig.generatorSizeError);
            } else {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: action,
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        onAjaxStart();
                    },
                    success: function(response) {
                        onAjaxStop();
                        if ($.isEmptyObject(response.error)) {
                            $.each(response.images, function(index, item) {
                                generatedImages.prepend('<div class="col"> <div class="ai-image"> <img class="lazy" data-src="' + item.src + '" alt="' + item.prompt + '" /> <div class="spinner-border"></div> <div class="ai-image-hover"> <p class="mb-0">' + item.prompt + '</p> <div class="row g-2 alig-items-center"> <div class="col"> <a href="' + item.link + '" target="_blank" class="btn btn-primary btn-md w-100">' + getConfig.translates.viewImage + '</a> </div> <div class="col-auto"> <a href="' + item.download_link + '" class="btn btn-light btn-md px-3"><i class="fas fa-download"></i></a> </div> </div> </div> </div> </div>');
                                generatedImages.removeClass('d-none');
                                lazyLoad();
                            });
                        } else {
                            onAjaxStop();
                            generatedImages.addClass('d-none');
                            defaultImages.removeClass('d-none');
                            viewAllImagesButton.removeClass('d-none');
                            toastr.error(response.error);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        onAjaxStop();
                        generatedImages.addClass('d-none');
                        defaultImages.removeClass('d-none');
                        viewAllImagesButton.removeClass('d-none');
                        toastr.error(errorThrown);
                    }
                })
            }

            function onAjaxStart() {
                generatorBtn.prop('disabled', true);
                generatorForm.addClass('d-none');
                defaultImages.addClass('d-none');
                viewAllImagesButton.addClass('d-none');
                blogArticles.addClass('d-none');
                generatorProcessing.removeClass('d-none');
            }

            function onAjaxStop() {
                generatorBtn.prop('disabled', false);
                generatorProcessing.addClass('d-none');
                generatorForm.removeClass('d-none');
                viewAllImagesButton.removeClass('d-none');
                blogArticles.removeClass('d-none');
            }
        }
    });

    let editImage = $('.edit-image'),
        editImageModal = $('#editImageModal'),
        editImageModalForm = $('#editImageModal form'),
        editImageModalImg = $('#editImageModal img'),
        editModalVisibility = document.querySelector("#editImageModal select[name=visibility]");

    editImage.on('click', function(e) {
        e.preventDefault();
        let details = $(this).data('details');
        editImageModalForm.attr('action', details.action);
        editImageModalImg.attr('src', details.image);
        let visibility = editModalVisibility.querySelector(`option[value="${details.visibility}"]`);
        visibility.selected = true;
        editImageModal.modal('show');
    });

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