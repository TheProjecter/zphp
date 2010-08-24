<html>
<head>
	<title>Hello World!</title>	
	
	<link href="{publish this="css/blitzer/jquery-ui-1.8.4.custom.css" assets="assets/jquery/"}" rel="stylesheet" type="text/css"/>
  	<script src="{publish this="js/jquery-1.4.2.min.js" assets="assets/jquery/"}"></script>
  	<script src="{publish this="js/jquery-ui-1.8.4.custom.min.js" assets="assets/jquery/"}"></script>  
</head>
<body>
Hello World from a basic Smarty view!

<a href="{publish this="test.txt"}">view auto-published asset</a>
<br />
<a href="{publish this="asset.txt" assets="assets/" }">view published asset from auto-published asset folder</a>


<h1>jQuery UI Example</h1>

<h2>Progressbar</h2>
	<style type="text/css">
		.ui-progressbar-value{bracket} background-image: url({publish this="images/pbar-ani.gif" assets="assets/jquery/"}); {/bracket}
	</style>	
	 {literal} 
	<script type="text/javascript">
	$(function() {
		$("#progressbar").progressbar({
			value: 59
		});
	});
	</script>
{/literal}


<div class="demo">

<div id="progressbar"></div>

</div><!-- End demo -->

<div class="demo-description">

<p>
This progressbar has an animated fill by setting the
<code>background-image</code>
on the
<code>.ui-progressbar-value</code>
element, using css.
</p>

</div><!-- End demo-description -->
</body>
</html>