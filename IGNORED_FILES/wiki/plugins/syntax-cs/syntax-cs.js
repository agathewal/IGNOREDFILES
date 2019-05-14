var syntax_cs = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-cs/syntax-cs.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.cs = [
           "Wiky.rules.pre",
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class=\"syntax_cs\">");}}, // style for syntax
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='csstr'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'/g, tmplt:function($0,$1){return Wiky.store("<span class=\"csstr\">\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/\/\/(.*?)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"cscmt\">//"+$1+"</span>\xB6");}}, // single line comment
          { rex:/\/\*(.*?)\*\//g, tmplt:function($0,$1){return Wiky.store("<span class\=\"cscmt\">\/*"+$1+"*\/</span>");}}, // multi-line comment
          { rex:/\b(abstract|as|base|break|case|catch|checked|const|continue|decimal|default|delegate|do|else|explicit|extern|finally|fixed|for|foreach|goto|if|implicit|in|internal|is|lock|new|operator|out|override|params|private|protected|public|readonly|ref|return|sealed|sizeof|stackalloc|static|switch|throw|try|typeof|unchecked|unsafe|virtual|while|using|namespace|set|get|value)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"cskwd\">"+$1+"</span>");}}, // keywords
          { rex:/\b(bool|byte|char|class|double|enum|event|float|int|interface|long|object|sbyte|short|string|struct|uint|ulong|ushort|void)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"cskwd2\">"+$1+"</span>");}}, // more keywords
          { rex:/\b(false|null|this|true)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"csliteral\">"+$1+"</span>");}}, // literals
          { rex:/([\s\.])([a-zA-Z0-9_]+)(\s?\()/g, tmplt:function($0,$1,$2,$3){return Wiky.store($1+"<span class\=\"csmbr\">"+$2+"</span>"+$3);}}, // multi-line comment
          "Wiky.rules.post",    
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.cs);        
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
