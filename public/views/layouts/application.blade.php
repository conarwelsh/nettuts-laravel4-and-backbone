<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Laravel4 & Backbone | Nettuts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A single page blog built using Backbone.js, Laravel, and Twitter Bootstrap">
  <meta name="author" content="Conar Welsh">

  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
  <script src="{{ asset('js/html5shiv.js') }}"></script>
  <![endif]-->
</head>
<body>

  <div id="notifications">
  </div>

  <div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="brand" href="#">Nettuts Tutorial</a>
        <div class="nav-collapse collapse">
          <ul class="nav">
            <li class="active"><a href="#">Blog</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
  </div>

  <div class="container" data-role="main">
    {{ $content }}
  </div> <!-- /container -->

  <!-- Placed at the end of the document so the pages load faster -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> <!-- use Google CDN for jQuery to hopefully get a cached copy -->
  <script src="{{ asset('node_modules/underscore/underscore-min.js') }}"></script>
  <script src="{{ asset('node_modules/backbone/backbone-min.js') }}"></script>
  <script src="{{ asset('node_modules/mustache/mustache.js') }}"></script>
  <script src="{{ asset('js/bootstrap.min.js') }}"></script>
  <script>
    var fullUrl = '{{ asset('') }}';
    window.siteUrl = fullUrl.replace(window.location.origin, '');
   </script>
  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>