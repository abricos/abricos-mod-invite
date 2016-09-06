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

        },
        search: function(){
            var tp = this.template,
                data = this.toJSON();

            this.fire('request', {
                data: data
            });

            tp.hide('findEmailNotValid,findNotInvite,findUserNotFound');

            this.set('waiting', true);
            this.get('appInstance').userSearch(data, function(err, result){
                this.set('waiting', false);

                if (err){
                    return;
                }

                var sr = result.userSearch;
                if (sr.isNotValid){
                    return tp.show('findEmailNotValid');
                }

                if (sr.isNotInvite){
                    return tp.show('findNotInvite');
                }

                if (sr.userid === 0){
                    tp.setHTML('email', email);
                    tp.show('findUserNotFound');
                }

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
            callbackContext: {value: null},
            onLoadCallback: {value: null}
        },
        CLICKS: {
            search: 'search',
        }
    });
};
