(function($) {
    'use strict';
})(jQuery);


function setLoadingButtonGenral(idButton,idForm,btnValue="Consultar") {
  const button = jQuery(idButton);
        

    const isValid = jQuery(idForm).valid();

    if(isValid){
      button.attr('disabled',true);
      button.attr('loading',1);
      jQuery(idForm).attr('disabled',true);
      button.html(`<span class="loader-general"></span>`);
  
  }
    
};

function setActiveButtonGenral(idButton,idForm,btnValue="Consultar") {
  console.log('activamos el formulario');
  const button = jQuery(idButton);
        
  const isLoading = button.attr('loading');
  button.attr('disabled',false);
  button.attr('loading',0);
  jQuery(idForm).attr('disabled',false);
  button.html(btnValue);
};
function recaptchaExpired() {
  jQuery("#hidden-grecaptcha").val("");
}
