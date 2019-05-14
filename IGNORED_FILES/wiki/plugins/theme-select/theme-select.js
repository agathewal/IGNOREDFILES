var theme_select = {
    id:null,
    parms:null,
    element:null,
    styles:null,
    current:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.element.innerHTML = '';
        if(theme_select.current==null)
          theme_select.current = theme_select.getcurrent();
    },
    
    render:function(){
        
        // load select element with list of styles from server
        var handler = "plugins/theme-select/themes.php?cur="+theme_select.current;
        var self = this;
		ajax(handler,null,
		function(x){
			self.element.innerHTML = x.responseText;
		},
		"GET"
		);
        
    },

    replacestyles:function(){
        
        // remove current style and add new on for each stylesheet
        var styles = ["ui.css","tabs.css","tree.css","popupform.css"]
        var theme = document.getElementById("seltheme").value;
        for(var i=0;i<styles.length;i++){
            this.removestyle(styles[i]);
            this.addstyle("theme/"+theme+"/css/"+styles[i]);
        }
        theme_select.current = theme;       
    },
    
    addstyle:function(s){
        
        // the old style will be removed in replacestyles so no need to worry about duplicating     
        var css=document.createElement("link")
        css.setAttribute("rel", "stylesheet")
        css.setAttribute("type", "text/css")
        css.setAttribute("href", s);
        document.getElementsByTagName("head")[0].appendChild(css);
    },
    
    removestyle:function(filename){
        
        // loop through all link elements and remove match to filename
        var targetelement="link";
        var targetattr="href";
        var allsuspects=document.getElementsByTagName(targetelement);
        for (var i=allsuspects.length-1; i>=0; i--){ //search backwards within nodelist for matching elements to remove
            if (allsuspects[i] && allsuspects[i].getAttribute(targetattr)!=null &&
                    (allsuspects[i].getAttribute(targetattr).indexOf(filename)!=-1) ||
                    allsuspects[i].getAttribute(targetattr).indexOf("min.css") > -1)
                allsuspects[i].parentNode.removeChild(allsuspects[i]) //remove element by calling parentNode.removeChild()
        }       
    },
    getcurrent:function(){
        
        // loop through all link elements to find theme
        var targetelement="link";
        var targetattr="href";
        var allsuspects=document.getElementsByTagName(targetelement);
        for (var i=allsuspects.length-1; i>=0; i--){ //search backwards within nodelist for matching elements
            if (allsuspects[i] && allsuspects[i].getAttribute(targetattr)!=null &&
                    (allsuspects[i].getAttribute(targetattr).indexOf("ui.css") > -1 ||
                    allsuspects[i].getAttribute(targetattr).indexOf("min.css") > -1)
                    ) {
                var paths = allsuspects[i].getAttribute(targetattr).split("/");
                return paths[1];
            }
        }       
    }
}
