(function($) {
    showToast = function(title,message,level="info") {

	if (level=="ok")
	{
		color='#f96868';
		mytitle="";
		icon="success";
	}
	else if (level=="warning")
	{
		color='#57c7d4';
		mytitle="Attention";
		icon="warning";
	}	
	else if (level=="error")
	{
		color='#f2a654';
		mytitle="Erreur";
		icon="error";
	}
	else
	{
		color='#46c35f';
		mytitle="";
		icon="info";
	}

	if (title!="")
	{
		mytitle=title
	}

    resetToastPosition();
    $.toast({
      heading: mytitle,
      text: message,
      showHideTransition: 'slide',
      icon: icon,
      loaderBg: color,
      position: 'top-right',
	  hideAfter: 5000   
    })
  };
  resetToastPosition = function() {
    $('.jq-toast-wrap').removeClass('bottom-left bottom-right top-left top-right mid-center'); // to remove previous position class
    $(".jq-toast-wrap").css({
      "top": "",
      "left": "",
      "bottom": "",
      "right": ""
    }); //to remove previous position style
  }

})(jQuery);