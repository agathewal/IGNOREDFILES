var popup = {

	postmode: null,
	showing: false,
	blocker: null,
	
	show : function(form, id, handler, w, resp, validator) {
		if(this.showing) return;
		
		// blocker
		if(this.blocker==null){
			this.blocker = document.createElement("div");
			this.blocker.style.position = "absolute";
			this.blocker.style.left = 0;
			this.blocker.style.right = 0;
			this.blocker.style.top = 0;
			this.blocker.style.bottom = 0;
			this.blocker.style.background = "#ccc";
			this.blocker.id="blocker";
			document.body.appendChild(this.blocker);			
		}
		this.blocker.onmousedown = function(){
		popup.closeform(id);
        };
        this.blocker.style.display = "block";
		
		var elid = id;
		var element = document.getElementById(id);
		var action = handler;
		var width = w;
		var val = validator==null?"":",\""+validator+"\"";
		
		ajax(form,
		  null,
		  function(x){
                var response = x.responseText;
                element.innerHTML = "<form  onsubmit='popup.submitform(this,\""
                                    +handler+"\",\""
                                    +elid+"\",\""+resp+"\""
                                    +val+");return false;'><div style='width:"+width
                                    +";position:absolute;'><div class='popup'><div style='float:right;'>"
                                    +"<a href='javascript:popup.closeform(\""
                                    +elid+"\");' class='close'>"
                                    +"x</a></div><div style='clear:both;'></div>"
                                    +response
                                    +"</div></div></form>";
                element.style.display = 'block';
		  }
		);

		this.showing = true;
	},
	
	closeform : function(id){
		document.getElementById(id).style.display='none';
		this.blocker.style.display='none';
		this.showing = false;
	},
	
	submitform : function(frm,action,id,resp, val){
		if (val != null) {
			if(!eval(val))
				return false;
			
		}
		
		var url = action;
		var params = '';
		var element = document.getElementById(id);
		for(i=0; i<frm.elements.length; i++){
			if(frm.elements[i].name!=''){
				params+= + i==0?'':'&';
				params+=frm.elements[i].name + "=" + encodeURIComponent(frm.elements[i].value);
			}
		}		

		var self = this;
		var res = resp;
        ajax(url,
          params,
          function(x){
                var obj = eval('(' + x.responseText + ')');

                if (obj.response == 'ok') {                 
                    element.style.display = 'none';
                    self.showing = false;
                    self.blocker.style.display='none';
                }//else{alert(obj.response);}
                eval(""+res+"("+x.responseText+")");
          }
        );
	}
	
}

