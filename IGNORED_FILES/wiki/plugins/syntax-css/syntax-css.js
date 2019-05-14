var syntax_css = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-css/syntax-css.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.css = [
           "Wiky.rules.pre",
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class='syntax_css'>");}}, // style for syntax
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='cssstr'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='cssstr'>\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/\/\*(.*?)\*\//g, tmplt:function($0,$1){return Wiky.store("<span class\='cmt'>\/*"+$1+"*\/</span>");}}, // multi-line comment
          { rex:/(ascent|azimuth|background-attachment|background-color|background-image|background-position|background-repeat|background|baseline|bbox|border-collapse|border-color|border-spacing|border-style|border-top|border-right|border-bottom|border-left|border-top-color|border-right-color|border-bottom-color|border-left-color|border-top-style|border-right-style|border-bottom-style|border-left-style|border-top-width|border-right-width|border-bottom-width|border-left-width|border-width|border|cap-height|caption-side|centerline|clear|clip|color|content|counter-increment|counter-reset|cue-after|cue-before|cue|cursor|definition-src|descent|direction|display|elevation|empty-cells|float|font-size-adjust|font-family|font-size|font-stretch|font-style|font-variant|font-weight|font|height|letter-spacing|line-height|list-style-image|list-style-position|list-style-type|list-style|margin-top|margin-right|margin-bottom|margin-left|margin|marker-offset|marks|mathline|max-height|max-width|min-height|min-width|orphans|outline-color|outline-style|outline-width|outline|overflow|overflow-x|overflow-y|padding-top|padding-right|padding-bottom|padding-left|padding|page|page-break-after|page-break-before|page-break-inside|pause|pause-after|pause-before|pitch|pitch-range|play-during|position|quotes|richness|size|slope|src|speak-header|speak-numeral|speak-punctuation|speak|speech-rate|stemh|stemv|stress|table-layout|text-align|text-decoration|text-indent|text-shadow|text-transform|unicode-bidi|unicode-range|units-per-em|vertical-align|visibility|voice-family|volume|white-space|widows|width|widths|word-spacing|x-height|z-index|top|right|bottom|left)(?=:)\b|\b([a-z_\\*]|\\*|)(?=:)\b(?=[^>]*<)/g, tmplt:"<span class='csskwd'>$1</span>" }, // keywords
          { rex:/([\:\s])(none|hidden|dotted|dashed|solid|double|groove|ridge|inset|outset|thin|medium|thick|length|left|right|both|url|auto|crosshair|default|pointer|move|e-resize|ne-resize|nw-resize|n-resize|se-resize|sw-resize|s-resize|w-resize|text|wait|help|inline-block|inline|block|list-item|run-in|compact|marker|table|inline-table|table-row-group|table-header-group|table-footer-group|table-row|table-column-group|table-column|table-cell|table-caption|static|relative|absolute|fixed|visible|hidden|collapse|xx-small|x-small|small|medium|large|x-large|xx-large|smaller|larger|italic|oblique|bold|bolder|lighter|normal|disc|circle|square|decimal|decimal-leading-zero|lower-roman|upper-roman|lower-alpha|upper-alpha|lower-greek|lower-latin|upper-latin|hebrew|armenian|georgian|cjk-ideographic|hiragana|katakana|hiragana-iroha|katakana-iroha|static|relative|absolute|fixed|baseline|bottom|length|middle|sub|super|text-bottom|text-top|top|center|justify|underline|overline|line-through|blink|capitalize|uppercase|lowercase)/g, tmplt:"$1<span class='cssprop'>$2</span>" }, // properties
          { rex:/([\:\s])(\#[a-zA-Z0-9]{3,6})/g, tmplt:"$1<span class='cssval'>$2</span>" }, // values
          { rex:/((-?\d+)(\.\d+)?(px|em|pt|\:|%))/g, tmplt:"<span class='cssval'>$1</span>" }, // values
          //{ rex:/([\s]*)(html|body|h1|p|br|hr|b|font|i|em|big|strong|small|sup|sub|bdo|u|pre|code|tt|kbd|var|dfn|samp|xmp|acronym|abbr|address|blockquote|center|q|cite|ins|del|s|strike|a|link|frame|frameset|noframes|iframe|form|input|textarea|button|select|optgroup|option|label|fieldset|legend|isindex|ul|ol|li|dir|dl|dt|dd|menu|img|map|area|table|caption|th|tr|td|thead|tbody|tfoot|col|colgroup|style|div|span|head|title|meta|base|basefont)(([\s]*|\.)[\s\n]*[\{])/g, tmplt:"$1<span class=\"tag\">$2</span>$3" }, // members
          "Wiky.rules.post",
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.css);       
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
