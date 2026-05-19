// preloader
jQuery(window).on('load', function() {
  jQuery('#status').fadeOut();
  jQuery('#preloader').delay(350).fadeOut('slow');
  jQuery('body').delay(350).css({'overflow':'visible'});
})

// toggle button
jQuery(function($){
  $( '.toggle-nav button' ).click( function(e){
    $( 'body' ).toggleClass( 'show-main-menu' );
    var element = $( '.sidenav' );
    apartment_rental_services_trapFocus( element );
  });

  $( '.close-button' ).click( function(e){
    $( '.toggle-nav button' ).click();
    $( '.toggle-nav button' ).focus();
  });
  $( document ).on( 'keyup',function(evt) {
    if ( $( 'body' ).hasClass( 'show-main-menu' ) && evt.keyCode == 27 ) {
      $( '.toggle-nav button' ).click();
      $( '.toggle-nav button' ).focus();
    }
  });
});

function apartment_rental_services_trapFocus( element, namespace ) {
  var apartment_rental_services_focusableEls = element.find( 'a, button' );
  var apartment_rental_services_firstFocusableEl = apartment_rental_services_focusableEls[0];
  var apartment_rental_services_lastFocusableEl = apartment_rental_services_focusableEls[apartment_rental_services_focusableEls.length - 1];
  var KEYCODE_TAB = 9;

  element.keydown( function(e) {
    var isTabPressed = ( e.key === 'Tab' || e.keyCode === KEYCODE_TAB );

    if ( !isTabPressed ) {
      return;
    }

    if ( e.shiftKey ) /* shift + tab */ {
      if ( document.activeElement === apartment_rental_services_firstFocusableEl ) {
        apartment_rental_services_lastFocusableEl.focus();
        e.preventDefault();
      }
    } else /* tab */ {
      if ( document.activeElement === apartment_rental_services_lastFocusableEl ) {
        apartment_rental_services_firstFocusableEl.focus();
        e.preventDefault();
      }
    }
  });
}

jQuery(document).ready(function () {
  // Sticky Header
  jQuery(window).scroll(function () {
    var sticky = jQuery('.header-sticky'),
        scroll = jQuery(this).scrollTop();

    if (scroll >= 100) {
      sticky.addClass('header-fixed');
    } else {
      sticky.removeClass('header-fixed');
    }

    // Scroll to Top Button
    if (scroll > 0) {
      jQuery('#button').fadeIn();
    } else {
      jQuery('#button').fadeOut();
    }
  });

  jQuery('#button').click(function () {
    jQuery("html, body").animate({
      scrollTop: 0
    }, 600);
    return false;
  });

  apartment_rental_services_search_focus();
});

// Slider
jQuery(document).ready(function() {
  jQuery('.owl-carousel').owlCarousel({
    loop: true,
    margin: 0,
    nav:true,
    navText: ["<i class='fa-solid fa-chevron-left'></i>", "<i class='fa-solid fa-chevron-right'></i>"], 
    dots:false,
    rtl:false,
    items: 1,
    autoplay:true,
  })
});