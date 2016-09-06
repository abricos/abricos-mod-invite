var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['appModel.js']}
    ]
};
Component.entryPoint = function(NS){
    var Y = Brick.YUI,
        SYS = Brick.mod.sys;

    NS.isOwner = function(val){
        if (!val){
            return false;
        }
        if (val.module && val.type && val.ownerid){
            return true;
        }
        if (!Y.Lang.isFunction(val.get)){
            return false;
        }
        return true;
    };

    NS.ATTRIBUTE = {
        owner: {
            validator: NS.isOwner,
            setter: function(val){
                if (val.module && val.type && val.ownerid){
                    return this.get('appInstance').get('ownerList').getOwner(val.module, val.type, val.ownerid);
                }
                return val;
            }
        }
    };

    NS.Owner = Y.Base.create('owner', SYS.AppModel, [], {
        structureName: 'Owner',
        compare: function(val){
            if (!NS.isOwner(val)){
                return false;
            }
            return val.get('module') === this.get('module')
                && val.get('type') === this.get('type')
                && val.get('ownerid') === this.get('ownerid');
        }
    }, {
        ATTRS: {
            id: {
                readOnly: true,
                getter: function(){
                    return this.get('module') + '|'
                        + this.get('type') + '|'
                        + this.get('ownerid');
                }
            },
        }
    });

    NS.OwnerList = Y.Base.create('ownerList', SYS.AppModelList, [], {
        appItem: NS.Owner,
        getOwner: function(module, type, ownerid){
            var app = this.appInstance,
                Owner = app.get('Owner'),
                id;

            if (Y.Lang.isObject(module)){
                var tempOwner = new Owner(Y.merge(module, {appInstance: app}));
                id = tempOwner.get('id');
            } else {
                id = module + '|' + type + '|' + ownerid;
            }

            var owner = this.getById(id);
            if (owner){
                return owner;
            }

            if (Y.Lang.isObject(module)){
                owner = new Owner(Y.merge(module, {appInstance: app}));
            } else {
                owner = new Owner({
                    module: module,
                    type: type,
                    ownerid: ownerid,
                    appInstance: app
                });
            }

            this.add(owner);

            return owner;
        },
    });

    NS.Invite = Y.Base.create('invite', SYS.AppModel, [], {
        structureName: 'Invite'
    });
};
