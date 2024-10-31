jQuery(document).ready(function($) {

    $('.push-monkey .push_monkey_woo_settings #push-monkey-abandoned-title, .push-monkey .push_monkey_woo_settings #push-monkey-abandoned-message')
    .unbind('keyup change input paste').bind('keyup change input paste',function(e){

      var $this = $(this);
      var val = $this.val();
      var valLength = val.length;
      var maxCount = $this.attr('maxlength');
      if(valLength > maxCount){

        $this.val($this.val().substring(0, maxCount));
      }
    }); 
})