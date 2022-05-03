jQuery(document).ready(function(){
    var hamburger = jQuery('.navbar-toggle'),
        navBar = jQuery('.navigation'),
        backDrop = '<div class="backdrop"></div>';
        hamburger.on('click', function () {
            navBar.addClass('push-menu-visible');
			jQuery('body').addClass('show-nav');
            jQuery(backDrop).appendTo('body').on('click', function () {
                navBar.removeClass('push-menu-visible');
                jQuery(this).remove();
                jQuery('body').removeClass('show-nav');
            });
        });

    var navbarClose = jQuery('.navbar-close'),
        navBar = jQuery('.navigation');
    if (navbarClose.length > 0) {
        navbarClose.on('click', function () {
            navBar.removeClass('push-menu-visible');
            jQuery('body').removeClass('show-nav');
            jQuery('.backdrop').remove();
        });
    }
});

//footer menu
if (jQuery(window).width() <= 991) {
jQuery('.footer-toggle').click(function(){
  jQuery(this).toggleClass('active');
  jQuery(this).parent().find('.arrow').toggleClass('arrow-animate');
  jQuery(this).parent().find('.content-footer').slideToggle(280);
});
};
//Login Popup


// After Login JS
!(function (s) {
    "use strict";
    
    
    s("#side-menu").metisMenu(),
        s("#vertical-menu-btn").on("click", function (e) {
            e.preventDefault(), s("body").toggleClass("sidebar-enable"), 992 <= s(window).width() ? s("body").toggleClass("vertical-collpsed") : s("body").removeClass("vertical-collpsed");
        }),
        s("#sidebar-menu a").each(function () {
            var e = window.location.href.split(/[?#]/)[0];
            this.href == e &&
                (s(this).addClass("active"),
                s(this).parent().addClass("mm-active"),
                s(this).parent().parent().addClass("mm-show"),
                s(this).parent().parent().prev().addClass("mm-active"),
                s(this).parent().parent().parent().addClass("mm-active"),
                s(this).parent().parent().parent().parent().addClass("mm-show"),
                s(this).parent().parent().parent().parent().parent().addClass("mm-active"));
        }),
        s(document).ready(function () {
            var e;
            0 < s("#sidebar-menu").length &&
                0 < s("#sidebar-menu .mm-active .active").length &&
                300 < (e = s("#sidebar-menu .mm-active .active").offset().top) &&
                ((e -= 300), s(".vertical-menu .simplebar-content-wrapper").animate({ scrollTop: e }, "slow"));
        }),
        s(".navbar-nav a").each(function () {
            var e = window.location.href.split(/[?#]/)[0];
            this.href == e &&
                (s(this).addClass("active"),
                s(this).parent().addClass("active"),
                s(this).parent().parent().addClass("active"),
                s(this).parent().parent().parent().addClass("active"),
                s(this).parent().parent().parent().parent().addClass("active"),
                s(this).parent().parent().parent().parent().parent().addClass("active"),
                s(this).parent().parent().parent().parent().parent().parent().addClass("active"));
        }),
        
       
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function (e) {
            return new bootstrap.Tooltip(e);
        }),
        [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).map(function (e) {
            return new bootstrap.Popover(e);
        }),
        [].slice.call(document.querySelectorAll(".offcanvas")).map(function (e) {
            return new bootstrap.Offcanvas(e);
        })
})(jQuery);



