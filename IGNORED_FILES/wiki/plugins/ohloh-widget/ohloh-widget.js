var ohloh_widget = {
    id:null,
    parms:null,
    element:null,
    widgets:{
        "use-logo":"project_users_logo.js",
        "use-green":"project_users.js?style=green",
        "use-red":"project_users.js?style=red",
        "use-blue":"project_users.js?style=blue",
        "use-rainbow":"project_users.js?style=rainbow",
        "use-simple":"project_users.js",
        "use-gray":"project_users.js?style=gray",
        "thin-badge":"project_thin_badge.js",
        "partner-badge":"project_partner_badge.js",
        "languages":"project_languages.js",
        "factoids":"project_factoids.js",
        "cocomo":"project_cocomo.js",
        "stats":"project_basic_stats.js"
	},
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
		
    },
    
    render:function(){
		var src = this.parms.id+"/widgets/"+this.widgets[this.parms.widget]
        var handler = "plugins/ohloh-widget/ohloh-widget.php?src="+encodeURIComponent(src);
        var self = this;
		this.element.innerHTML = "<img src='images/system/bigrotation2.gif' />"
        ajax(handler,null,
        function(x){
            self.element.innerHTML = x.responseText;
        },
        "GET"
        );
        
    },
    
}
