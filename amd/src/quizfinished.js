define(['jquery', 'core/log','core/modal_factory','core/str','core/modal_events','mod_minilesson/definitions' ], function($, log,ModalFactory, str, ModalEvents, def) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('Poodll Minilesson Finished page: initialising');

  return {

    strings: {},

    //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
     },

    init: function() {
        this.init_strings();
        this.register_events();

    },

    register_events: function(){
        var that = this;
        $('body').on('click','.btn_finished_attempt',function(e) {

            e.preventDefault();
            var buttonhref= $(this).data('href');

            //if its not a reattempt ... proceed
            if($(this).data('action')!=='reattempt') {
                window.location.href = buttonhref;
                return;
            }

            //if its a reattempt, confirm and proceed
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: that.strings.reattempttitle,
                body: that.strings.reattemptbody
            })
            .then(function(modal) {
                modal.setSaveButtonText(that.strings.reattempt);
                var root = modal.getRoot();
                root.on(ModalEvents.save, function() {
                    window.location.href = buttonhref;
                });
                modal.show();
            });
      }
        );
    },

    init_strings: function(){
        var that = this;
        // set up strings
        str.get_strings([
            {"key": "reattempttitle",       "component": 'mod_minilesson'},
            {"key": "reattemptbody",           "component": 'mod_minilesson'},
            {"key": "reattempt",           "component": 'mod_minilesson'}

        ]).done(function(s) {
            var i = 0;
            that.strings.reattempttitle = s[i++];
            that.strings.reattemptbody = s[i++];
            that.strings.reattempt = s[i++];
        });
    }
  }
});