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
jQuery('.footer-toggle').click(function(){
  jQuery(this).toggleClass('active');
  jQuery(this).parent().find('.arrow').toggleClass('arrow-animate');
  jQuery(this).parent().find('.content').slideToggle(280);
});

//Login Popup


