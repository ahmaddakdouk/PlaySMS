<script src='{HTTP_PATH_THEMES}/common/jscss/jquery.easytabs.js' type='text/javascript'></script>

<script type='text/javascript'>
	$(document).ready( function() {
	$('#tab-container').easytabs();
});
</script>

<h2>{Welcome to playSMS}</h2>
<div id='tab-container' class='tab-container'>
	<ul class='tabs'>
		<li class='tab'><a href='#tabs-about'>{About playSMS}</a></li>
		<li class='tab'><a href='#tabs-changelog'>{Changelog}</a></li>
		<li class='tab'><a href='#tabs-faq'>{F.A.Q}</a></li>
		<li class='tab'><a href='#tabs-license'>{License}</a></li>
		<li class='tab'><a href='#tabs-webservices'>{Webservices}</a></li>
	</ul>
	<div id='tabs-about'>{READ_README}</div>
	<div id='tabs-changelog'>{READ_CHANGELOG}</div>
	<div id='tabs-faq'>{READ_FAQ}</div>
	<div id='tabs-license'>{READ_LICENSE}</div>
	<div id='tabs-webservices'>{READ_WEBSERVICES}</div>
</div>
