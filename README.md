## Laravel 4 & Backbone

For this tutorial, we are going to be building a single page app using Laravel 4 and Backbone.js.  Both frameworks make it very easy to use a template engine other than their respective default, so we are going to use Mustache, which is an engine that is common to both.  By using the same templating language on both sides of our application, we will be able to share our views betweem them, saving us from having to repeat our work multiple times.

Our Backbone app will be powered by a Laravel 4 JSON API which we will develop together.  Laravel 4 comes with some new features that make the development of this API very easy.  I will show you a few tricks along the way to allow you to stay a bit more organized.

All of our dependencies will be managed by Package Managers, there will be no manual downloading or updating of libraries for this application!  In addition, I will be showing you how to leverage a little extra power from some of our dependencies.

For this project we will be using:

- [Laravel 4](http://four.laravel.com): A great PHP framework
- [Mustache.php](https://github.com/bobthecow/mustache.php): The PHP rendering engine for [Mustache](http://mustache.github.io/)
- [Mustache.js](https://github.com/janl/mustache.js/): The Javascript rendering engine for [Mustache](http://mustache.github.io/)
- [Jeffrey Way's Generators for Laravel 4](https://github.com/JeffreyWay/Laravel-4-Generators): Improve our workflow by generating some boilerplate code for us
- [Twitter Bootstrap](http://twitter.github.io/bootstrap/): A front end library to aid in our styling.
- [PHPUnit](https://github.com/sebastianbergmann/phpunit/): A PHP testing suite
- [Mockery](https://github.com/padraic/mockery): For mocking PHP Objects while testing
- [Backbone.js](http://backbonejs.org/): A Javascript MVC for our single page app
- [Underscore.js](http://underscorejs.org/): A dependency of Backbone, and a great little toolkit of functions.

To complete this tutorial, you will need the following items installed:

- [Composer](http://getcomposer.org/):  You can download this from the homepage, I recommend the global install instructions located [here](http://getcomposer.org/doc/00-intro.md#globally)
- [Node + NPM](http://nodejs.org/): the installer on the homepage will install both items
- [LESS](http://lesscss.org/) Compiler:  If you are on a Mac, I recommend CodeKit. However regardless of your operating system, or if you do not feel like paying for CodeKit, you can just install the LESS Compiler for Node.js by typing `npm install -g less` at the command prompt.


## Part 1: The Base Architecture

First thing first... we need to get our application setup before we can begin adding our business logic to it.  We will do a basic setup of Laravel 4, and the get all of our dependencies installed using our Package Managers.

### Git

Let's start by creating a git repository to work in.  For your reference this entire repo will be made publicly available at [https://github.com/conarwelsh/nettuts-laravel4-and-backbone](https://github.com/conarwelsh/nettuts-laravel4-and-backbone).

	mkdir project && cd project
	git init

### Laravel 4 Install

Laravel 4 uses Composer to install all of its dependencies, but first we will need an application structure to install into.  The master branch on [Laravel's Github repository](https://github.com/laravel/laravel) is the home for this application structure.  However, we need to be prepared for this structure to change at any time.  By adding Laravel as a remote repository, we can pull in these changes whenever we need to.

	git remote add laravel https://github.com/laravel/laravel
	git fetch laravel
	git merge laravel/master
	git add . && git commit -am "commit the laravel application structure"

Now we have the application structure, but all of the library files that Laravel needs are not yet installed.  You will notice at the root of our application there is a file called `composer.json`.  This is the file that will keep track of all the dependencies that our Laravel application requires.  Before we tell Composer to download and install them, let's first add a few more dependencies that we are going to need.  We will be adding:

- [Jeffrey Way's Generators](https://packagist.org/packages/way/generators):  Some very useful commands to greatly improve our workflow by automatically generating file stubs for us.
- [Laravel 4 Mustache](https://packagist.org/packages/conarwelsh/mustache-l4):  This will allow us to seemlessly use Mustache.php in our Laravel project, just as we would Blade.
- [Twitter Bootstrap](https://packagist.org/packages/twitter/bootstrap):  We will use the LESS files from this project to speed up our front-end development.
- [PHPUnit](https://packagist.org/packages/phpunit/phpunit):  We will be doing some TDD for our JSON API, PHPUnit will be our testing engine
- [Mockery](https://packagist.org/packages/mockery/mockery):  Mockery will help us "mock" objects during our testing

PHPUnit and Mockery are only required in our development environment, so we will specify that in our composer.json file.


***

**composer.json**

```json
{
    "require": {
        "laravel/framework": "4.0.*",
        "way/generators": "dev-master",
        "twitter/bootstrap": "dev-master",
        "conarwelsh/mustache-l4": "dev-master"
    },
    "require-dev": {
        "phpunit/phpunit": "3.7.*",
        "mockery/mockery": "0.7.*"
    },
    "autoload": {
        "classmap": [
            "app/commands",
            "app/controllers",
            "app/models",
            "app/database/migrations",
            "app/database/seeds",
            "app/tests/TestCase.php"
        ]
    },
    "scripts": {
        "post-update-cmd": "php artisan optimize"
    },
    "minimum-stability": "dev"
}
```

Now we just need to tell Composer to do all of our leg work!  Notice the --dev switch, we are telling composer that we are in our development evironment, and that it should also install all of our dependencies listed in `"require-dev"`.

	composer install --dev

After that finishes installing, we will need to inform Laravel of a few of our dependencies.  Laravel uses "service providers" for this purpose.  These service providers basically just tell Laravel how their code is going to interact with the application, and run any necessary setup procedures.  Open up `app/config/app.php` and add the following 2 items to the "providers" array.  Not all packages require this, only those that will enhance or change the functionality of Laravel.

***

**app/config/app.php**

```php
...

'Way\Generators\GeneratorsServiceProvider',
'Conarwelsh\MustacheL4\MustacheL4ServiceProvider',

...
```

Lastly, we just need to do some generic application tweaks to complete our Laravel install.  Let's open up `bootstrap/start.php` and tell Laravel our machine name so that it can determine what environment it is in.

***

**bootstrap/start.php**

```php
/*
|--------------------------------------------------------------------------
| Detect The Application Environment
|--------------------------------------------------------------------------
|
| Laravel takes a dead simple approach to your application environments
| so you can just specify a machine name or HTTP host that matches a
| given environment, then we will automatically detect it for you.
|
*/

$env = $app->detectEnvironment(array(

	'local' => array('your-machine-name'),

));
```

Replace "your-machine-name" with whatever the hostname for your machine is.  If you are unsure of what your exact machine name is, you can just type `hostname` at the command prompt (on Mac or Linux), whatever it prints is the value that belongs in this setting.

We want our views to be able to be served to our client from a web request.  Currently, our views are stored outside our `public` folder, which would mean that they are *not* publicly accessible.  Luckily Laravel makes it very easy to move, or add other view folders.  Open up `app/config/view.php` and change the `paths` setting to point to our public folder.  This setting works like the PHP native include path, it will check in each folder until it finds a matching view file, so feel free to add several here:

***

**app/config/view.php**

```php
'paths' => array(__DIR__.'/../../public/views'),
```

Next you will need to configure your database.  Open up `app/config/database.php` and add in your database settings.

> Note: It is recommended to use `127.0.0.1` instead of `localhost`.  You get a bit of a performance boost on most systems, and with some system configurations, `localhost` will not even connect properly.

Finally, you just need to make sure that your storage folder can be written to.

	chmod -R 755 app/storage

Laravel is now installed, with all of its dependencies, as well as our own dependencies.  Now let's setup our Backbone install!

Just like our `composer.json` installed all of our server-side dependencies, we will create a `package.json` in our *public folder* to install all of our client-side dependencies.

For our client-side dependencies we will use:

- [Underscore.js](https://npmjs.org/package/underscore): This is a dependency of Backbone.js, and a handy toolbelt of functions.
- [Backbone.js](https://npmjs.org/package/backbone): This is our client-side MVC that we will use to build out our application.
- [Mustache.js](https://npmjs.org/package/mustache): The Javascript version of our templating library, by using the same templating language both on the client and the server, we can share views, as opposed to duplicating logic.

***

**public/package.json**

```json
{
    "name": "nettuts-laravel4-and-backbone",
    "version": "0.0.1",
    "private": true,
    "dependencies": {
        "underscore": "*",
        "backbone": "*",
        "mustache": "*"
    }
}
```

Now just switch into your public folder, and run `npm install`.  After that completes lets switch back to our application root so we are prepared for the rest of our commands.

	cd public
	npm install
	cd ..

Package managers save us from a ton of work, should you want to update any of these libraries, all you have to do is run `npm update` or `composer update`.  Also, should you want to lock any of these libraries in at a specific version, all you have to do is specify the version number, and the package manager will handle the rest.

To wrap up our setup process we will just add in all of the basic project files and folders that we will need, and then test that it all works OK.

We will need to add the following folders:

- public/views
- public/views/layouts
- public/js
- public/css

And the following files:

- public/css/styles.less
- public/js/app.js
- public/views/app.mustache

the one-liner:

	mkdir public/views public/views/layouts public/js public/css && touch public/css/styles.less public/js/app.js public/views/app.mustache

Twitter Bootstrap also has 2 Javascript dependencies that we will need, so let's just copy them from the vendor folder into our public folder.

- **html5shiv.js**: allows us to use HTML5 elements without fear of older browsers not supporting them
- **bootstrap.min.js**: the supporting Javascript libraries for Twitter Bootstrap

```
cp vendor/twitter/bootstrap/docs/assets/js/html5shiv.js public/js/html5shiv.js
cp vendor/twitter/bootstrap/docs/assets/js/bootstrap.min.js public/js/bootstrap.min.js
```

For our layout file, Twitter Bootstrap also provides us with some nice starter templates to work with, so let's copy one into our layouts folder for a head start.

	cp vendor/twitter/bootstrap/docs/examples/starter-template.html public/views/layouts/application.blade.php

> Notice that I am using a blade extension here, this could just as easily be a mustache template, but I wanted to show you how easy it is to mix the templating engines.  Since our layout will be rendered on page load, and will not need to be re-rendered by the client, we are safe to use PHP here exclusively.  If for some reason you found yourself needing to render this file on the client-side, you would want to switch this file to use the Mustache template engine instead.

Now that we have all of our basic files in place, let's add some starter content that we can use to test that everything is working as we would expect.  I am providing you with some basic stubs to get you started.  

***

**public/css/styles.less**

We will just import the Twitter Bootstrap files from the vendor directory as opposed to copying them.  This allows us to update Twitter Bootstrap with nothing but a `composer update`.  

We declare our variables at the end of the file, the LESS compiler will figure out the value of all of its variables before parsing the LESS into CSS.  This means that by re-defining a Twitter Bootstrap variable at the end of the file, the value will actually change for all of the files included, allowing us to do simple overrides without modifying the Twitter Bootstrap core files.

```css
/**
 * Import Twitter Bootstrap Base File
 ******************************************************************************************
 */
@import "../../vendor/twitter/bootstrap/less/bootstrap";


/**
 * Define App Styles
 * Do this before the responsive include, so that it can override properly as needed.
 ******************************************************************************************
 */
body {
    padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
}

/* this will set the position of our alerts */
#notifications {
	width: 300px;
	position: fixed;
	top: 50px;
	left: 50%;
	margin-left: -150px;
	text-align: center;
}

/**
 * Import Bootstrap's Responsive Overrides
 * now we allow bootstrap to set the overrides for a responsive layout
 ******************************************************************************************
 */
@import "../../vendor/twitter/bootstrap/less/responsive";


/**
 * Define our variables last, any variable declared here will be used in the includes above
 * which means that we can override any of the variables used in the bootstrap files easily
 * without modifying any of the core bootstrap files
 ******************************************************************************************
 */

// Scaffolding
// -------------------------
@bodyBackground:        #f2f2f2;
@textColor:             #575757;

// Links
// -------------------------
@linkColor:             #41a096;

// Typography
// -------------------------
@sansFontFamily:        Arial, Helvetica, sans-serif;
```

***


**public/js/app.js**

We will wrap all of our code in an immediately-invoking-anonymous-function that passes in a few global objects.  We will then alias these global objects to something more useful to us.  Also we will cache a few jQuery objects inside the document ready function.

```js
//alias the global object
//alias jQuery so we can potentially use other libraries that utilize $
//alias Backbone to save us on some typing
(function(exports, $, bb){

    //document ready
    $(function(){

        /**
         ***************************************
         * Cached Globals
         ***************************************
         */
        var $window, $body, $document;

        $window   = $(window);
        $document = $(document);
        $body     = $('body');


    });//end document ready

}(this, jQuery, Backbone));
```


***

**public/views/layouts/application.blade.php**

Just a simple HTML layout file.  We are however using the `asset` helper from Laravel to aid us in creating paths to our assets.  It is good practice to use this type of helper, because if you ever happen to move your project into a sub-folder, all of your links will still work.

We made sure that we included all of our dependencies in this file, and also added the jQuery dependency.  I chose to request jQuery from the Google CDN, because chances are the visiting user of this site will already have a copy from that CDN cached in their browser, saving us from having to complete the HTTP request for it.

One important thing to note here is the way in which we are nesting our view.  Mustache does not have Block Sections like Blade does, so instead, the contents of the nested view will be made available under a variable with the name of the section.  I will point this out when we render this view from our route.

```html
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
    {{--since we are using mustache as the view, it does not have a concept of sections like blade has, so instead of using @yield here, our nested view will just be a variable that we can echo--}}

    {{ $content }}

  </div> <!-- /container -->

  <!-- Placed at the end of the document so the pages load faster -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> <!-- use Google CDN for jQuery to hopefully get a cached copy -->
  <script src="{{ asset('node_modules/underscore/underscore-min.js') }}"></script>
  <script src="{{ asset('node_modules/backbone/backbone-min.js') }}"></script>
  <script src="{{ asset('node_modules/mustache/mustache.js') }}"></script>
  <script src="{{ asset('js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>
```

***

**public/views/app.mustache**

Just a simple view that we will nest into our layout.

```html
<dl>
    <dt>Q. What did Biggie say when he watched inception?</dt>
    <dd>A. "It was all a dream!"</dd>
</dl>
```

***

**app/routes.php**

Laravel should have already provided you with a default route, all we are doing here is changing the name of the view which that route is going to render.  

Remember above I told you that the nested view was going to be available under a variable named whatever the parent section was?  Well when you nest a view, the first parameter to the function is the section name:

```php
View::make('view.path')->nest($sectionName, $nestedViewPath, $viewVariables);
```

In our `nest` command we call the section "content" that means if we echo `$content` from our layout, we will get the rendered contents of that view.  If we were to do `return View::make('layouts.application')->nest('foobar', 'app');` the our nested view would be available under a variable named `$foobar`.

```php
<?php

//backbone app route
Route::get('/', function()
{
    //change our view name to the view we created in a previous step
    //notice that we do not need to provide the .mustache extension
    return View::make('layouts.application')->nest('content', 'app');
});
```




With all of our basic files in place, we can test to ensure everything went OK.  Laravel 4 utilizes the new PHP web server to provide us with a great little development environment.  So long to the days of having a million virtual hosts setup on your development machine for every project that you work on!

> Note:: make sure that you compiled your LESS file first!

	php artisan serve

If you followed along correctly, you should be laughing hysterically at my horrible sense of humor, and all of our assets should be properly included into the page.  



























## Part 2: Laravel 4 JSON API

Now we will build the API that will power our Backbone application.  Laravel 4 makes this process a breeze.

### API Guidelines
First let's go over a few general guidelines to keep in mind while we build an API:

- Status Codes:  Responses should reply with proper status codes, fight the temptation to just place an `{ error: "this is an error message" }` in the body of your response.  Use the HTTP protocol to it's fullest!
		
		- 200: success
		- 201: resource created
		- 204: success, but no content to return
		- 400: request not fulfilled //validation error
		- 401: not authenticated
		- 403: refusal to respond //wrong credentials, do not have permission (un-owned resource)
		- 404: not found
		- 500: other error

- Resource Methods:  Even though controllers will be serving different resources, they should still have very similar behavior.  The more predictable your API is, the easier it is to implement and adopt.

		- index: return a collection of resources
		- show: return a single resource
		- create: return a form, this form should detail out the required fields, validation, and labels as best as possible.  As well as anything else needed to properly create a resource.  Even though this is a JSON API, it is very useful to return a form here.  Both a computer and a person can parse through this form, and very easily decipher which items are needed to fill out this form successsfully.  This is a very easy way to "document" the needs of your API.
		- store: store a new resource, and return with the proper status code: 201
		- edit: return a form filled with the current state of a resource, this form should detail out the required fields, validation, and labels as best as possible.  As well as anything else needed to properly edit a resource
		- update: update an existing resource, and return with the proper status code
		- delete: delete an existing resource, and return with the proper status code: 204


### Routing & Versioning

API's are designed to be around for a while. This is not like your website where you can just change its functionality at the drop of a dime.  If you have programs that use your API, they are not going to be happy with you if you change things around and their program breaks.  For this reason it is important that you use versioning.
We can always create a "version 2" with additional, or altered functionality, and allow our subscribing programs opt-in to these changes, rather than be forced.

Laravel provides us with route groups that are perfect for this, place the following code ABOVE our first route:

```php
<?php

//create a group of routes that will belong to APIv1
Route::group(array('prefix' => 'v1'), function()
{
	//... insert API routes here...
});
```



### Generate Resources

We are going to use Jeffrey Way's generators to generate our resources.  When we generate a resource it will create us the following items:

- Controller
- Model
- Views (index.blade.php, show.blade.php, create.blade.php, edit.blade.php)
- Migration
- Seeds

We are going to need only 2 resources for this app: a Post resource, and a Comment resource.

> Note: in a recent update to the generators, I have been receiving a permissions error due to the way my web servers are setup.  To remedy this problem, you must allow write permissions to the folder that the generators write the temp file to.
	
	sudo chmod -R 755 vendor/way/generators/src/Way/

Run the `generate:resource` command

	php artisan generate:resource post --fields="title:string, content:text, author_name:string"

	php artisan generate:resource comment --fields="content:text, author_name:string, post_id:integer"

You should now pause for a second to investigate all of the files that the generator created for us.




### Adjust the generated resources:

The `generate:resource` command saved us a lot of work, but due to our unique configuration, we are still going to need to make some modifications.

First of all, the generator placed the views it created in the `app/views` folder, so we need to move them to the `public/views` folder

	mv app/views/posts public/views/posts
	mv app/views/comments public/views/comments

*** 

**app/routes.php**

We decided that we wanted our API to be versioned, so we will need to move the routes the generator created for us into the version group.  We will also want to namespace our controllers with the corresponding version, so that we can have a different set of controllers for each version we build.  Also the comments resource needs to be nested under the posts resource.

```php
<?php

//create a group of routes that will belong to APIv1
Route::group(array('prefix' => 'v1'), function()
{
	//... insert API routes here...
	Route::resource('posts', 'V1\PostsController'); //notice the namespace
	Route::resource('posts.comments', 'V1\PostsCommentsController'); //notice the namespace, and the nesting
});

//backbone app route
Route::get('/', function()
{
    //change our view name to the view we created in a previous step
    //notice that we do not need to provide the .mustache extension
    return View::make('layouts.application')->nest('content', 'app');
});
```

Since we namespaced our controllers, we should move them into their own folder for organization, let's create a folder named `V1` and move our generated controllers into it.  Also, since we nested our comments controller under the posts controller, let's change the name of that controller to reflect the relationship.

	mkdir app/controllers/V1
	mv app/controllers/PostsController.php app/controllers/V1/
	mv app/controllers/CommentsController.php app/controllers/V1/PostsCommentsController.php

We will need to update the controller files to reflect our changes as well.  First of all, we need to namespace them, and since they are namespaced, any classes outside of that namespace will need to be manually imported with the `use` statement.

**app/controllers/PostsController.php**

```php
<?php
//use our new namespace
namespace V1;

//import classes that are not in this new namespace
use BaseController;

class PostsController extends BaseController {
```

**app/controllers/PostsCommentsController.php**

We also need to update our `CommentsController` with our new name: `PostsCommentsController`


```php
<?php
//use our new namespace
namespace V1;

//import classes that are not in this new namespace
use BaseController;

//rename our controller class
class PostsCommentsController extends BaseController {
```






### Repositories

By default, repositories are not part of Laravel.  Laravel is extremely flexible though, and makes it very easy to add them in.  We are going to use repositories to help us separate our logic for code re-usability, as well as for testing.  For now we will just get setup to use repositories, we will add in the proper logic later.

make a folder to store our repositories

	mkdir app/repositories

To let our auto-loader know about this new folder, we need to add it to our `composer.json` file.  Take a look at the updated "autoload" section of our file, and see that we added the repositories folder.

**composer.json**

```json
{
    "require": {
        "laravel/framework": "4.0.*",
        "way/generators": "dev-master",
        "twitter/bootstrap": "dev-master",
        "conarwelsh/mustache-l4": "dev-master"
    },
    "require-dev": {
        "phpunit/phpunit": "3.7.*",
        "mockery/mockery": "0.7.*"
    },
    "autoload": {
        "classmap": [
            "app/commands",
            "app/controllers",
            "app/models",
            "app/database/migrations",
            "app/database/seeds",
            "app/tests/TestCase.php",
            "app/repositories"
        ]
    },
    "scripts": {
        "post-update-cmd": "php artisan optimize"
    },
    "minimum-stability": "dev"
}
```







### Seeds

Database seeds are a useful tool, they provide us with an easy way to fill our database with some content.  The generators provided us with base files for seeding, we merely need to add in some actual seeds.

**app/database/seeds/PostsTableSeeder.php**

```php
<?php

class PostsTableSeeder extends Seeder {

    public function run()
    {
        $posts = array(
        	array(
                'title'       => 'Test Post',
                'content'     => 'Lorem ipsum Reprehenderit velit est irure in enim in magna aute occaecat qui velit ad.',
                'author_name' => 'Conar Welsh',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
            array(
                'title'       => 'Another Test Post',
                'content'     => 'Lorem ipsum Reprehenderit velit est irure in enim in magna aute occaecat qui velit ad.',
                'author_name' => 'Conar Welsh',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
        );

        // Uncomment the below to run the seeder
        DB::table('posts')->insert($posts);
    }

}
```

**app/database/seeds/CommentsTableSeeder.php**

```php
<?php

class CommentsTableSeeder extends Seeder {

    public function run()
    {
        $comments = array(
        	array(
                'content'     => 'Lorem ipsum Nisi dolore ut incididunt mollit tempor proident eu velit cillum dolore sed',
                'author_name' => 'Testy McTesterson',
                'post_id'     => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
            array(
                'content'     => 'Lorem ipsum Nisi dolore ut incididunt mollit tempor proident eu velit cillum dolore sed',
                'author_name' => 'Testy McTesterson',
                'post_id'     => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
            array(
                'content'     => 'Lorem ipsum Nisi dolore ut incididunt mollit tempor proident eu velit cillum dolore sed',
                'author_name' => 'Testy McTesterson',
                'post_id'     => 2,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
        );

        // Uncomment the below to run the seeder
        DB::table('comments')->insert($comments);
    }

}
```

Do not forget to run `composer dump-autoload` to let the Composer auto loader know about the new migration files!

	composer dump-autoload

Now we can run our migrations, and seed the database. Laravel provides us with a single command to do both:

	php artisan migrate --seed
















### Tests

Testing is one of those topics in development that no one can argue the importance of, however most people tend to ignore due to the learning curve.  However testing is really not that difficult, and can dramatically improve your application.  For this tutorial, we will setup some basic tests to help us ensure that our API is functioning properly.  We will build this API TDD style.  The rules of TDD state that we are not allowed to write any production code, until we have failing tests that warrants it.  However if I were to walk you through each test individually, this would prove to be a very long tutorial, so in the interest of brevity, I will just provide you with some tests to work from, and then the correct code to make them pass afterwards.

Before we write any tests though, we should first check the current test status of our application.  Since we installed PHPUnit via composer, we have the binaries available to us to use.  All you need to do is run:

	vendor/phpunit/phpunit/phpunit.php

Whoops! We already have a failure!  The test that is failing is actually an example test that comes pre-installed in our Laravel application structure, this tests against the default route that was also installed with the Laravel application structure.  Since we modified this route, we cannot be surprised that the test failed.  We can however,  just delete this test altogether as it does not apply to our application.

	rm app/tests/ExampleTest.php

If you run the PHPUnit command again, you will see that no tests were executed, and we have a clean slate for testing.

> Note: it is possible that if you have an older version of Jeffrey Way's generators that you will actually have a few tests in there that were created by those generators, and those tests are probably failing.  Just delete or overwrite those tests with the ones found below to proceed.

For this tutorial we will be testing our controllers and our repositories.  Let's create a few folders to store these tests in:

	mkdir app/tests/controllers app/tests/repositories





Now for the test files.  We are going to use Mockery to mock our repositories for our controller tests.  Mockery objects do as their name implies, they "mock" objects and report back to us on how those objects were interacted with.

In the case of the controller tests, we do not actually want the repositories to be called, after all these are the controller tests, not the repository tests.  So Mockery will set us up objects to use *instead* of our repositories, and let us know whether or not those objects were called as we expected them to.

In order to pull this off, we will have to tell the controllers to use our "mocked" objects as opposed to the real things.  We will just tell our Application to use a mocked instance next time a certain class is requested.  The command looks like this:

```php
App::instance($classToReplace, $instanceOfClassToReplaceWith);
```

The overall mocking process will go something like this:

- Create a new Mockery object, providing it the name of the class which it is to mock
- Tell the Mockery object which methods it should expect to receive, how many times it should receive that method, and what that method should return.
- Use the command shown above to tell our Application to use this new Mockery object instead of the default.
- Run the controller method like usual
- Assert the response



**app/tests/controllers/CommentsControllerTest.php**

```php
<?php

class CommentsControllerTest extends TestCase {

	/**
	 ************************************************************************
	 * Basic Route Tests
	 * notice that we can use our route() helper here!
	 ************************************************************************
	 */
	
	//test that GET /v1/posts/1/comments returns HTTP 200
	public function testIndex()
	{
		$response = $this->call('GET', route('v1.posts.comments.index', array(1)) );
		$this->assertTrue($response->isOk());
	}

	//test that GET /v1/posts/1/comments/1 returns HTTP 200
	public function testShow()
	{
		$response = $this->call('GET', route('v1.posts.comments.show', array(1,1)) );
		$this->assertTrue($response->isOk());
	}

	//test that GET /v1/posts/1/comments/create returns HTTP 200
	public function testCreate()
	{
		$response = $this->call('GET', route('v1.posts.comments.create', array(1)) );
		$this->assertTrue($response->isOk());
	}

	//test that GET /v1/posts/1/comments/1/edit returns HTTP 200
	public function testEdit()
	{
		$response = $this->call('GET', route('v1.posts.comments.edit', array(1,1)) );
		$this->assertTrue($response->isOk());
	}

	/**
	 *************************************************************************
	 * Tests to ensure that the controller calls the repo as we expect
	 * notice we are "Mocking" our repository
	 * 
	 * also notice that we do not really care about the data or interactions
	 * we merely care that the controller is doing what we are going to want
	 * it to do, which is reach out to our repository for more information
	 *************************************************************************
	 */
	
	//ensure that the index function calls our repository's "findAll" method
	public function testIndexShouldCallFindAllMethod()
	{
		//create our new Mockery object with a name of CommentRepositoryInterface
		$mock = Mockery::mock('CommentRepositoryInterface');

		//inform the Mockery object that the "findAll" method should be called on it once
		//and return a string value of "foo"
		$mock->shouldReceive('findAll')->once()->andReturn('foo');

		//inform our application that we have an instance that it should use
		//whenever the CommentRepositoryInterface is requested
		App::instance('CommentRepositoryInterface', $mock);

		//call our controller route
		$response = $this->call('GET', route('v1.posts.comments.index', array(1)));

		//assert that the response is a boolean value of true
		$this->assertTrue(!! $response->original);
	}

	//ensure that the show method calls our repository's "findById" method
	public function testShowShouldCallFindById()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.show', array(1,1)));
		$this->assertTrue(!! $response->original);
	}

	//ensure that our create method calls the "instance" method on the repository
	public function testCreateShouldCallInstanceMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('instance')->once()->andReturn(array());
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.create', array(1)));
		$this->assertViewHas('comment');
	}

	//ensure that the edit method calls our repository's "findById" method
	public function testEditShouldCallFindByIdMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn(array());
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.edit', array(1,1)));
		$this->assertViewHas('comment');
	}

	//ensure that the store method should call the repository's "store" method
	public function testStoreShouldCallStoreMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('store')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('POST', route('v1.posts.comments.store', array(1)));
		$this->assertTrue(!! $response->original);
	}

	//ensure that the update method should call the repository's "update" method
	public function testUpdateShouldCallUpdateMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('update')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('PUT', route('v1.posts.comments.update', array(1,1)));
		$this->assertTrue(!! $response->original);
	}

	//ensure that the destroy method should call the repositories "destroy" method
	public function testDestroyShouldCallDestroyMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('destroy')->once()->andReturn(true);
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('DELETE', route('v1.posts.comments.destroy', array(1,1)));
		$this->assertTrue( empty($response->original) );
	}


}
```

**app/tests/controllers/PostsControllerTest.php**

We will follow the exact same procedure for the `PostsController` tests

```php
<?php

class PostsControllerTest extends TestCase {

	/**
	 * Test Basic Route Responses
	 */
	public function testIndex()
	{
		$response = $this->call('GET', route('v1.posts.index'));
		$this->assertTrue($response->isOk());
	}

	public function testShow()
	{
		$response = $this->call('GET', route('v1.posts.show', array(1)));
		$this->assertTrue($response->isOk());
	}

	public function testCreate()
	{
		$response = $this->call('GET', route('v1.posts.create'));
		$this->assertTrue($response->isOk());
	}

	public function testEdit()
	{
		$response = $this->call('GET', route('v1.posts.edit', array(1)));
		$this->assertTrue($response->isOk());
	}

	/**
	 * Test that controller calls repo as we expect
	 */
	public function testIndexShouldCallFindAllMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('findAll')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.index'));
		$this->assertTrue(!! $response->original);
	}

	public function testShowShouldCallFindById()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.show', array(1)));
		$this->assertTrue(!! $response->original);
	}

	public function testCreateShouldCallInstanceMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('instance')->once()->andReturn(array());
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.create'));
		$this->assertViewHas('post');
	}

	public function testEditShouldCallFindByIdMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn(array());
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.edit', array(1)));
		$this->assertViewHas('post');
	}

	public function testStoreShouldCallStoreMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('store')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('POST', route('v1.posts.store'));
		$this->assertTrue(!! $response->original);
	}

	public function testUpdateShouldCallUpdateMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('update')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('PUT', route('v1.posts.update', array(1)));
		$this->assertTrue(!! $response->original);
	}

	public function testDestroyShouldCallDestroyMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('destroy')->once()->andReturn(true);
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('DELETE', route('v1.posts.destroy', array(1)));
		$this->assertTrue( empty($response->original) );
	}

}
```

***

**app/tests/repositories/EloquentCommentRepositoryTest.php**

Now for the repository tests.  In writing our controller tests, we pretty much already decided what most of the interface should look like for the repositories.  Our controllers needed the following methods:

- findById($id)
- findAll()
- instance($data)
- store($data)
- update($id, $data)
- destroy($id)

The only other method that we will want to add here, is a `validate` method.  This will mainly be a private method for the repository to ensure that the data is safe to store or update.

For these tests, we are also going to add a `setUp` method, which will allow us to run some code on our class, prior to the execution of *each* test.  Our `setUp` will be a very simple one, we will just make sure that any `setUp` methods defined in parent classes are also called using `parent::setUp()` and the simply add a class variable that stores an instance of our repository.

We will use the power of Laravel's IoC container again to get an instance of our repository.  The `App::make()` command will return an instance of the requested class, it may seem strange that we do not just do `$this->repo = new EloquentCommentRepository()`, but hold that thought, we will come back to it.  You probably noticed that we are asking for a class called `EloquentCommentRepository`, but in our controller tests above we were calling our repository `CommentRepositoryInterface`... put this thought on the back-burner as well... explainations for both are coming I promise!

```php
<?php

class EloquentCommentRepositoryTest extends TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->repo = App::make('EloquentCommentRepository');
	}

	public function testFindByIdReturnsModel()
	{
		$comment = $this->repo->findById(1,1);
		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testFindAllReturnsCollection()
	{
		$comments = $this->repo->findAll(1);
		$this->assertTrue($comments instanceof Illuminate\Database\Eloquent\Collection);
	}

	public function testValidatePasses()
	{
		$reply = $this->repo->validate(array(
			'post_id'     => 1,
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		));

		$this->assertTrue($reply);
	}

	public function testValidateFailsWithoutContent()
	{
		try {
			$reply = $this->repo->validate(array(
				'post_id'     => 1,
				'author_name' => 'Testy McTesterson'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testValidateFailsWithoutAuthorName()
	{
		try {
			$reply = $this->repo->validate(array(
				'post_id'     => 1,
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testValidateFailsWithoutPostId()
	{
		try {
			$reply = $this->repo->validate(array(
				'author_name' => 'Testy McTesterson',
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testStoreReturnsModel()
	{
		$comment_data = array(
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		);

		$comment = $this->repo->store(1, $comment_data);

		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($comment->content === $comment_data['content']);
		$this->assertTrue($comment->author_name === $comment_data['author_name']);
	}

	public function testUpdateSaves()
	{
		$comment_data = array(
			'content' => 'The Content Has Been Updated'
		);

		$comment = $this->repo->update(1, 1, $comment_data);

		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($comment->content === $comment_data['content']);
	}

	public function testDestroySaves()
	{
		$reply = $this->repo->destroy(1,1);
		$this->assertTrue($reply);

		try {
			$this->repo->findById(1,1);
		}
		catch(NotFoundException $expected)
		{
			return;
		}

		$this->fail('NotFoundException was not raised');
	}

	public function testInstanceReturnsModel()
	{
		$comment = $this->repo->instance();
		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testInstanceReturnsModelWithData()
	{
		$comment_data = array(
			'title' => 'Un-validated title'
		);

		$comment = $this->repo->instance($comment_data);
		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($comment->title === $comment_data['title']);
	}

}
```


**app/tests/repositories/EloquentPostRepositoryTest.php**

```php
<?php

class EloquentPostRepositoryTest extends TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->repo = App::make('EloquentPostRepository');
	}

	public function testFindByIdReturnsModel()
	{
		$post = $this->repo->findById(1);
		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testFindAllReturnsCollection()
	{
		$posts = $this->repo->findAll();
		$this->assertTrue($posts instanceof Illuminate\Database\Eloquent\Collection);
	}

	public function testValidatePasses()
	{
		$reply = $this->repo->validate(array(
			'title'       => 'This Should Pass',
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		));

		$this->assertTrue($reply);
	}

	public function testValidateFailsWithoutTitle()
	{
		try {
			$reply = $this->repo->validate(array(
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
				'author_name' => 'Testy McTesterson'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testValidateFailsWithoutAuthorName()
	{
		try {
			$reply = $this->repo->validate(array(
				'title'       => 'This Should Pass',
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testStoreReturnsModel()
	{
		$post_data = array(
			'title'       => 'This Should Pass',
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		);

		$post = $this->repo->store($post_data);

		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($post->title === $post_data['title']);
		$this->assertTrue($post->content === $post_data['content']);
		$this->assertTrue($post->author_name === $post_data['author_name']);
	}

	public function testUpdateSaves()
	{
		$post_data = array(
			'title' => 'The Title Has Been Updated'
		);

		$post = $this->repo->update(1, $post_data);

		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($post->title === $post_data['title']);
	}

	public function testDestroySaves()
	{
		$reply = $this->repo->destroy(1);
		$this->assertTrue($reply);

		try {
			$this->repo->findById(1);
		}
		catch(NotFoundException $expected)
		{
			return;
		}

		$this->fail('NotFoundException was not raised');
	}

	public function testInstanceReturnsModel()
	{
		$post = $this->repo->instance();
		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testInstanceReturnsModelWithData()
	{
		$post_data = array(
			'title' => 'Un-validated title'
		);

		$post = $this->repo->instance($post_data);
		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($post->title === $post_data['title']);
	}

}
```



Now that we have all of our tests in place, let's run PHPUnit again to watch them fail!

	vendor/phpunit/phpunit/phpunit.php

You should have a whole ton of failures, and in fact, the test suite probably did not even finish testing before it crashed.  This is OK, that means we have followed the rules of TDD and wrote failing tests before production code, albeit that tpically these tests would be written on at a time, and you would not move on to the next test until you had code that allowed the previous test to pass.  Your terminal should probably look something like mine at the moment:

![Screenshot](https://raw.github.com/conarwelsh/nettuts-laravel4-and-backbone/master/public/img/first-failed-screenshot.png)

What is actually failing is the assertViewHas method in our controller tests.  It is kind of intimidating to deal with this kind of an error when we have lumped together all of our tests without any production code at all.  This is why you should always write the tests one at a time, as you will find these errors in stride, as opposed to just a huge mess.  For now just follow my lead into the implementation of our code.












## Sidebar Discussion

Before we proceed with the implementations, let's break for a quick sidebar discussion on the responsibilities of an MVC.

From [The Gang of 4](http://www.amazon.com/Design-Patterns-Object-Oriented-Professional-Computing/dp/0201634988):  *"The Model is the application object, the View is its screen presentation, and the Controller defines the way the user interface reacts to user input."*

The point of using a structure like this is to remain encapsulated and flexible, allowing us to exchange and reuse components.  Let's go through each part of the MVC and talk about its reusability and flexibility:

#### View

I think most people would agree that a View is supposed to be a simple visual representation of data, and should not contain much logic.  In our case, as developers for the web, our View tends to be HTML or XML.

- **reusable**: always, almost anything can create a view
- **flexible**: not having any real logic in these layers makes this very flexible


#### Controller

If a Controller "defines the way the user interface reacts to user input", then it's responsibility should be to listen to user input (GET, POST, Headers, etc), and build out the current state of the application. In my opinion, a Controller should be very light, and should not contain more code than is required to accomplish the above.

- **reusable**:  We have to remember that our Controllers return an opinionated View, so we cannot ever call that Controller method in a practical way to use any of the logic inside it.  Therefor any logic placed in Controller methods, must be specific to that Controller method, if the logic is reusable, it should be placed elsewhere.
- **flexible**:  In most PHP MVC's, the Controller is tied directly to the route, which does not leave us very much flexibility.  Laravel fixes this issue by allowing us to declare routes that use a controller, so we can now swap out our controllers with different implementations if need be:

```php
Route::get('/', array(
	'uses' => 'SomeController@action'
));
```

#### Model

The Model is the "application object" in our definition from the Gang of Four.  This is a very generic definition.  In addition, we just decided to offload any logic that needs to be reusable from our Controller, and since Model is the only component left in our defined structure, its logical to assume that this is the new home for that logic.  However, I think the Model should not contain any logic like this.  In my opinion we should think of our "application object" in this case as an object that represents its place in the data-layer, whether that be a table, row, or collection entirely depends on state.  The model should contain not much more than getters and setters for data (including relationships).

- **reusable**: If we follow the above practice and make our Models be an object that represents its place in the database, this object remains very reusable.  Any part of our system can use this model, and by doing so gain complete and unopinionated access to the database.
- **flexible**:  Following the above practice, our Model is basically an implementation of an ORM, this allows us to be flexible, because we now have the power to change ORM's whenever we would like to just by adding a new Model.  We should probably have a pre-defined interface that our Model's must abide by, such as: all, find, create, update, delete.  Implementation of a new ORM would be as simple as ensuring that the previously mentioned interface was accomodated.

#### Repository

Just by carefully defining our MVC components, we orphaned all kinds of logic into no-man's land.  This is where Repositories come in to fill the void.  Repositories become the intermediary of the Controllers and Models.  A typical request would be something like this:

- Controller receives all user input, and passes to the repository
- Repository does any "pre-gathering" actions such as validation of data, authorization, authentication, etc.  If these "pre-gathering" actions are successful, then the request is passed to the Model for processing.
- The Model will process all of the data into the data-layer, and return the current state.
- Repository will handle any "post-gathering" routines and return the current state to the controller
- Controller will then create the appropriate view using the information provided by the repository.

Our Repository ends up as flexible and organized as we have made our Controllers and Models, allowing us to reuse this in most parts of our system, as well as being able to swap it out for another implementation if needed.

We have already seen an example of swapping out a repository for another implementation in the Controller tests above.  Instead of using our default Repository, we instead asked the IoC container to provide the controller with an instance of a Mockery object.  We have this same power for all of our components.

What we have accomplised here by adding another layer to our MVC, is a very organized, scalable, and testable system.  Let's start putting the pieces in place and getting our tests to pass.



## Controller Implementation

If you take a read through the controller tests, you will see that all we really care about is how the controller is interacting with the repository.  So let's see how light and simple that makes our controllers.

> Note: in TDD, the objective is to do no more work than is required to make your tests pass.  So we want to do the absolute bare minimum here.

**app/controllers/V1/PostsController.php**

```php
<?php
namespace V1;

use BaseController;
use PostRepositoryInterface;
use Input;
use View;

class PostsController extends BaseController {

	/**
	 * We will use Laravel's dependency injection to auto-magically
	 * "inject" our repository instance into our controller
	 */
	public function __construct(PostRepositoryInterface $posts)
	{
		$this->posts = $posts;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return $this->posts->findAll();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$post = $this->posts->instance();
		return View::make('posts._form', compact('post'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		return $this->posts->store( Input::all() );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		return $this->posts->findById($id);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$post = $this->posts->findById($id);
		return View::make('posts._form', compact('post'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		return $this->posts->update($id, Input::all());
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$this->posts->destroy($id);
		return '';
	}

}
```

**app/controllers/PostsCommentsController.php**

```php
<?php
namespace V1;

use BaseController;
use CommentRepositoryInterface;
use Input;
use View;

class PostsCommentsController extends BaseController {

	/**
	 * We will use Laravel's dependency injection to auto-magically
	 * "inject" our repository instance into our controller
	 */
	public function __construct(CommentRepositoryInterface $comments)
	{
		$this->comments = $comments;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($post_id)
	{
		return $this->comments->findAll($post_id);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($post_id)
	{
		$comment = $this->comments->instance(array(
			'post_id' => $post_id
		));

		return View::make('comments._form', compact('comment'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($post_id)
	{
		return $this->comments->store( $post_id, Input::all() );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($post_id, $id)
	{
		return $this->comments->findById($post_id, $id);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($post_id, $id)
	{
		$comment = $this->comments->findById($post_id, $id);

		return View::make('comments._form', compact('comment'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($post_id, $id)
	{
		return $this->comments->update($post_id, $id, Input::all());
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($post_id, $id)
	{
		$this->comments->destroy($post_id, $id);
		return '';
	}

}
```

It does not get much simpler than that, all the Controllers are doing is handing the input data to the repository, taking the response from that, and handing it to the View, the View in our case is merely JSON for most of our methods.  When we return an Eloquent Collection, or Eloquent Model from a Controller in Laravel 4, the object is parsed into JSON auto-magically, which makes our job very easy.

> Note: notice that we added a few more "use" statements to the top of the file to support the other classes that we are using.  Do not forget this when you are working within a namespace.

The only thing that is a bit tricky in this controller is the constructor.  Notice we are passing in a typed variable as a dependency for this Controller, yet there is no point that we have access to the instantiation of this controller to actually insert that class... welcome to dependency injection!  What we are actually doing here is hinting to our controller that we have a dependency needed to run this class, and what its class name is (or its IoC binding name).  Laravel uses `App::make()` to create its Controllers before calling them.  `App::make()` will try to resolve an item, by looking for any bindings that we may have declared, and/or using the auto-loader to provide an instance.  In addition it will also resolve any dependencies needed to instantiate that class for us, by more-or-less recursively calling `App::make()` on each of the dependencies.

The observant of you will notice that what we are trying to pass in as a dependency is an interface, and as you know, an interface cannot be instantiated.  This is where it gets cool, and we actually already did the same thing in our tests.  In our tests however we used `App::instance()` to provide an already created instance instead of the interface.  For our Controllers, we are actually going to tell Laravel that whenever an instance of `PostRepositoryInterface` is requested, to actually return an instance of `EloquentPostRepository`.

Open up your `app/routes.php` file and add the following to the top of the file
	
```php
App::bind('PostRepositoryInterface', 'EloquentPostRepository');
App::bind('CommentRepositoryInterface', 'EloquentCommentRepository');
```

After adding those lines, anytime `App::make()` asks for an instance of `PostRepositoryInterface`, instead it will create an instance of `EloquentPostRepository`, which is assumed to implement `PostRepositoryInterface`.  If you were to ever change your repository to instead use a different ORM than Eloquent, or maybe a file-based driver, all you have to do is change these 2 lines and you are good to go, your Controllers will still work as normal.  The Controllers actual dependency is any object that implements that interface, we can determine at run-time what that implementation actually is.

PostRepositoryInterface and CommentRepositoryInterface must actually exist, and the bindings must actually implement them.  So let's create them now:

**app/repositories/PostRepositoryInterface.php**

```php
<?php

interface PostRepositoryInterface {
	public function findById($id);
	public function findAll();
	public function paginate($limit = null);
	public function store($data);
	public function update($id, $data);
	public function destroy($id);
	public function validate($data);
	public function instance();
}
```

**app/repositories/CommentRepositoryInterface.php**

```php
<?php

interface CommentRepositoryInterface {
	public function findById($post_id, $id);
	public function findAll($post_id);
	public function store($post_id, $data);
	public function update($post_id, $id, $data);
	public function destroy($post_id, $id);
	public function validate($data);
	public function instance();
}
```

Now that we have our 2 interfaces built, we must provide implementations of these interfaces.  Let's build them now.

**app/repositories/EloquentPostRepository.php**

As the name of this implementation implies, we are relying on Eloquent, which we can call directly.  If you had other dependencies, remember that `App::make()` is being used to resolve this repository, so you can feel free to use the same constructor method we used with our Controllers to inject your dependencies.

```php
<?php

class EloquentPostRepository implements PostRepositoryInterface {

	public function findById($id)
	{
		$post = Post::with(array(
				'comments' => function($q)
				{
					$q->orderBy('created_at', 'desc');
				}
			))
			->where('id', $id)
			->first();

		if(!$post) throw new NotFoundException('Post Not Found');
		return $post;
	}

	public function findAll()
	{
		return Post::with(array(
				'comments' => function($q)
				{
					$q->orderBy('created_at', 'desc');
				}
			))
			->orderBy('created_at', 'desc')
			->get();
	}

	public function paginate($limit = null)
	{
		return Post::paginate($limit);
	}

	public function store($data)
	{
		$this->validate($data);
		return Post::create($data);
	}

	public function update($id, $data)
	{
		$post = $this->findById($id);
		$post->fill($data);
		$this->validate($post->toArray());
		$post->save();
		return $post;
	}

	public function destroy($id)
	{
		$post = $this->findById($id);
		$post->delete();
		return true;
	}

	public function validate($data)
	{
		$validator = Validator::make($data, Post::$rules);
		if($validator->fails()) throw new ValidationException($validator);
		return true;
	}

	public function instance($data = array())
	{
		return new Post($data);
	}

}
```


**app/repositories/EloquentCommentRepository.php**


```php
<?php

class EloquentCommentRepository implements CommentRepositoryInterface {

	public function findById($post_id, $id)
	{
		$comment = Comment::find($id);
		if(!$comment || $comment->post_id != $post_id) throw new NotFoundException('Comment Not Found');
		return $comment;
	}

	public function findAll($post_id)
	{
		return Comment::where('post_id', $post_id)
			->orderBy('created_at', 'desc')
			->get();
	}

	public function store($post_id, $data)
	{
		$data['post_id'] = $post_id;
		$this->validate($data);
		return Comment::create($data);
	}

	public function update($post_id, $id, $data)
	{
		$comment = $this->findById($post_id, $id);
		$comment->fill($data);
		$this->validate($comment->toArray());
		$comment->save();
		return $comment;
	}

	public function destroy($post_id, $id)
	{
		$comment = $this->findById($post_id, $id);
		$comment->delete();
		return true;
	}

	public function validate($data)
	{
		$validator = Validator::make($data, Comment::$rules);
		if($validator->fails()) throw new ValidationException($validator);
		return true;
	}

	public function instance($data = array())
	{
		return new Comment($data);
	}

}
```


If you take a look in our repositories, there are a few Exceptions that we are throwing, which are not native, nor do they belong to Laravel.  Those are custom Exceptions that we are using to simplify our code.  By using custom Exceptions, we are able to easily halt the progress of the application if certain conditions are met.  For instance if a post is not found, we can just toss a NotFoundException, and the application will handle it accordingly, but not by showing a 500 error as usual, instead we are going to setup custom error handlers.  You could alternatively use `App::abort(404)` or something along those lines, but I find that this method saves me many conditional statements and repeat code, as well as allowing me to adjust the implementation of error reporting in a single place very easily.


First let's define the custom Exceptions.  Create a file in your `app` folder called `errors.php`

	touch app/errors.php



**app/errors.php**

```php
<?php

class PermissionException extends Exception {
	
	public function __construct($message = null, $code = 403)
	{
		parent::__construct($message ?: 'Action not allowed', $code);
	}

}

class ValidationException extends Exception {

	protected $messages;

	/**
	 * We are adjusting this constructor to receive an instance 
	 * of the validator as opposed to a string to save us some typing
	 * @param Validator $validator failed validator object
	 */
	public function __construct($validator)
	{
		$this->messages = $validator->messages();
		parent::__construct($this->messages, 400);
	}

	public function getMessages()
	{
		return $this->messages;
	}

}

class NotFoundException extends Exception {

	public function __construct($message = null, $code = 404)
	{
		parent::__construct($message ?: 'Resource Not Found', $code);
	}

}
```



Very simple Exceptions, notice for the ValidationException, we can just pass it the failed validator instance, and it will handle the error messages accordingly!

Now we need to define our error handlers, that will be called when one of these Exceptions are thrown.  These are basically Event listeners, whenever one of these exceptions are thrown, it is treated as an Event and calls the appropriate function.  It is very simple to add logging, or any other error handling procedures here.

**app/filters.php**

```php
...

/**
 * General HttpException handler
 */
App::error( function(Symfony\Component\HttpKernel\Exception\HttpException $e, $code)
{
	$headers = $e->getHeaders();

	switch($code)
	{
		case 401:
			$default_message = 'Invalid API key';
			$headers['WWW-Authenticate'] = 'Basic realm="CRM REST API"';
		break;

		case 403:
			$default_message = 'Insufficient privileges to perform this action';
		break;

		case 404:
			$default_message = 'The requested resource was not found';
		break;

		default:
			$default_message = 'An error was encountered';
	}

	return Response::json(array(
		'error' => $e->getMessage() ?: $default_message
	), $code, $headers);
});

/**
 * Permission Exception Handler
 */
App::error(function(PermissionException $e, $code)
{
	return Response::json($e->getMessage(), $e->getCode());
});

/**
 * Validation Exception Handler
 */
App::error(function(ValidationException $e, $code)
{
	return Response::json($e->getMessages(), $code);
});

/**
 * Not Found Exception Handler
 */
App::error(function(NotFoundException $e)
{
	return Response::json($e->getMessage(), $e->getCode());
});
```



We must now let our auto-loader know about these new files.  So we must tell Composer where to check for them:


**composer.json**

Notice that we added the `"app/errors.php"` line.

```json
{
    "require": {
        "laravel/framework": "4.0.*",
        "way/generators": "dev-master",
        "twitter/bootstrap": "dev-master",
        "conarwelsh/mustache-l4": "dev-master"
    },
    "require-dev": {
        "phpunit/phpunit": "3.7.*",
        "mockery/mockery": "0.7.*"
    },
    "autoload": {
        "classmap": [
            "app/commands",
            "app/controllers",
            "app/models",
            "app/database/migrations",
            "app/database/seeds",
            "app/tests/TestCase.php",
            "app/repositories",
            "app/errors.php"
        ]
    },
    "scripts": {
        "post-update-cmd": "php artisan optimize"
    },
    "minimum-stability": "dev"
}
```


We must now tell Composer to actually check for these files, and include them in the auto-load registry.

	composer dump-autoload

Great, so we have completed our controllers and our repositories, the last 2 items in our MVRC that we have to take care of is the models and views, both of which are pretty straight forward.

**app/models/Post.php**

```php
<?php
/**
 * Represent a Post Item, or Collection
 */
class Post extends Eloquent {

	/**
	 * Items that are "fillable"
	 * meaning we can mass-assign them from the constructor
	 * or $post->fill()
	 * @var array
	 */
    protected $fillable = array(
    	'title', 'content', 'author_name'
    );

    /**
     * Validation Rules
     * this is just a place for us to store these, you could
     * alternatively place them in your repository
     * @var array
     */
    public static $rules = array(
    	'title'       => 'required',
		'author_name' => 'required'
    );

    /**
     * Define the relationship with the comments table
     * @return Collection collection of Comment Models
     */
    public function comments()
	{
		return $this->hasMany('Comment');
	}

}
```

**app/models/Comment.php**

```php
<?php
/**
 * Represent a Comment Item, or Collection
 */
class Comment extends Eloquent {

	/**
	 * Items that are "fillable"
	 * meaning we can mass-assign them from the constructor
	 * or $comment->fill()
	 * @var array
	 */
	protected $fillable = array(
		'post_id', 'content', 'author_name'
	);

	/**
     * Validation Rules
     * this is just a place for us to store these, you could
     * alternatively place them in your repository
     * @var array
     */
	public static $rules = array(
		'post_id'     => 'required|numeric',
		'content'     => 'required',
		'author_name' => 'required'
	);

	/**
     * Define the relationship with the posts table
     * @return Model parent Post model
     */
	public function post()
	{
		return $this->belongsTo('Post');
	}

}
```

As far as views are concerned, I am just going to mark up some simple bootstrap-friendly pages.  Remember to change each files extension to `.mustache` though, since our generator thought that we would be using `.blade.php`.  We are also going to create a few "partial" views, using the Rails convention of prefixing them with an `_` to signify a partial.

> Note: I skipped a few views, as we will not be using them in this tutorial.

**public/views/posts/index.mustache**
The index page view, we will just loop over all of our posts, showing the post partial for each.


```html
{{#posts}}
	{{> posts._post}}
{{/posts}}
```

**public/views/posts/show.mustache**

The view we will use to show an entire post and its comments

```html
<article>
	<h3>
		{{ post.title }} {{ post.id }}
		<small>{{ post.author_name }}</small>
	</h3>
	<div>
		{{ post.content }}
	</div>
</article>

<div>
	<h2>Add A Comment</h2>
	{{> comments._form }}

	<section data-role="comments">
		{{#post.comments}}
			<div>
				{{> comments._comment }}
			</div>
		{{/post.comments}}
	</section>
</div>
```

**public/views/posts/_post.mustache**

The partial used to show a post in a list, this will be used in our index view

```html
<article data-toggle="view" data-target="posts/{{ id }}">
	<h3>{{ title }} {{ id }}</h3>
	<cite>{{ author_name }} on {{ created_at }}</cite>
</article>
```

**public/views/posts/_form.mustache**

The form needed to create a post, we will use this from our API, but this could also be a useful view in an admin panel and other places, which is why we choose to make it a partial.

```html
{{#exists}}
	<form action="/v1/posts/{{ post.id }}" method="post">
		<input type="hidden" name="_method" value="PUT" />
{{/exists}}
{{^exists}}
	<form action="/v1/posts" method="post">
{{/exists}}
	
	<fieldset>

		<div class="control-group">
			<label class="control-label"></label>
			<div class="controls">
				<input type="text" name="title" value="{{ post.title }}" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label"></label>
			<div class="controls">
				<input type="text" name="author_name" value="{{ post.author_name }}" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label"></label>
			<div class="controls">
				<textarea name="content">{{ post.content }}"</textarea>
			</div>
		</div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="Save" />
		</div>

	</fieldset>
</form>
```

**public/views/comments/_comment.mustache**

Represent a single comment in a list of comments

```html
<h5>
	{{ author_name }}
	<small>{{ created_at }}</small>
</h5>
<div>
	{{ content }}
</div>
```

**public/views/comments/_form.mustache**

The form needed to create a comment, both used in the API and the Show Post view

```html
{{#exists}}
	<form class="form-horizontal" action="/v1/posts/{{ comment.post_id }}/{{ id }}" method="post">
		<input type="hidden" name="_method" value="PUT" />
{{/exists}}
{{^exists}}
	<form class="form-horizontal" action="/v1/posts/{{ comment.post_id }}" method="post">
{{/exists}}
	
	<fieldset>

		<div class="control-group">
			<label class="control-label">Author Name</label>
			<div class="controls">
				<input type="text" name="author_name" value="{{ comment.author_name }}" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Comment</label>
			<div class="controls">
				<textarea name="content">{{ comment.content }}</textarea>
			</div>
		</div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="Save" />
		</div>

	</fieldset>
</form>
```


**public/views/layouts/_notification.mustache**

A helper view partial to allow us to show a notification.

```html
<div class="alert alert-{{type}}">
	{{message}}
</div>
```




Great, we have all of our API components in place.  Let's run our unit tests to see where we are at!

	vendor/phpunit/phpunit/phpunit.php

Your first run of this test should pass with flying (green) colors.  However, if you were to run this test again, you will notice that it fails now with a handful of errors, and that is because our repository tests actually tested the database, and in doing so deleted some of the records our previous tests used to assert values.  This is an easy fix, all we have to do is tell our tests that they need to re-seed the database after each test.  In addition, we did not receive a noticable error for this, but we did not close Mockery after each test either, this is a requirement of Mockery that you can find in their docs.  So let's add both missing methods.

open up `app/tests/TestCase.php` and add the following 2 methods

```php
/**
 * setUp is called prior to each test
 */
public function setUp()
{
	parent::setUp();
	$this->seed();
}

/**
 * tearDown is called after each test
 * @return [type] [description]
 */
public function tearDown()
{
	Mockery::close();
}
```

This is great, we now said that at every "setUp", which is run before each test, to re-seed the database.  However we still have one problem, everytime you re-seed, it is only going to append new rows to the tables, our tests are looking for items with a row ID of 1, so we still have a few changes to make.  We just need to tell the database to truncate our tables when seeding:

**app/database/seeds/CommentsTableSeeder.php**

Before we insert the new rows, we will truncate the table, deleting all rows and resetting the auto-increment counter.

```php
<?php

class CommentsTableSeeder extends Seeder {

	public function run()
    {
        $comments = array(
            array(
                'content'     => 'Lorem ipsum Nisi dolore ut incididunt mollit tempor proident eu velit cillum dolore sed',
                'author_name' => 'Testy McTesterson',
                'post_id'     => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
            array(
                'content'     => 'Lorem ipsum Nisi dolore ut incididunt mollit tempor proident eu velit cillum dolore sed',
                'author_name' => 'Testy McTesterson',
                'post_id'     => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
            array(
                'content'     => 'Lorem ipsum Nisi dolore ut incididunt mollit tempor proident eu velit cillum dolore sed',
                'author_name' => 'Testy McTesterson',
                'post_id'     => 2,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
        );

        //truncate the comments table when we seed
        DB::table('comments')->truncate();
        DB::table('comments')->insert($comments);
    }

}
```


**app/database/seeds/PostsTableSeeder.php**

```php
<?php

class PostsTableSeeder extends Seeder {

	public function run()
    {
        $posts = array(
            array(
                'title'       => 'Test Post',
                'content'     => 'Lorem ipsum Reprehenderit velit est irure in enim in magna aute occaecat qui velit ad.',
                'author_name' => 'Conar Welsh',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ),
            array(
                'title'       => 'Another Test Post',
                'content'     => 'Lorem ipsum Reprehenderit velit est irure in enim in magna aute occaecat qui velit ad.',
                'author_name' => 'Conar Welsh',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            )
        );

        //truncate the posts table each time we seed
        DB::table('posts')->truncate();
        DB::table('posts')->insert($posts);
    }

}
```

	
Now you should be able to run the tests any number of times, and get passing tests each time!  That means we have fulfilled our TDD cycle and we are not allowed to write anymore production code for our API!!  Let's just commit our changes to our repo and move onto the Backbone application!

	git add . && git commit -am "built out the API and corresponding tests"


























## Backbone App

Now that we have completed all of the back-end work, we can move forward to creating a nice user interface to access all of that data. We will keep this part of the project a little bit on the simpler side, and I warn you that my approach can be considered an opinionated one. I have seen many people with so many different methods for structuring a Backbone application. My trials and errors have led me to my current method, if you do not agree with it, my hope is that it may inspire you to find your own!

We are going to use the Mustache templating engine instead of Underscore, this will allow is to share our views between the client and server!  The trick is in how you load the views, we are going to use AJAX in this tutorial, but it is just as easy to load them all into the main template, or precompile them.

### Router

First we will get our router going.  There are 2 parts to this, the Laravel router, and the Backbone router.

#### Laravel Router

There are 2 main approaches we can take here:

##### Approach #1: The catch-all

Remember I told you when you were adding the resource routes that it was important that you placed them ABOVE the app route?? The catch-all method is the reason for that statement. The overall goal of this method is to have any routes that have not found a match in Laravel be caught and sent to Backbone. To implement this method is easy:

**app/routes.php**

```php
// change your existing app route to this:
// we are basically just giving it an optional parameter of "anything"
Route::get('/{path?}', function($path = null)
{
    return View::make('app');
})
->where('path', '.*'); //regex to match anything (dots, slashes, letters, numbers, etc)
```

Now, every route other than our API routes will render our app view.

In addition, if you have a multi-page app (several single page apps), you can define several of these catch-alls:

```php
Route::get('someApp1{path?}', function($path = null)
{
    return View::make('app');
})
->where('path', '.*');

Route::get('anotherApp/{path?}', function($path = null)
{
    return View::make('app');
})
->where('path', '.*');

Route::get('athirdapp{path?}', function($path = null)
{
    return View::make('app');
})
->where('path', '.*');
```

> Note: Note: keep in mind the '/' before {path?}. If that slash is there, it will be required in the URL (with the exception of the index route), sometimes this is desired and sometimes not.




##### Approach #2:

Since our front and back end share views... wouldn't it be extremely easy to just define routes in both places? You can even do this *in addition* to the catch-all approach if you want.

The routes that we are going to end up defining for the app are simply:

	GET /
	GET /posts/:id

app/routes.php

```php
<?php

App::bind('PostRepositoryInterface', 'EloquentPostRepository');
App::bind('CommentRepositoryInterface', 'EloquentCommentRepository');





//create a group of routes that will belong to APIv1
Route::group(array('prefix' => 'v1'), function()
{
    Route::resource('posts', 'V1\PostsController');
    Route::resource('posts.comments', 'V1\PostsCommentsController');
});



/**
 * Method #1: use catch-all
 * optionally commented out while we use Method 2
 */
// change your existing app route to this:
// we are basically just giving it an optional parameter of "anything"
// Route::get('/{path?}', function($path = null)
// {
//     return View::make('layouts.application')->nest('content', 'app');
// })
// ->where('path', '.*'); //regex to match anything (dots, slashes, letters, numbers, etc)



/**
 * Method #2: define each route
 */
Route::get('/', function()
{
    $posts = App::make('PostRepositoryInterface')->paginate();
    return View::make('layouts.application')->nest('content', 'posts.index', array(
		'posts' => $posts
	));
});

Route::get('posts/{id}', function($id)
{
    $post = App::make('PostRepositoryInterface')->findById($id);
    return View::make('layouts.application')->nest('content', 'posts.show', array(
		'post' => $post
	));
});
```


Pretty cool huh?! Regardless of which method we use, or the combination of both, your Backbone router will end up mostly the same.

Notice that we are using our Repository again, this is yet another reason why Repositories are a useful addition to our framework.  We can now run almost all of the logic that the controller does, but without repeating hardly any of the code!

Keep in mind a few things while choosing which method to use, if you use the catch-all it will do just like the name implies... catch-ALL... this means there is no such thing as a 404 on your site anymore.  No matter the request, its landing on the app page (unless you manually toss an exception somewhere such as your repository). The inverse is, with defining each route... now you have 2 sets of routes to manage. Both methods have their ups and downs, but both are equally easy to deal with.







### Base View

One view to rule them all! This BaseView is the view that all of our other Views will inherit from. For our purposes this view has but one job... templating! In a larger app this view is a good place to put other shared logic.

We will simply extend `Backbone.View` and add a `template` function that will return our view from the cache if it exists, or get it via AJAX and place it in the cache.  We have to use synchronous AJAX due to the way that Mustache.js fetches partials, but since we are only retrieving these views if they are not cached we should not receive much of a performance hit here.

```js
/**
 ***************************************
 * Array Storage Driver
 * used to store our views
 ***************************************
 */
var ArrayStorage = function(){
    this.storage = {};
};
ArrayStorage.prototype.get = function(key)
{
    return this.storage[key];
};
ArrayStorage.prototype.set = function(key, val)
{
    return this.storage[key] = val;
};



/**
 ***************************************
 * Base View
 ***************************************
 */
var BaseView = bb.View.extend({
    
    /**
     * Set our storage driver
     */
    templateDriver: new ArrayStorage,

    /**
     * Set the base path for where our views are located
     */
    viewPath: '/views/',

    /**
     * Get the template, and apply the variables
     */
    template: function()
    {
        var view, data, template, self;

        switch(arguments.length)
        {
            case 1:
                view = this.view;
                data = arguments[0];
                break;
            case 2:
                view = arguments[0];
                data = arguments[1];
                break;
        }

        template = this.getTemplate(view, false);
        self = this;

        return template(data, function(partial)
        {
            return self.getTemplate(partial, true);
        });
    },

    /**
     * Facade that will help us abstract our storage engine,
     * should we ever want to swap to something like LocalStorage
     */
    getTemplate: function(view, isPartial)
    {
        return this.templateDriver.get(view) || this.fetch(view, isPartial);
    },

    /**
     * Facade that will help us abstract our storage engine,
     * should we ever want to swap to something like LocalStorage
     */
    setTemplate: function(name, template)
    {
        return this.templateDriver.set(name, template);
    },

    /**
     * Function to retrieve the template via ajax
     */
    fetch: function(view, isPartial)
    {
        var markup = $.ajax({
            async: false,

            //the URL of our template, we can optionally use dot notation
            url: this.viewPath + view.split('.').join('/') + '.mustache'
        }).responseText;

        return isPartial
            ? markup
            : this.setTemplate(view, Mustache.compile(markup));
    }
});
```


### PostView

The PostView renders a single blog post

```js
// this view will show an entire post
// comment form, and comments
var PostView = BaseView.extend({

	//the location of the template this view will use, we can use dot notation
    view: 'posts.show',

    //events this view should subscribe to
    events: {
        'submit form': function(e)
        {
            e.preventDefault();
            e.stopPropagation();

            return this.addComment( $(e.target).serialize() );
        }
    },

    //render our view into the defined `el`
    render: function()
    {
        var self = this;

        self.$el.html( this.template({
            post: this.model.attributes
        }) );
    },

    //add a comment for this post
    addComment: function(formData)
    {
        var
            self = this,

            //build our url
            action = this.model.url() + '/comments'
        ;

        //submit a post to our api
        $.post(action, formData, function(comment, status, xhr)
        {
        	//create a new comment partial
            var view = new CommentViewPartial({
            	//we are using a blank backbone model, since we done have any specific logic needed
                model: new bb.Model(comment)
            });
			
			//prepend the comment partial to the comments list
            view.render().$el.prependTo(self.$('[data-role="comments"]'));
			
			//reset the form            
            self.$('input[type="text"], textarea').val('');
            
            //prepend our new comment to the collection
            self.model.attributes.comments.unshift(comment);
			
			//send a notification that we successfully added the comment            
            notifications.add({
                type: 'success',
                message: 'Comment Added!'
            });
        });

    }
});
```




### Partial Views

We will need a few views to render partials.  We mainly just need to tell the view which template to use, and that it should extend our view that provides the method to fetch our template.

```js
// this will be used for rendering a single comment in a list
var CommentViewPartial = BaseView.extend({
	//define our template location
    view: 'comments._comment',
    render: function()
    {
        this.$el.html( this.template(this.model.attributes) );
        return this;
    }
});

//this view will be used for rendering a single post in a list
var PostViewPartial = BaseView.extend({
	//define our template location
    view: 'posts._post',
    render: function()
    {
        this.$el.html( this.template(this.model.attributes) );
        return this;
    }
});
```



### Blog View

This is our overall application view.  It contains our configuration logic, as well as handling the fetching of our PostCollection.  We also setup a cool little infinite scroll feature.  Notice how we are using jQuery promises to ensure that the fetching of our collection has completed prior to rendering the view.

```js
var Blog = BaseView.extend({
	//define our template location
    view: 'posts.index',

    //setup our app configuration
    initialize: function()
    {
        this.perPage  = this.options.perPage || 15;
        this.page     = this.options.page || 0;
        this.fetching = this.collection.fetch();

        if(this.options.infiniteScroll) this.enableInfiniteScroll();
    },

    //wait til the collection has been fetched, and render the view
    render: function()
    {
        var self = this;
        this.fetching.done(function()
        {
            self.$el.html('');
            self.addPosts();

            // var posts = this.paginate()
            
            // for(var i=0; i<posts.length; i++)
            // {
            //     posts[i] = posts[i].toJSON();
            // }

            // self.$el.html( self.template({
            //     posts: posts
            // }) );
            
            if(self.options.infiniteScroll) self.enableInfiniteScroll();
        });
    },

    //helper function to limit the amount of posts we show at a time
    paginate: function()
    {
        var posts;
        posts = this.collection.rest(this.perPage * this.page);
        posts = _.first(posts, this.perPage);
        this.page++;

        return posts;
    },

    //add the next set of posts to the view
    addPosts: function()
    {
        var posts = this.paginate();

        for(var i=0; i<posts.length; i++)
        {
            this.addOnePost( posts[i] );
        }
    },

    //helper function to add a single post to the view
    addOnePost: function(model)
    {
        var view = new PostViewPartial({
            model: model
        });
        this.$el.append( view.render().el );
    },

    //this function will show an entire post, we could alternative make this its own View
    //however I personally like having it available in the overall application view, as it 
    //makes it easier to manage the state
    showPost: function(id)
    {
        var self = this;

        this.disableInifiniteScroll();

        this.fetching.done(function()
        {
            var model = self.collection.get(id);

            if(!self.postView)
            {
                self.postView = new self.options.postView({
                    el: self.el
                });
            }
            self.postView.model = model;                   
            self.postView.render();
        });
    },

    //function to run during the onScroll event
    infiniteScroll: function()
    {
        if($window.scrollTop() >= $document.height() - $window.height() - 50)
        {
            this.addPosts();
        }
    },

    //listen for the onScoll event
    enableInfiniteScroll: function()
    {
        var self = this;

        $window.on('scroll', function()
        {
            self.infiniteScroll();
        });
    },

    //stop listening to the onScroll event
    disableInifiniteScroll: function()
    {
        $window.off('scroll');
    }
});
```



### PostCollection

Setup our PostCollection, we just need to tell the Collection the URL it should use to fetch its contents.

```js
// the posts collection is configured to fetch
// from our API, as well as use our PostModel
var PostCollection = bb.Collection.extend({
    url: '/v1/posts'
});
```



### Blog Router

Notice that we are not instantiating new instances of our views, we are merely telling them to render.  Our initialize functions are designed to only be ran once, we do not want them to run but once on page load.

```js
var BlogRouter = bb.Router.extend({
    routes: {
        "": "index",
        "posts/:id": "show"
    },
    initialize: function(options)
    {
        // i do this to avoid having to hardcode an instance of a view
        // when we instantiate the router we will pass in the view instance
        this.blog = options.blog;
    },
    index: function()
    {
        //reset the paginator
        this.blog.page = 0;

        //render the post list
        this.blog.render();
    },
    show: function(id)
    {
        //render the full-post view
        this.blog.showPost(id);
    }
});
```



### Notifications Collection

We are just going to setup a simple Collection to store user notifications

```js
var notifications = new bb.Collection();
```


### NotificationsView

This view will handle the displaying and hiding of user notifications.

```js
var NotificationView = BaseView.extend({
    el: $('#notifications'),
    view: 'layouts._notification',
    initialize: function()
    {
        this.listenTo(notifications, 'add', this.render);
    },
    render: function(notification)
    {
        var $message = $( this.template(notification.toJSON()) );
        this.$el.append($message);
        this.delayedHide($message);
    },
    delayedHide: function($message)
    {
        var timeout = setTimeout(function()
        {
            $message.fadeOut(function()
            {
                $message.remove();
            });
        }, 5*1000);

        var self = this;
        $message.hover(
            function()
            {
                timeout = clearTimeout(timeout);   
            },
            function()
            {
                self.delayedHide($message);
            }
        );
    }
});
var notificationView = new NotificationView();
```



### Error Handling

Since we used the custom exception handlers for our API.  It makes it very easy to handle any error our API may throw.  Very similar to the way we defined out event listeners for our API in the `app/filters.php` file, we will define event listeners for our app here.  Each code that could be thrown can just show a notification very easily!

```js
$.ajaxSetup({
    statusCode: {
        401: function()
        {
            notification.add({
                type: null, //error, success, info, null
                message: 'You do not have permission to do that'
            });
        },
        403: function()
        {
            notification.add({
                type: null, //error, success, info, null
                message: 'You do not have permission to do that'
            });
        },
        404: function()
        {
            notification.add({
                type: 'error', //error, success, info, null
                message: '404: Page Not Found'
            });
        },
        500: function()
        {
            notification.add({
                type: 'error', //error, success, info, null
                message: 'The server encountered an error'
            });
        }
    }
});
```



## Event Listeners

We will need a few global event listeners, to help us navigate through our app without refreshing the page.  We mainly just hijack the default behavior and call `Backbone.history.navigate()`.  Notice how on our first listener, we are specifying the selector to only match those that do not have a data attribute of `bypass`.  This will allow us to create links such as `<a href="/some/non-ajax/page" data-bypass="true">link</a>` that will force the page to refresh.  We could also go a step further here and check whether the link is a local one, as opposed to a link to another site.

```js
$document.on("click", "a[href]:not([data-bypass])", function(e){
    e.preventDefault();
    e.stopPropagation();

    var href = $(this).attr("href");
    bb.history.navigate(href, true);
});

$document.on("click", "[data-toggle='view']", function(e)
{
    e.preventDefault();
    e.stopPropagation();
    
    var
        self = $(this),
        href = self.attr('data-target') || self.attr('href')
    ;

    bb.history.navigate(href, true);
});
```





### Start The App

Now we just need to boot the app, passing in any config values that we need.  Notice the line that checks for the `silentRouter` global variable, this is kind of a hacky way to be able to use both back-end routing methods at the same time.  This allows us to define a variable in the view called `silentRouter` and set it to true, meaning that the router should not actually engage the backbone route, allowing our back end to handle the initial rendering of the page, and just wait for any needed updates or AJAX.

```js
var BlogApp = new Blog({
    el             : $('[data-role="main"]'),
    collection     : new PostCollection(),
    postView       : PostView,
    perPage        : 15,
    page           : 0,
    infiniteScroll : true
});

var router = new BlogRouter({
    blog: BlogApp
});

if (typeof window.silentRouter === 'undefined') window.silentRouter = true;

bb.history.start({ pushState: true, root: '/', silent: window.silentRouter });
```




## Conclusion

Notice that for the Backbone portion of our app, all we had to do was write some Javascript that knew how to interact with pre-existing portions of our application?  That is what I love about this method!  It may seem like we had a lot of steps to take to get to that portion of things, but really most of that work was just a foundation build-up.  Once we got that initial foundation in place, the actual application logic falls together very simply.

Try adding another feature to this blog, such as User listing and info.  The basic steps you would take would be something like this:

- Use the generator tool to create a new "User" resource.
- Make the necessary modifications to ensure that the UserController is in the V1 API group
- Create your Repository and setup the proper IoC bindings in `app/routes.php`
- Write your Controller tests one at a time using Mockery for the repository, following each test up with the proper implementation to make that test passes.
- Write your Repository tests one at a time, again, following each test up with the implementation
- Add in the new functionality to your Backbone App.  I suggest trying 2 different approaches to the location of the User views.  Decide for yourself which is the better implementation.
	- First place them in their own routes and Main view
	- Then try incorporating them into the overall BlogView

I hope this gave you some insight into creating a scalable single page app and API using Backbone.js and Laravel 4.  If you have any questions, please ask them in the comment section below!











