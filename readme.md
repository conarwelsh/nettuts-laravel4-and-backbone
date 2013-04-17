## What we are going to build:

- Build a JSON API using Laravel 4
- Use Composer for all server-side dependencies
- Build a single page app using Backbone.js
- Use NPM to manage all client-side dependencies

> Note: This tutorial assumes that you have both Composer and NPM installed.  Installations for both are very simple, if you do not already have them installed, follow the directions on their sites to do so now... I'll wait...




## Part 1: Architecture

### Git

Let's start by creating a git repository to work in.  For your reference this entire repo will be made publically available.

	mkdir project && cd project
	git init

### Laravel 4 Install

Laravel 4 uses Composer to install all of its dependencies, but first we will need an application structure to install into.  The develop branch on Laravel's Github repository is the home for this application structure.  However, since Laravel 4 is currently still in beta, we need to be prepared for this structure to change at any time.  By adding Laravel as a remote repository, we can pull in these changes whenever we need to.

	git remote add laravel https://github.com/laravel/laravel
	git fetch laravel
	git merge laravel/develop
	git add . && git commit -am "commit the laravel application structure"