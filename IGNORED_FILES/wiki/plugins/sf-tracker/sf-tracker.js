var sf_tracker = {
	id:null,
	parms:null,
	element:null,
	
	init:function(id,parms){
		this.id=id;
		this.parms=parms;
		this.element = document.getElementById(this.id);
		this.addstyle("plugins/sf-tracker/sf-tracker.css");
	},
	
	render:function(){
		var feed = "plugins/sf-tracker/sf-tracker.php?group_id="+this.parms.group_id+"&atid="+this.parms.atid;
        var self = this;
		this.element.innerHTML = "<img src='images/system/bigrotation2.gif' />"
        ajax(feed,null,
        function(x){
            self.element.innerHTML = x.responseText;
        },
        "GET"
        );
		
	},
	
	addstyle:function(s){
		if (ArrayContains(css_urls,s)) return;
		css_urls.push(s);
 		var css=document.createElement("link")
  		css.setAttribute("rel", "stylesheet")
  		css.setAttribute("type", "text/css")
  		css.setAttribute("href", s);
		document.getElementsByTagName("head")[0].appendChild(css);
	}
}
