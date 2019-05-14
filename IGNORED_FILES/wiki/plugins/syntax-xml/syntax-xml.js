var syntax_xml = {
	id:null,
	parms:null,
	element:null,
	html:null,
	
	init:function(id,parms){
		this.id=id;
		this.parms=parms;
		this.element = document.getElementById(this.id);
		this.addstyle("plugins/syntax-xml/syntax-xml.css");		
        this.html = this.element.innerHTML;
        Wiky.rules.lang.xml = [
              { rex:/(\r?\n)/g, tmplt:"\xB6" },  // replace line breaks with '�' ..
              { rex:/<p>(.*)<\/p>/gi, tmplt:"$1"}, 
              { rex:/(<pre)>/gi, tmplt:function($0,$1){return Wiky.store("<pre class='syntax_xml'>");}}, 
              { rex:/(<\/pre)>/gi, tmplt:function($0,$1){return Wiky.store("</pre>");}}, 
              
              { rex:/&lt;!\[CDATA\[(.*?)\]\]&gt;/g, tmplt:function($0,$1){return Wiky.store("&lt;!<span class='cdata'>[CDATA[</span>"+$1+"<span class='cdata'>]]</span>&gt;");} }, // CDATA sections, ..
              { rex:/&lt;!--(.*?)--&gt;/g, tmplt:function($0,$1){return Wiky.store("<span class='xcmt'>&lt;!--"+$1+"--&gt;</span>");} }, // inline xml comments, doctypes, ..

              { rex:/([-A-Za-z0-9_:]+)[ ]*=[ ]*\'(.*?)\'/g, tmplt:function($0,$1,$2){return Wiky.store("<span class='xnam'>"+$1+"</span>=<span class='xval'>&#39;"+$2+"&#39;</span>");}}, // "xml attribute value strings ..
              { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='xval'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
              { rex:/&lt;(\/?)([-A-Za-z0-9_:]+)/g, tmplt:function($0,$1,$2){return Wiky.store("<span class='xsymb'>&lt;"+$1+"</span><span class='xtag'>"+$2+"</span>");}}, // "xml tag ..
              { rex:/(\/?&gt;)/g,tmplt:"<span class='xsymb'>$1</span>"},
              { rex:/@([0-9]+)@/g, tmplt:function($0,$1){return Wiky.restore($1);} }, // resolve blocks ..
              { rex:/\xB6/g, tmplt:"\n" }
        ];
	},
	
	render:function(){
	    Wiky.blocks = [];
	    this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.xml);
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

