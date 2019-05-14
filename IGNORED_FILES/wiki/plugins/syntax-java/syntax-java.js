var syntax_java = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-java/syntax-java.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.java = [
           "Wiky.rules.pre",
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class=\"syntax_java\">");}}, // style for syntax
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='javastr'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'/g, tmplt:function($0,$1){return Wiky.store("<span class=\"javastr\">\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/\/\/(.*?)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"javacmt\">//"+$1+"</span>\xB6");}}, // single line comment
          { rex:/\/\*(.*?)\*\//g, tmplt:function($0,$1){return Wiky.store("<span class\=\"javacmt\">\/*"+$1+"*\/</span>");}}, // multi-line comment
          { rex:/\b(abstract|break|case|catch|continue|default|do|else|extends|final|finally|for|if|implements|instanceof|native|new|private|protected|public|return|static|switch|synchronized|throw|throws|transient|try|volatile|while|import|package)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"javakwd\">"+$1+"</span>");}}, // keywords
          { rex:/\b(boolean|byte|char|class|double|float|int|interface|long|short|void)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"javakwd2\">"+$1+"</span>");}}, // more keywords
          { rex:/\b(false|null|super|this|true)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"javaliteral\">"+$1+"</span>");}}, // literals
          { rex:/([\s\.])([a-zA-Z0-9_]+)(\s?\()/g, tmplt:function($0,$1,$2,$3){return Wiky.store($1+"<span class\=\"javambr\">"+$2+"</span>"+$3);}}, // multi-line comment
          "Wiky.rules.post",    
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.java);        
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
