var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'team', files: ['lib.js']},
        {name: '{C#MODNAME}', files: ['model.js']}
    ]
};
Component.entryPoint = function(NS){
    var COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isAdmin: 50,
        isWrite: 30,
    });

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
            UserSearch: {value: NS.UserSearch},
        },
        REQS: {
            stat: {
                attrs: ['owner'],
                type: 'model:Stat'
            },
            userSearch: {
                args: ['data'],
                type: 'response:UserSearch',
                onResponse: function(userSearch){
                    var userid = userSearch.get('userid');
                    if (userid === 0){
                        return;
                    }

                    var userIds = [userid];

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