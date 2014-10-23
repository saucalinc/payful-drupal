(function($){
	$(function(){
		var titles = $("#uc-payment-method-settings-form .settings-sub-title");
		/*var navBar = $("<ul/>", {
			"class": "subsubsub"
		}).insertBefore(titles.first());

		$('<br class="clear">').insertAfter(navBar); */

		var divs = $();

		titles.each(function(){
			var thisTitle = $(this);
			var items = thisTitle.nextUntil(".settings-sub-title, .form-actions").not("script");
			var div = $("<div/>", {
				"class": "payful-service"
			}).insertBefore(thisTitle).append(thisTitle).append(items).data("title", thisTitle.text());

			divs = divs.add(div);

			/*var thisLi = $("<li/>").appendTo(navBar);
			var link = $("<a/>", {
				"href": "#"
			}).html(thisTitle.text()).on("click", function(e){
				e.preventDefault();
				div.show().siblings('.payful-service').hide();
				thisLi.find("a").addClass('current').end().siblings().find("a").removeClass('current');
			}).appendTo(thisLi);

			div.data("linkItem", link);*/

			thisTitle.detach();
		});
		/*var items = navBar.children("li");
		items.not(items.last()).append(" | ");
		items.first().find("a").trigger("click");*/

		$("[name='uc_payful_merchant_service']").bind("change", function(){
			var thisSelect = $(this);
			var optSelected = $(this).find("option").filter(function(){
				return $(this).get(0).selected;
			});
			optSelected = optSelected.html();
			divs.filter(function(){
				return $(this).data("title") == optSelected;
			}).first().show().siblings('.payful-service').hide();
		}).trigger("change");
	});
})(jQuery)