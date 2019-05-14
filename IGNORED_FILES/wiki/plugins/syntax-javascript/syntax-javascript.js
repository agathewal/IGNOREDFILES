var syntax_javascript = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-javascript/syntax-javascript.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.js = [
           "Wiky.rules.pre",
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class=\"syntax_js\">");}}, // style for syntax
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='str'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'/g, tmplt:function($0,$1){return Wiky.store("<span class=\"str\">\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/\/\/(.*?)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"cmt\">//"+$1+"</span>\xB6");}}, // single line comment
          { rex:/\/\*(.*?)\*\//g, tmplt:function($0,$1){return Wiky.store("<span class\=\"cmt\">\/*"+$1+"*\/</span>");}}, // multi-line comment
          { rex:/\b(break|case|catch|continue|do|else|false|for|function|if|in|new|return|switch|this|throw|true|try|var|while|with)\b/g, tmplt:"<span class=\"kwd\">$1</span>" }, // keywords
          { rex:/\b(arguments|Array|Boolean|Date|Error|Function|Global|Math|Number|Object|RegExp|String)\b/g, tmplt:"<span class=\"obj\">$1</span>" }, // objects
          { rex:/\.(abs|acos|anchor|arguments|asin|atan|atan2|big|blink|bold|callee|caller|ceil|charAt|charCodeAt|concat|constructor|cos|E|escape|eval|exp|fixed|floor|fontcolor|fontsize|fromCharCode|getDate|getDay|getFullYear|getHours|getMilliseconds|getMinutes|getMonth|getSeconds|getTime|getTimezoneOffset|getUTCDate|getUTCDay|getUTCFullYear|getUTCHours|getUTCMilliseconds|getUTCMinutes|getUTCMonth|getUTCSeconds|getVarDate|getYear|index|indexOf|Infinity|input|isFinite|isNaN|italics|join|lastIndex|lastIndexOf|lastMatch|lastParen|leftContext|length|link|LN10|LN2|log|LOG10E|LOG2E|match|max|MAX_VALUE|min|MIN_VALUE|NaN|NaN|NEGATIVE_INFINITY|parse|parseFloat|parseInt|PI|pop|POSITIVE_INFINITY|pow|prototype|push|random|replace|reverse|rightContext|round|search|setDate|setFullYear|setHours|setMilliseconds|setMinutes|setMonth|setSeconds|setTime|setUTCDate|setUTCFullYear|setUTCHours|setUTCMilliseconds|setUTCMinutes|setUTCMonth|setUTCSeconds|setYear|shift|sin|slice|slice|small|sort|splice|split|sqrt|SQRT1_2|SQRT2|strike|sub|substr|substring|sup|tan|toGMTString|toLocaleString|toLowerCase|toString|toUpperCase|toUTCString|unescape|unshift|UTC|valueOf)\b/g, tmplt:".<span class=\"mbr\">$1</span>" }, // members
          "Wiky.rules.post",
    
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.js);        
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
