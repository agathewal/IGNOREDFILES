/*	This work is licensed under Creative Commons GNU LGPL License.

	License: http://creativecommons.org/licenses/LGPL/2.1/

	Author:  Stefan Goessner/2005-06
	Web:     http://goessner.net/ 
*/

/*
 * Modified for Wiki Web Help Project, original header above
 * 
 */

var Wiky = {
  version: 0.95,
  blocks: null,
  plugins: null,
  rules: {
     all: [
       "Wiky.rules.pre",
       "Wiky.rules.nonwikiblocks",
       "Wiky.rules.wikiblocks"
//       "Wiky.rules.post",
     ],
     pre: [
       { rex:/(\r?\n)/g, tmplt:"\xB6" }  // replace line breaks with '�' ..
     ],
     post: [
       { rex:/(^\xB6)|(\xB6$)/g, tmplt:"" },  // .. remove linebreaks at BOS and EOS ..
       { rex:/@([0-9]+)@/g, tmplt:function($0,$1){return Wiky.restore($1);} }, // resolve blocks ..
       { rex:/\xB6/g, tmplt:"\n" } // replace '�' with line breaks ..
     ],
     nonwikiblocks: [
       { rex:/\\([%])/g, tmplt:function($0,$1){return Wiky.store($1);} },
       { rex:/\[%(.*?)%\]/g, tmplt:function($0,$1){return Wiky.store("<pre>" + Wiky.apply($1, Wiky.rules.code) + "</pre>")+"[p:";} }
     ],
     wikiblocks: [
       "Wiky.rules.nonwikiinlines",
       "Wiky.rules.escapes",
       { rex:/(?:^|\xB6)(={1,6})(.*?)[=]*(?=\xB6|$)/g, tmplt:function($0,$1,$2){ var h=$1.length; return ":p]\xB6<h"+h+">"+$2+"</h"+h+">\xB6[p:";} }, // <h1> .. <h6>
       { rex:/(?:^|\xB6)[-]{4}(?:\xB6|$)/g, tmplt:"\xB6<hr/>\xB6" },  // horizontal ruler ..
       { rex:/\\\\([ \xB6])/g, tmplt:"<br/>$1" },  // forced line break ..
       { rex:/`(?:\{(.*?)\})([^`]+)`/g, tmplt:function($0,$1,$2){return Wiky.store("<span"+Wiky.style($1)+">"+$2+"</span>");} }, // inline styling
       { rex:/(^|\xB6)([*01aAiIg]*[\.*])[ ]/g, tmplt:function($0,$1,$2){var state=$2.replace(/([*])/g,"u").replace(/([\.])/,"");return ":"+state+"]"+$1+"["+state+":";}},
       { rex:/(?:^|\xB6);[ ](.*?):[ ]/g, tmplt:"\xB6:l][l:$1:d][d:"},  // ; term : definition
       { rex:/\[(?:\{([^}]*)\})?(?:\(([^)]*)\))?\"/g, tmplt:function($0,$1,$2){return ":p]<blockquote"+Wiky.attr($2,"cite",0)+Wiky.attr($2,"title",1)+Wiky.style($1)+">[p:"; } }, // block quotation start
       { rex:/\"\]/g, tmplt:":p]</blockquote>[p:" }, // block quotation end
       { rex:/\[(\{[^}]*\})?\|/g, tmplt:":t]$1[r:" },  // .. start table ..
       { rex:/\|\]/g, tmplt:":r][t:" },  // .. end table ..
       { rex:/\|\xB6[ ]?\|/g, tmplt:":r]\xB6[r:" },  // .. end/start table row ..
       { rex:/\|/g, tmplt:":c][c:" },  // .. end/start table cell ..
       { rex:/^(.*)$/g, tmplt:"[p:$1:p]" },  // start paragraph '[p:' at BOS .. end paragraph ':p]' at EOS ..
       { rex:/(([\xB6])([ \t\f\v\xB6]*?)){2,}/g, tmplt:":p]$1[p:" },  // .. separate paragraphs at blank lines ..
       { rex:/\[([01AIacdgilprtu]+)[:](.*?)[:]([01AIacdgilprtu]+)\]/g, tmplt:function($0,$1,$2,$3){return Wiky.sectionRule($1==undefined?"":$1,"",Wiky.apply($2,Wiky.rules.wikiinlines),!$3?"":$3);} },
       { rex:/\[[01AIacdgilprtu]+[:]|[:][01AIacdgilprtu]+\]/g, tmplt:"" },  // .. remove singular section delimiters (they frequently exist with incomplete documents while typing) ..
       { rex:/<td>(?:([0-9]*)[>])?([ ]?)(.*?)([ ]?)<\/td>/g, tmplt:function($0,$1,$2,$3,$4){return "<td"+($1?" colspan=\""+$1+"\"":"")+($2==" "?(" style=\"text-align:"+($2==$4?"center":"right")+";\""):($4==" "?" style=\"text-align:left;\"":""))+">"+$2+$3+$4+"</td>";} },
       { rex:/<(p|table|h[1-6])>(?:\xB6)?(?:\{(.*?)\})/g, tmplt:function($0,$1,$2){return Wiky.store("<"+$1+Wiky.style($2)+">");} },
       { rex:/<p>([ \t\f\v\xB6]*?)<\/p>/g, tmplt:"$1" },  // .. remove empty paragraphs ..
       "Wiky.rules.shortcuts"
     ],
     nonwikiinlines: [
       { rex:/%(?:\{([^}]*)\})?(?:\(([^)]*)\))?(.*?)%/g, tmplt:function($0,$1,$2,$3){return Wiky.store("<code"+($2?(" lang=\"x-"+Wiky.attr($2)+"\""):"")+Wiky.style($1)+">" + Wiky.apply($3, $2?Wiky.rules.lang[Wiky.attr($2)]:Wiky.rules.code) + "</code>");} }, // inline code
       { rex:/%(.*?)%/g, tmplt:function($0,$1){return Wiky.store("<code>" + Wiky.apply($2, Wiky.rules.code) + "</code>");} }
     ],
     wikiinlines: [
       { rex:/\*([^*]+)\*/g, tmplt:"<strong>$1</strong>" },  // .. strong ..
       { rex:/\(\+(.+?)\+\)/g, tmplt:"<ins>$1</ins>" },
       { rex:/(((http(s?))\:\/\/)?[A-Za-z0-9\._\/~\-: ]+\.(?:png|jpg|jpeg|gif|bmp))/gi, tmplt:function($0,$1,$2){return Wiky.store("<img src=\""+$1+"\" alt=\""+$1+"\"/>");} },  // simple images .. 
       { rex:/_([^_]+)_/g, tmplt:"<i>$1</i>" },
       { rex:/\^([^^]+)\^/g, tmplt:"<sup>$1</sup>" },
       { rex:/~([^~]+)~/g, tmplt:"<sub>$1</sub>" },
       { rex:/\(-(.+?)-\)/g, tmplt:"<del>$1</del>" },
       { rex:/\?([^ \t\f\v\xB6]+)\((.+)\)\?/g, tmplt:"<abbr title=\"$2\">$1</abbr>" },  // .. abbreviation ..
       { rex:/\[(?:\{([^}]*)\})?[Ii]ma?ge?\:([^ ,\]]*)(?:[, ]([^\]]*))?\]/g, tmplt:function($0,$1,$2,$3){return Wiky.store("<img"+Wiky.style($1)+" src=\""+$2+"\" alt=\""+($3?$3:$2)+"\" title=\""+($3?$3:$2)+"\"/>");} },  // wikimedia image style ..
       
	   { rex:/#\]/g, tmplt:"</div>" },
       { rex:/\[#\s*([^ ,]+)[, ]([^\]]*);/g, tmplt:function($0,$1,$2){return Wiky.plug($1,$2);}},
       
	   { rex:/\[(\/[a-zA-z0-9_\u00A1-\uFFFF\-\/]+)\]/g, tmplt:function($0,$1){return Wiky.store("<a href=\"javascript:pageFromPath('"+$1+"');\">"+Wiky.word($1)+"</a>");}},  // wiki words ..
       { rex:/\[(http(s?)\:\/\/[^ ,]+)[, ]([^\]]*)\]/g, tmplt:function($0,$1,$2,$3){return Wiky.store("<a class='extlink title_fst_color' target='_blank' href=\""+$1+$2+"\">"+$3+"</a>");}},  // wiki block style uri's ..
       { rex:/\[([^ ,]+)[, ]([^\]]*)\]/g, tmplt:function($0,$1,$2){return Wiky.store("<a href=\""+$1+"\">"+$2+"</a>");}},  // wiki block style uri's ..
       { rex:/((mailto\:|javascript\:|(news|file|(ht|f)tp(s?))\:\/\/)[A-Za-z0-9\.:_\/~%\-+&#?!=()@\x80-\xB5\xB7\xFF]+)/g, tmplt:"<a href=\"$1\">$1</a>" }  // simple uri's .. 
     ],
     escapes: [
       { rex:/\\([|*_~\^])/g, tmplt:function($0,$1){return Wiky.store($1);} },
       { rex:/\\&/g, tmplt:"&amp;" },
       { rex:/\\>/g, tmplt:"&gt;" },
       { rex:/\\</g, tmplt:"&lt;" }
     ],
     shortcuts: [
       { rex:/---/g, tmplt:"&#8212;" },  // &mdash;
       { rex:/--/g, tmplt:"&#8211;" },  // &ndash;
       { rex:/[\.]{3}/g, tmplt:"&#8230;"}, // &hellip;
       { rex:/<->/g, tmplt:"&#8596;"}, // $harr;
       { rex:/<-/g, tmplt:"&#8592;"}, // &larr;
       { rex:/->/g, tmplt:"&#8594;"} //&rarr;
     ],
     code: [
       { rex:/&/g, tmplt:"&amp;"},
       { rex:/</g, tmplt:"&lt;"},
       { rex:/>/g, tmplt:"&gt;"}
     ],
     lang: {}
   },

   toHtml: function(str) {
		Wiky.blocks = [];
		Wiky.plugins = [];
		return str_replace(["</h1><br />","</h2><br />","</h3><br />","</h4><br />","</h5><br />","</h6><br />","<br />\n<li class=\"u\">","<br />\n<li class=\"1\">"], ["</h1>","</h2>","</h3>","</h4>","</h5>","</h6>","<li>","<li>"],nl2br(Wiky.apply(Wiky.sanitize(Wiky.apply(str, Wiky.rules.all)),Wiky.rules.post)));
   },

   apply: function(str, rules) {
      if (str && rules)
         for (var i in rules) {
            if (typeof(rules[i]) == "string")
               str = Wiky.apply(str, eval(rules[i]));
            else
               str = str.replace(rules[i].rex, rules[i].tmplt);
         }
      return str;
   },
   plug: function(plgn,parms) {
   	  var obj = eval('(' + parms + ')');
	  var id = plgn+"_"+Wiky.plugins.length;
   	  Wiky.plugins.push([plgn,id, obj]);
   	  var $str = "<div id='"+id+"'>";
   	  return $str;
   },
   sanitize: function(text){
     // create a dummy element
    var div = document.createElement('div');
    div.innerHTML = text;
    var alltags = div.getElementsByTagName('*');
    
    // loop through any html tags that may have been entered
    for (var i = alltags.length-1; i > -1; i--) {
        var el = alltags.item(i);
        var elname = el.tagName.toLowerCase();
        if(elname=='object' || elname=='iframe' || elname=='embed'){
			var p = el.parentNode;
            p.removeChild(el);
            continue;
        }

        if(el.style.position == 'absolute' || el.style.position == 'relative') el.style.position="static"; 
        // loop through any attributes(backwards because we delete as we go)
        var attrs = el.attributes;
        var l = attrs.length;
        
        for (var a = l - 1; a > -1; a--) {
            var attr = attrs[a].name;
            
            // remove on anything
            if (attr.indexOf('on') == 0) {
                el.removeAttribute(attr);
            }
        }
    }
    
    var r = div.innerHTML;
    div = null;
    return r;
   },
   store: function(str, unresolved) {
      return unresolved ? "@" + (Wiky.blocks.push(str)-1) + "@"
                        : "@" + (Wiky.blocks.push(str.replace(/@([0-9]+)@/g, function($0,$1){return Wiky.restore($1);}))-1) + "@";
   },
   restore: function(idx) {
      return Wiky.blocks[idx];
   },
   attr: function(str, name, idx) {
      var a = str && str.split(",")[idx||0];
      return a ? (name ? (" "+name+"=\""+a+"\"") : a) : "";
   },
   hasAttr: function(str, name) {
      return new RegExp(name+"=").test(str);
   },
   attrVal: function(str, name) {
      return str.replace(new RegExp("^.*?"+name+"=\"(.*?)\".*?$"), "$1");
   },
   invAttr: function(str, names) {
      var a=[], x;
      for (var i in names)
         if (str.indexOf(names[i]+"=")>=0) 
            a.push(str.replace(new RegExp("^.*?"+names[i]+"=\"(.*?)\".*?$"), "$1"));
      return a.length ? ("("+a.join(",")+")") : "";
   },
   style: function(str) {
      var s = str && str.split(/,|;/), p, style = "";
      for (var i in s) {
	  	 if(s[i]==null) continue;
         p = s[i].split(":");
         if (p[0] == ">")       style += "margin-left:4em;";
         else if (p[0] == "<")  style += "margin-right:4em;";
         else if (p[0] == ">>") style += "float:right;";
         else if (p[0] == "<<") style += "float:left;";
         else if (p[0] == "=") style += "display:block;margin:0 auto;";
         else if (p[0] == "_")  style += "text-decoration:underline;";
         else if (p[0] == "b")  style += "border:solid 1px;";
         else if (p[0] == "c")  style += "color:"+p[1]+";";
         else if (p[0] == "C")  style += "background:"+p[1]+";";
         else if (p[0] == "w")  style += "width:"+p[1]+";";
         else                   style += p[0]+":"+p[1]+";";
      }
      return style ? " style=\""+style+"\"" : "";
   },
   invStyle: function(str) {
      var s = /style=/.test(str) ? str.replace(/^.*?style=\"(.*?)\".*?$/, "$1") : "",
          p = s && s.split(";"), pi, prop = [];
      for (var i in p) {
         pi = p[i].split(":");
         if (pi[0] == "margin-left" && pi[1]=="4em") prop.push(">");
         else if (pi[0] == "margin-right" && pi[1]=="4em") prop.push("<");
         else if (pi[0] == "float" && pi[1]=="right") prop.push(">>");
         else if (pi[0] == "float" && pi[1]=="left") prop.push("<<");
         else if (pi[0] == "margin" && pi[1]=="0 auto") prop.push("=");
         //else if (pi[0] == "display" && pi[1]=="block") ;
         else if (pi[0] == "text-decoration" && pi[1]=="underline") prop.push("_");
         else if (pi[0] == "border" && pi[1]=="solid 1px") prop.push("b");
         else if (pi[0] == "color") prop.push("c:"+pi[1]);
         else if (pi[0] == "background") prop.push("C:"+pi[1]);
         else if (pi[0] == "width") prop.push("w:"+pi[1]);
         else if (pi[0]) prop.push(pi[0]+":"+pi[1]);
      }
      return prop.length ? ("{" + prop.join(",") + "}") : "";
   },
   sectionRule: function(fromLevel, style, content, toLevel) {
      var trf = { p_p: "<p>$1</p>",
                  p_u: "<p>$1</p><ul$3>",
                  p_o: "<p>$1</p><ol$3>",
                  // p - ul
                  // ul - p
                  u_p: "<li$2>$1</li></ul>",
                  u_c: "<li$2>$1</li></ul></td>",
                  u_r: "<li$2>$1</li></ul></td></tr>",
                  uu_p: "<li$2>$1</li></ul></li></ul>",
                  uo_p: "<li$2>$1</li></ol></li></ul>",
                  uuu_p: "<li$2>$1</li></ul></li></ul></li></ul>",
                  uou_p: "<li$2>$1</li></ul></li></ol></li></ul>",
                  uuo_p: "<li$2>$1</li></ol></li></ul></li></ul>",
                  uoo_p: "<li$2>$1</li></ol></li></ol></li></ul>",
                  // ul - ul
                  u_u: "<li$2>$1</li>",
                  uu_u: "<li$2>$1</li></ul></li>",
                  uo_u: "<li$2>$1</li></ol></li>",
                  uuu_u: "<li$2>$1</li></ul></li></ul></li>",
                  uou_u: "<li$2>$1</li></ul></li></ol></li>",
                  uuo_u: "<li$2>$1</li></ol></li></ul></li>",
                  uoo_u: "<li$2>$1</li></ol></li></ol></li>",
                  u_uu: "<li$2>$1<ul$3>",
                  // ul - ol
                  u_o: "<li$2>$1</li></ul><ol$3>",
                  uu_o: "<li$2>$1</li></ul></li></ul><ol$3>",
                  uo_o: "<li$2>$1</li></ol></li></ul><ol$3>",
                  uuu_o: "<li$2>$1</li></ul></li></ul></li></ul><ol$3>",
                  uou_o: "<li$2>$1</li></ul></li></ol></li></ul><ol$3>",
                  uuo_o: "<li$2>$1</li></ol></li></ul></li></ul><ol$3>",
                  uoo_o: "<li$2>$1</li></ol></li></ol></li></ul><ol$3>",
                  u_uo: "<li$2>$1<ol$3>",
                  // ol - p
                  o_p: "<li$2>$1</li></ol>",
                  oo_p: "<li$2>$1</li></ol></li></ol>",
                  ou_p: "<li$2>$1</li></ul></li></ol>",
                  ooo_p: "<li$2>$1</li></ol></li></ol>",
                  ouo_p: "<li$2>$1</li></ol></li></ul></li></ol>",
                  oou_p: "<li$2>$1</li></ul></li></ol></li></ol>",
                  ouu_p: "<li$2>$1</li></ul></li></ul></li></ol>",
                  // ol - ul
                  o_u: "<li$2>$1</li></ol><ul$3>",
                  oo_u: "<li$2>$1</li></ol></li></ol><ul$3>",
                  ou_u: "<li$2>$1</li></ul></li></ol><ul$3>",
                  ooo_u: "<li$2>$1</li></ol></li></ol></li></ol><ul$3>",
                  ouo_u: "<li$2>$1</li></ol></li></ul></li></ol><ul$3>",
                  oou_u: "<li$2>$1</li></ul></li></ol></li></ol><ul$3>",
                  ouu_u: "<li$2>$1</li></ul></li></ul></li></ol><ul$3>",
                  o_ou: "<li$2>$1<ul$3>",
                  // -- ol - ol --
                  o_o: "<li$2>$1</li>",
                  oo_o: "<li$2>$1</li></ol></li>",
                  ou_o: "<li$2>$1</li></ul></li>",
                  ooo_o: "<li$2>$1</li></ol></li></ol></li>",
                  ouo_o: "<li$2>$1</li></ol></li></ul></li>",
                  oou_o: "<li$2>$1</li></ul></li></ol></li>",
                  ouu_o: "<li$2>$1</li></ul></li></ul></li>",
                  o_oo: "<li$2>$1<ol$3>",
                  // -- dl --
                  l_d: "<dt>$1</dt>",
                  d_l: "<dd>$1</dd>",
                  d_u: "<dd>$1</dd></dl><ul>",
                  d_o: "<dd>$1</dd></dl><ol>",
                  p_l: "<p>$1</p><dl>",
                  u_l: "<li$2>$1</li></ul><dl>",
                  o_l: "<li$2>$1</li></ol><dl>",
                  uu_l: "<li$2>$1</li></ul></li></ul><dl>",
                  uo_l: "<li$2>$1</li></ol></li></ul><dl>",
                  ou_l: "<li$2>$1</li></ul></li></ol><dl>",
                  oo_l: "<li$2>$1</li></ol></li></ol><dl>",
                  d_p: "<dd>$1</dd></dl>",
                  // -- table --
                  p_t: "<p>$1</p><table>",
                  p_r: "<p>$1</p></td></tr>",
                  p_c: "<p>$1</p></td>",
                  t_p: "</table><p>$1</p>",
                  r_r: "<tr><td>$1</td></tr>",
                  r_p: "<tr><td><p>$1</p>",
                  r_c: "<tr><td>$1</td>",
                  r_u: "<tr><td>$1<ul>",
                  c_p: "<td><p>$1</p>",
                  c_r: "<td>$1</td></tr>",
                  c_c: "<td>$1</td>",
//                  c_u: "<td>$1<ul>",
                  u_t: "<li$2>$1</li></ul><table>",
                  o_t: "<li$2>$1</li></ol><table>",
                  d_t: "<dd>$1</dd></dl><table>",
                  t_u: "</table><p>$1</p><ul>",
                  t_o: "</table><p>$1</p><ol>",
                  t_l: "</table><p>$1</p><dl>"
      };
      var type = { "0": "decimal-leading-zero",
                   "1": "decimal",
                   "a": "lower-alpha",
                   "A": "upper-alpha",
                   "i": "lower-roman",
                   "I": "upper-roman",
                   "g": "lower-greek" };

      var from = "", to = "", maxlen = Math.max(fromLevel.length, toLevel.length), sync = true, sectiontype = type[toLevel.charAt(toLevel.length-1)], transition;

      for (var i=0; i<maxlen; i++)
         if (fromLevel.charAt(i+1) != toLevel.charAt(i+1) || !sync || i == maxlen-1)
         {
            from += fromLevel.charAt(i) == undefined ? " " : fromLevel.charAt(i);
            to += toLevel.charAt(i) == undefined ? " " : toLevel.charAt(i);
            sync = false;
         }
      transition = (from + "_" + to).replace(/([01AIagi])/g, "o");
      return !trf[transition] ? ("?(" +  transition + ")")  // error string !
                              : trf[transition].replace(/\$2/, " class=\"" + fromLevel + "\"")
                                               .replace(/\$3/, !sectiontype ? "" : (" style=\"list-style-type:" + sectiontype + ";\""))
                                               .replace(/\$1/, content)
                                               .replace(/<p><\/p>/, "");
   },
   word:function(str){
   	  var segs = str.split("/");
	  var w = segs[segs.length-1];
	  return w.replace(/([a-z])([A-Z])/g,"$1 $2");
   }
}
