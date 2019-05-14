// TODO: read arrays is strings "blah, blah {$array[0]} blah, blah"

var syntax_php = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-php/syntax-php.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.php = [
           "Wiky.rules.pre",
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class='syntax_php'>");}}, // style for syntax
          { rex:/\/\/(.*?)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\='phpcmt'>//"+$1+"</span>\xB6");}}, // single line comment
          { rex:/#(.*?)(?=[^>]*<)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\='phpcmt'>#"+$1+"</span>\xB6");}}, // single line comment
          { rex:/\/\*(.*?)\*\//g, tmplt:function($0,$1){return Wiky.store("<span class\='phpcmt'>\/*"+$1+"*\/</span>");}}, // multi-line comment
          { rex:/(\$\$?)([a-zA-Z0-9_]+)/g, tmplt:function($0,$1,$2){return Wiky.store("<span class\='phpvar'>"+$1+$2+"</span>");}}, // variables
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='phpstr'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class=\"phpstr\">\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/\b(and|or|xor|__FILE__|exception|php_user_filter|__LINE__|array|as|break|case|catch|cfunction|class|const|continue|declare|default|die|do|each|echo|else|elseif|empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|eval|exit|extends|for|foreach|function|global|if|isset|list|new|old_function|print|return|static|switch|try|unset|use|var|while|__FUNCTION__|__CLASS__|__METHOD__)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"phpkwd\">"+$1+"</span>");}}, // keywords
          { rex:/\b(define|include|include_once|require|require_once)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\='phpkwd2'>"+$1+"</span>");}}, // more keywords
          { rex:/\b(FALSE|NULL|TRUE)\b/gi, tmplt:function($0,$1){return Wiky.store("<span class\='phpliteral'>"+$1+"</span>");}}, // literals
          { rex:/(\s?|-&gt;)([a-zA-Z0-9_]+)(\s?\()/g, tmplt:function($0,$1,$2,$3){return Wiky.store($1+"<span class\='phpmbr'>"+$2+"</span>"+$3);}}, // members
          "Wiky.rules.post",
    
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.php);
        
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
