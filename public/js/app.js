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
        $body     = $('body');
        $document = $(document);


        /**
         ***************************************
         * Array Storage Driver
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
            templateDriver: new ArrayStorage,
            viewPath: window.siteUrl + 'views/',
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
            getTemplate: function(view, isPartial)
            {
                return this.templateDriver.get(view) || this.fetch(view, isPartial);
            },
            setTemplate: function(name, template)
            {
                return this.templateDriver.set(name, template);
            },
            fetch: function(view, isPartial)
            {
                var markup = $.ajax({
                    async: false,
                    url: this.viewPath + view.split('.').join('/') + '.mustache'
                }).responseText;

                return isPartial
                    ? markup
                    : this.setTemplate(view, Mustache.compile(markup));
            }
        });



        // this view will show an entire post
        // comment form, and comments
        var PostView = BaseView.extend({
            view: 'posts.show',
            events: {
                'submit form': function(e)
                {
                    e.preventDefault();
                    e.stopPropagation();

                    return this.addComment( $(e.target).serialize() );
                }
            },
            render: function()
            {
                var self = this;

                self.$el.html( this.template({
                    post: this.model.attributes
                }) );
            },
            addComment: function(formData)
            {
                var
                    self = this,
                    action = this.model.url() + '/comments'
                ;

                $.post(action, formData, function(comment, status, xhr)
                {
                    var view = new CommentViewPartial({
                        model: new bb.Model(comment)
                    });
                    
                    view.render().$el.prependTo(self.$('[data-role="comments"]'));
                    
                    self.$('input[type="text"], textarea').val('');
                    
                    self.model.attributes.comments.unshift(comment);
                    
                    notifications.add({
                        type: 'success',
                        message: 'Comment Added!'
                    });
                });

            }
        });




        // this will be used for rendering a single
        // comment
        var CommentViewPartial = BaseView.extend({
            view: 'comments._comment',
            render: function()
            {
                this.$el.html( this.template(this.model.attributes) );
                return this;
            }
        });


        var PostViewPartial = BaseView.extend({
            view: 'posts._post',
            render: function()
            {
                this.$el.html( this.template(this.model.attributes) );
                return this;
            }
        });




        var Blog = BaseView.extend({
            view: 'posts.index',
            initialize: function()
            {
                this.perPage  = this.options.perPage || 15;
                this.page     = this.options.page || 0;
                this.fetching = this.collection.fetch();

                if(this.options.infiniteScroll) this.enableInfiniteScroll();
            },
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
            paginate: function()
            {
                var posts;
                posts = this.collection.rest(this.perPage * this.page);
                posts = _.first(posts, this.perPage);
                this.page++;

                return posts;
            },
            addPosts: function()
            {
                var posts = this.paginate();

                for(var i=0; i<posts.length; i++)
                {
                    this.addOnePost( posts[i] );
                }
            },
            addOnePost: function(model)
            {
                var view = new PostViewPartial({
                    model: model
                });
                this.$el.append( view.render().el );
            },
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
            infiniteScroll: function()
            {
                if($window.scrollTop() >= $document.height() - $window.height() - 50)
                {
                    this.addPosts();
                }
            },
            enableInfiniteScroll: function()
            {
                var self = this;

                $window.on('scroll', function()
                {
                    self.infiniteScroll();
                });
            },
            disableInifiniteScroll: function()
            {
                $window.off('scroll');
            }
        });




        // the posts collection is configured to fetch
        // from our API, as well as use our PostModel
        var PostCollection = bb.Collection.extend({
            url: window.siteUrl + 'v1/posts'
        });





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



        var notifications = new bb.Collection();

        var NotificationView = BaseView.extend({
            el: $('#notifications'),
            view: 'layouts._notification',
            events: {

            },
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

        

        $.ajaxSetup({
            statusCode: {
                404: function()
                {
                    notifications.add({
                        type: 'error', //error, success, info, null
                        message: '404: Page Not Found'
                    });
                }
            }
        });



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

        bb.history.start({ pushState: true, root: window.siteUrl, silent: window.silentRouter });

    });//end document ready

}(this, jQuery, Backbone));