(function ( $ ) {
	"use strict";

	$(function () {

		// Place your public-facing JavaScript here
		$(".jsEnerg1zerEmail").each(function()
		{
			var $this = $(this);
			var username = $this.data("username");
			var domain = $this.data("domain");
			var mailto = "mailto:"+username+"@"+domain;
			$this.attr("href", mailto);
		});
			

	});

}(jQuery));