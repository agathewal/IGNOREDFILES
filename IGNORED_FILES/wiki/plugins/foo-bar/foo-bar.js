var foo_bar = {
	id:null,
	parms:null,
	element:null,
	
	init:function(id,parms){
		this.id=id;
		this.parms=parms;
		this.element = document.getElementById(this.id);
		this.addstyle("plugins/foo-bar/foo-bar.css");
		this.element.innerHTML = '';
	},
	
	render:function(){
		this.element.innerHTML = "<span class='foo'>"+this.parms.foo+"</span> <span class='bar'>"+this.parms.bar+"</span>";
		
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
