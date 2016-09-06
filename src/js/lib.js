var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'team', files: ['lib.js']},
        {name: '{C#MODNAME}', files: ['model.js']}
    ]
};
Component.entryPoint = function(NS){

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isAdmin: 50,
        isWrite: 30,
    });

    var COMPONENT = this,
        SYS = Brick.mod.sys;

    SYS.Application.build(COMPONENT, {}, {
        initializer: function(){
            this.appStructure(function(){
                NS.roles.load(function(){
                    this.initCallbackFire();
                }, this);
            }, this);
        },
    }, [], {
        APPS: {
            uprofile: {}
        },
        ATTRS: {
            isLoadAppStructure: {value: false},
            ownerList: {
                readOnly: true,
                getter: function(){
                    if (!this._ownerListAttr){
                        this._ownerListAttr = new NS.OwnerList({appInstance: this});
                    }
                    return this._ownerListAttr;
                }
            },
            Owner: {value: NS.Owner},
            Invite: {value: NS.Invite},
        },
        REQS: {
            stat: {
                attrs: ['owner'],
                type: 'model:Stat'
            },
            userSearch: {
                args: ['data'],
                onResponse: function(d){
                    console.log(arguments);
                    return;
                    d.userid = d.userid | 0;
                    if (d.userid === 0){
                        return;
                    }

                    var userIds = [d.userid];

                    return function(callback, context){
                        this.getApp('uprofile').userListByIds(userIds, function(err, result){
                            callback.call(context || null);
                        }, context);
                    };
                }
            }
        },
        URLS: {
            ws: "#app={C#MODNAMEURI}/wspace/ws/",
        }
    });
};