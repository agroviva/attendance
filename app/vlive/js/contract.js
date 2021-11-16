jQuery(function(){
	jQuery(".select_sec select").change(function(){
		if(this.value=='Password') {
			jQuery('.password_field').css('display','table-row')
		} else{
			jQuery('.password_field').css('display','none')
		}
	})
	jQuery(".not_defined input").click(function(){
		if (this.checked == true) {
			jQuery(".day").each(function(){
				jQuery(this).css("display","none");
			});
		} else {
			jQuery(".day").each(function(){
				jQuery(this).css("display","table-row");
			});
		}
	})
});

