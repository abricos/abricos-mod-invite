var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.UserInviteFormWidget = Y.Base.create('UserInviteFormWidget', SYS.AppWidget, [], {
        initializer: function(){
            this.publish('request');
            this.publish('response');
        },
        onInitAppWidget: function(err, appInstance){
            this.triggerHide('inviteForm');
        },
        search: function(){
            var data = this.toJSON();

            this.fire('request', {
                data: data
            });

            this.triggerHide('inviteForm');
            
            this.set('waiting', true);
            this.get('appInstance').userSearch(data, function(err, result){
                this.set('waiting', false);

                var rUS = result && result.userSearch ? result.userSearch : null,
                    eData = {
                        error: err,
                        result: rUS
                    };

                if (err || !rUS.isSetCode('OK')){
                } else {
                    var codes = rUS.getCodesIsSet();
                    this.triggerShow('inviteForm', codes);
                }
                this.fire('response', eData);
            }, this);
        },
        toJSON: function(){
            var tp = this.template;

            return {
                owner: this.get('owner').toJSON(),
                loginOrEmail: tp.getValue('loginOrEmail')
            };
        },
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'inviteForm'},
            owner: NS.ATTRIBUTE.owner,
        },
        CLICKS: {
            search: 'search',
        }
    });
};
