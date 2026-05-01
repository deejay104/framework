(function($) {
  'use strict';
  $(function() {
    $('[data-toggle="offcanvas"]').on("click", function() {
      console.log("click");
      $('.sidebar-offcanvas-open').toggleClass('active')
    });
  });
})(jQuery);