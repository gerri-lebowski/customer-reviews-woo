(function($){
  function initStars($root){
    var $container = $root.find('.cpr-rating-input');
    if(!$container.length) return;
    var $hidden = $root.find('#cpr_rating');
    var $stars = $container.find('.cpr-star');

    function paint(value){
      $stars.each(function(){
        var v = parseInt($(this).data('value'), 10) || 0;
        $(this).toggleClass('is-active', v <= value);
      });
    }

    // Hover effect
    $stars.on('mouseenter', function(){
      var v = parseInt($(this).data('value'), 10) || 0;
      $stars.removeClass('is-hover');
      $stars.each(function(){
        var vv = parseInt($(this).data('value'), 10) || 0;
        $(this).toggleClass('is-hover', vv <= v);
      });
    });
    $container.on('mouseleave', function(){
      $stars.removeClass('is-hover');
    });

    // Click to select
    $stars.on('click', function(){
      var v = parseInt($(this).data('value'), 10) || 0;
      $hidden.val(v);
      $stars.attr('aria-checked', 'false');
      $(this).attr('aria-checked', 'true');
      paint(v);
    });

    // Keyboard support
    $stars.on('keydown', function(e){
      var current = parseInt($hidden.val(), 10) || 0;
      if(e.key === 'ArrowRight' || e.key === 'ArrowUp'){
        e.preventDefault();
        current = Math.min(5, current + 1);
        $hidden.val(current);
        paint(current);
      } else if(e.key === 'ArrowLeft' || e.key === 'ArrowDown'){
        e.preventDefault();
        current = Math.max(1, current - 1);
        $hidden.val(current);
        paint(current);
      } else if(e.key === ' ' || e.key === 'Enter'){
        e.preventDefault();
        // confirm selection on focused star
        var v = parseInt($(this).data('value'), 10) || 0;
        $hidden.val(v);
        paint(v);
      }
    });

    // Initialize from existing value if any
    var initial = parseInt($hidden.val(), 10) || 0;
    paint(initial);
  }

  function initAccordion($root){
    var $toggle = $root.find('.cpr-accordion-toggle');
    var $content = $root.find('#cpr-accordion-content');
    if(!$toggle.length || !$content.length) return;

    function setOpen(open){
      $toggle.attr('aria-expanded', open ? 'true' : 'false');
      if(open){
        $content.removeAttr('hidden');
      } else {
        $content.attr('hidden', '');
      }
    }

    $toggle.on('click', function(){
      var expanded = $toggle.attr('aria-expanded') === 'true';
      setOpen(!expanded);
    });
  }

  $(function(){
    $('.cpr-review-form-wrapper').each(function(){
      var $wrapper = $(this);
      initAccordion($wrapper);
      initStars($wrapper);
    });
  });
})(jQuery);


