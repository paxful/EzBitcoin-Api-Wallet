<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>EasyBitz API server</title>
	<style>
		@import url(//fonts.googleapis.com/css?family=Lato:700);

		body {
			margin:0;
			font-family:'Lato', sans-serif;
			text-align:center;
			color: #999;
		}

		.welcome {
			width: 300px;
			height: 200px;
			position: absolute;
			left: 50%;
			top: 50%;
			margin-left: -150px;
			margin-top: -100px;
		}

		a, a:visited {
			text-decoration:none;
		}

		h1 {
			font-size: 32px;
			margin: 16px 0 0 0;
		}
	</style>
</head>
<body>
	<div class="welcome">
		<a href="http://easybitz.com" title="Easybitz">
			<img src="data:image/png;base64,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"/>
		</a>
		<h1>You have arrived.</h1>
	</div>
</body>
</html>
