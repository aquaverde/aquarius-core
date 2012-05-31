$(document).ready(function() {
	// piped navig
	$("ul.language li:not(:last),ul.metaNav li:not(:last)").after('<li>&nbsp;|&nbsp;</li>');
	// mainnav
	var $li = $("ul.mainNav li").not("ul.subNav li");
	var liWidth = Math.floor(1000/($li.length));
	$li.children("a").width(liWidth-31).parent(":last").children().css({'border-right':'1px #d6e542 solid'});
	var navWidth = liWidth*($li.length);
	if (navWidth < 1000) $li.last().children("a").width(($li.last().children("a").width())+(1000-navWidth));
	$li.hoverIntent({ 
		over: function() {
			$(this).children("ul").stop(true,true).slideDown(500,'easeInOutBack');
		},out: function() { 
			$(this).children("ul").stop(true,true).slideUp(500,'easeOutSine');
		}
	});
	$("ul.subNav").each(function() {
		$(this).width($(this).parent().width()-1).find("a").width($(this).parent().width()-41);							 
		$(this).find("li:first a").css({'padding-top':20});							 
		$(this).find("li:last a").css({'padding-bottom':15});							 
	});
	// coda slider
	$('.imgSlider').codaSlider();
	// arrows
	$(".arrow").hover(function() {
		$(this).stop().animate({'width':'40'},300,'easeInOutBack');
	},function() {
		$(this).stop().animate({'width':'25'},300,'easeInOutBack');
	});
	// mainTopics
	var $topic = $("div.mainTopics div");
	$topic.width(Math.floor((740/($topic).length)-58));
	$(window).load(function() {
		var topicHeight = 0;
		$topic.each(function(i) {
			var thisHeight = $(this).height();
			if (thisHeight > topicHeight) topicHeight = thisHeight;		
		});
		$topic.height(topicHeight).last().css({'margin':'0'});
	});
	// news
	$("ul.news li:nth-child(2n)").css({'background':'none'});
});