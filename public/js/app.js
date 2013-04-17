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


    });//end document ready

}(this, jQuery, Backbone));