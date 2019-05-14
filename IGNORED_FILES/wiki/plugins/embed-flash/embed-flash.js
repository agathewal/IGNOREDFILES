var embed_flash = {
	id:null,
	parms:null,
	element:null,
	
	init:function(id,parms){
		this.id=id;
		this.parms=parms;
		if(this.parms.autostart == null) this.parms.autostart = false;
		this.element = document.getElementById(this.id);
		this.element.innerHTML = '';
	},
	
	render:function(){
		var html = '<embed src="'+this.parms.src+'" width="'+this.parms.width+'" height="'+this.parms.height
					+'" autostart="'+this.parms.autostart+'" type="application/x-shockwave-flash" />';
		this.element.innerHTML = html;
	}
}

