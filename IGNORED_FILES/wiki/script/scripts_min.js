var VERSION="Wiki Web Help Version 0.3.8",ie=document.all?true:false,splitting=false,a,s,b,c,textbox,resultbox,bt,br,bl,bb,cl,lang,user,uid,ip,clipboard,imageclip,currenttitle,tab1,language,plugin_urls,loaded_plugins,unrendered,css_urls,_replaceContext=false,haveindex=false,help;
function loading(){lang="";CheckForHash();window.onresize=pack;document.onmousemove=mv;document.onmouseup=dmu;document.body.onmousedown=ContextMouseDown;document.body.oncontextmenu=treecontext;a=document.getElementById("pane_a");b=document.getElementById("pane_b");s=document.getElementById("splitter");c=document.getElementById("control");help=document.getElementById("help");pack();plugin_urls=[];loaded_plugins=[];css_urls=[];tab1=tabs();tab1.create({name:"tabs",target:"tabdiv",width:"100%",height:"100%",
info:[{label:"Contents",content:"ctab1",foc:""},{label:"Index",content:"ctab2",foc:"index"},{label:"Search",content:"ctab3",foc:"keyword"}]});tab1.tabs[0].onclick=tabchange;tab1.tabs[1].onclick=tabchange;languages();document.getElementById("adminmenu").style.display="none";user="anonymous";uid=0;cl="tree";imageclip=clipboard="";crumbs();getip();ajax("handlers/logincheck.php",null,function(e){e=eval("("+e.responseText+")");e.response=="ok"?loginresponse(e):logout()});var d=document.createElement("iframe");
d.setAttribute("id","printFrame");d.setAttribute("style","display:none");document.getElementsByTagName("body")[0].appendChild(d)}
function togglediff(d,e,f,g,h){var j=document.getElementById("revdiff_"+e);h=document.getElementById(h);if(j.style.display=="none"){j.style.display="block";h.className="histexpand";j.innerHTML==""&&e>0&&ajax("handlers/getdiff.php","to="+d+"&from="+e+"&page="+f+"&type="+g+"&lang="+lang,function(k){j.innerHTML=k.responseText})}else{j.style.display="none";h.className="histcollapse"}}function tabchange(){tab1.selected==0&&tree.updateNode(tree.activenode);tab1.selected==1&&!haveindex&&getindex()}
function currentStyle(){for(var d=document.getElementsByTagName("link"),e=d.length;e>=0;e--)if(d[e]&&d[e].getAttribute("href")!=null&&d[e].getAttribute("href").indexOf("ui.css")>-1)return d[e].getAttribute("href").split("/")[1]}
function printIframePage(){var d=document.getElementById("help").innerHTML,e=document.getElementById("printFrame");e.contentWindow.document.write("<html><head><title>Help Page</title>");e.contentWindow.document.write("<link rel='stylesheet' type='text/css' href='theme/"+currentStyle()+"/css/ui.css' /></head>");e.contentWindow.document.write("<body>"+d+"</body></html>");e.contentWindow.print()}
function printpage(){if((verOffset=navigator.userAgent.indexOf("Chrome"))!=-1)if(parseInt(navigator.userAgent.substring(verOffset+7))>12){printIframePage();return}var d=document.getElementById("help").innerHTML;win=window.open("","mywindow","width=500,height=500");win.document.open();win.document.write("<html><head><title>Help Page</title>");win.document.write("<link rel='stylesheet' type='text/css' href='theme/"+currentStyle()+"/css/ui.css' /></head>");win.document.write("<body>"+d+"</body></html>");
win.document.close();setTimeout(function(){showPrint(win)},500)}function showPrint(d){d.print();d.close()}function setclipboard(d){clipboard=d}function dummy(){}function revert(d){popup.show("revert.php?rev="+d,"revform","handlers/reverttorev.php?rev="+d+"&uid="+uid+"&user="+user+"&lang="+lang,"220px","revresponse","validaterev()")}function revresponse(d){d.response=="ok"?tree.click(d.node):alert(d.response)}
function validaterev(){if(document.getElementById("revcomment").value==""){alert("Please enter a comment(min 10 characters)!");return false}return true}function getrev(d,e,f){ajax("handlers/getrev.php","id="+d+"&rt="+encodeURIComponent(e),function(g){document.getElementById("help").innerHTML="<div style='float:right;'><a href='javascript:gethistory("+f+")'>"+language.cancel+"</a></div>"+Wiky.toHtml(g.responseText);plugins();document.getElementById("help").scrollTop=0},"POST")}
function gethistory(d){if(d==null)d=0;ajax("handlers/history.php","id="+tree.activenode+"&lang="+lang+"&offset="+d,function(e){document.getElementById("help").innerHTML=e.responseText;menucontrol("history")})}function getnodehistory(){ajax("handlers/nodehistory.php?lang="+lang,"id="+tree.activenode,function(d){d=d.responseText;document.getElementById("help").innerHTML=d})}
function profile(){popup.show("getprofile.php?id="+uid+"&lang="+lang,"profile","handlers/updateprofile.php?id="+uid,"260px","profileresponse","validateprofile()")}function profileresponse(d){d.response!="ok"&&alert(d.response)}function validateprofile(){document.getElementById("subscribe").value=document.getElementById("sub").checked;return true}
function indexkey(){var d=document.getElementById("indexpane"),e="id=['\"](tag-"+document.getElementById("index").value.replace(">","&gt;")+"[^('|\")]*?)['\"]";e=RegExp(e,"i").exec(d.innerHTML);if(e!=null)d.scrollTop=document.getElementById(e[1]).offsetTop}
function getindex(){ajax("handlers/getindex.php?lang="+lang,null,function(d){d=d.responseXML.documentElement.getElementsByTagName("tag");for(var e=document.getElementById("indexpane"),f="",g=0;g<d.length;g++){var h=d[g],j=h.getAttribute("label");j.replace(/\</g,"&lt;").replace(/\>/g,"&gt;").replace(/'/g,"&#39;").replace(/\&/g,"&amp;").replace(/"/g,"&quot;");f+="<div id='tag-"+j+"' class='indextag'>"+j+"</div>";h=h.getElementsByTagName("node");for(j=0;j<h.length;j++){var k=h[j],l=k.getAttribute("label");
k=k.getAttribute("id");f+="<div style='margin-left:20px;'><a href='javascript:indexpage("+k+")'>"+l+"</a></div>"}}e.innerHTML=f;haveindex=true;indexkey()},"GET")}function indexpage(d){ajax("handlers/getpage.php?id="+d+"&lang="+lang,null,function(e){e=e.responseText;e=Wiky.toHtml(e);document.getElementById("help").innerHTML=e;RedirectLocation("LocationAnchor",d,"#"+d)},"GET");tree.updateNode(d);cl="index"}
function previewcancel(){document.getElementById("imgpane").style.display="none";document.getElementById("editbar").style.display="block"}
function menucontrol(d){var e=document.getElementById("viewa"),f=document.getElementById("edita"),g=document.getElementById("historya"),h=document.getElementById("adminmenu");e.className=d=="view"?"menupage select":"menupage";f.className=d=="edit"?"menupage select":"menupage";g.className=d=="history"?"menupage select":"menupage";h.className=d=="admin"?"menupage select":"menupage"}
function editpage(d){ajax("handlers/editpage.php?id="+tree.activenode+(d==null?"":"&path="+d)+"&lang="+lang+"&clip="+encodeURIComponent(imageclip),null,function(e){e=e.responseText;document.getElementById("help").innerHTML=e;document.getElementById("edittext").focus();menucontrol("edit")},"GET")}
function loadscript(d,e){if(ArrayContains(loaded_plugins,d))e();else{var f=document.createElement("script");f.src=d;f.type="text/javascript";if(f.readyState)f.onreadystatechange=function(){if(f.readyState=="loaded"||f.readyState=="complete"){f.onreadystatechange=null;e();loaded_plugins.push(d)}};else f.onload=function(){loaded_plugins.push(d);e()};document.getElementsByTagName("head")[0].appendChild(f)}}function edit(d){loadscript("script/edit.js",function(){editpage(d)})}
function sysclipboard(d){imageclip=d;previewcancel("");insertimage();inlinepreview()}function ContextMouseDown(d){if(d==null)d=window.event;var e=d.target!=null?d.target:d.srcElement;if(d.button==2&&e.id.indexOf("treelabel")==0)_replaceContext=true}
function treecontext(d){if(d==null)d=window.event;d=d.target!=null?d.target:d.srcElement;if(_replaceContext){d=parseInt(d.id.substring(9));d!=tree.activenode&&tree.click(d);popup.show("foldermenu.php?target="+d+"&t=folder&lang="+lang+"&clip="+clipboard,"treemenu","handlers/folder.php?target="+d+"&lang="+lang+"&uid="+uid+"&clip="+clipboard,"220px","folderresponse","validateFolder()");return _replaceContext=false}return true}
function folderresponse(d){if(d.response=="ok"){if(clipboard=="")d.node==-1?tree.getTree("handlers/gettree.php?lang="+lang,"tree"):tree.getTree("handlers/gettree.php?lang="+lang,"tree",d.node)}else alert(d.response)}function tags(){popup.show("tagedit.php?id="+tree.activenode+"&lang="+lang,"tags","handlers/tagsave.php?id="+tree.activenode+"&uid="+uid+"&lang="+lang,"260px","tagresponse")}function tagresponse(){haveindex=false;gettags();tab1.selected==1&&getindex()}
function logout(){document.getElementById("loginmenu").style.display="block";document.getElementById("logoutmenu").style.display="none";document.getElementById("profilemenu").style.display="none";document.getElementById("registermenu").style.display="block";document.getElementById("adminmenu").style.display="none";document.getElementById("status").innerHTML="anonymous@";user="anonymous";uid=-1;getip();ajax("handlers/logout.php",null,function(){})}
function loginform(){popup.show("login.php?lang="+lang,"login","handlers/login.php","260px","loginresponse")}function registerform(){popup.show("register.php?lang="+lang,"register","handlers/register.php","260px","registerresponse","validateRegister()")}
function loginresponse(d){if(d.response=="ok"){document.getElementById("status").innerHTML="<span>"+d.user+"@"+d.ip+"</span>";document.getElementById("loginmenu").style.display="none";document.getElementById("logoutmenu").style.display="block";document.getElementById("profilemenu").style.display="block";document.getElementById("registermenu").style.display="none";if(d.level=="admin")document.getElementById("adminmenu").style.display="block";else document.getElementById("adminmenu").style.display=
"none";user=d.user;uid=d.uid;ip=d.ip;editable()}else alert(d.response)}function registerresponse(d){loginresponse(d)}function searchkey(d){d=d||window.event;if((d.keyCode||d.which)==13){search();tab1.setTab(2);document.getElementById("keyword").select()}}function getip(){ajax("handlers/ip.php",null,function(d){d=eval("("+d.responseText+")");document.getElementById("status").innerHTML="<span>"+user+"@"+d.ip+"</span>";ip=d.ip});return false}
function changelanguage(){var d=document.getElementById("langsel");lang=d.options[d.selectedIndex].value;if(lang!=""){d=new Date;d.setTime(d.getTime()+2592E6);ajax("language/"+lang+".json",null,function(e){e=eval("("+e.responseText+")");document.getElementById("logina").innerHTML=e.menu.login;document.getElementById("logouta").innerHTML=e.menu.logout;document.getElementById("registera").innerHTML=e.menu.register;document.getElementById("profilea").innerHTML=e.menu.profile;document.getElementById("edita").innerHTML=
e.menu.edit;document.getElementById("viewa").innerHTML=e.menu.view;document.getElementById("adminmenu").innerHTML=e.menu.admin;document.getElementById("tagsa").innerHTML=e.menu.edittags;document.getElementById("historya").innerHTML=e.menu.history;document.getElementById("historyna").innerHTML=e.treehistory;document.getElementById("searchlabel").innerHTML=e.tabs.search;tab1.setLabel(0,e.tabs.contents);tab1.setLabel(1,e.tabs.index);document.getElementById("indextype").innerHTML=e.tabs.type;tab1.setLabel(2,
e.tabs.search);language=e;tree.getTree("handlers/gettree.php?lang="+lang,"tree",tree.activenode);haveindex=false;tab1.selected==1&&getindex();crumbs();document.getElementById("keyword").value="";document.getElementById("searchresult").innerHTML=""});return false}}
function languages(){ajax("language/languages.json",null,function(d){d=eval("("+d.responseText+")");for(var e="",f=0;f<d.languages.length;f++)e+="<option value='"+d.languages[f].symbol+"'>"+d.languages[f].text+"</option>";document.getElementById("lang").innerHTML='<select id="langsel" onchange="changelanguage();">'+e+"</select>";e=document.getElementById("langsel");if(lang==""){lang=d.languages[0].symbol;changelanguage()}e.value=lang});return false}
function getRequestObject(){var d=null;try{this.postmode=true;d=new XMLHttpRequest}catch(e){try{this.postmode=false;d=new ActiveXObject("Msxml2.XMLHTTP")}catch(f){d=new ActiveXObject("Microsoft.XMLHTTP")}}return d}
function pack(){c.style.height="auto";a.style.overflow="hidden";if(c.currentStyle){bt=parseInt(c.currentStyle.borderTopWidth);br=parseInt(c.currentStyle.borderRightWidth);bb=parseInt(c.currentStyle.borderBottomWidth);bl=parseInt(c.currentStyle.borderLeftWidth)}else if(window.getComputedStyle){bt=parseInt(document.defaultView.getComputedStyle(c,null).getPropertyValue("border-top-width"));br=parseInt(document.defaultView.getComputedStyle(c,null).getPropertyValue("border-right-width"));bb=parseInt(document.defaultView.getComputedStyle(c,
null).getPropertyValue("border-bottom-width"));bl=parseInt(document.defaultView.getComputedStyle(c,null).getPropertyValue("border-left-width"))}b.style.width=Math.max(0,c.offsetWidth-a.offsetWidth-s.offsetWidth-bl-br)+"px"}var sp=300;function dc(){var d=s.offsetLeft>5?0:sp;sp=s.offsetLeft;a.style.width=Math.max(0,d)+"px";b.style.width=c.offsetWidth-a.offsetWidth-s.offsetWidth-bl-br+"px"}function md(){splitting=true;document.body.onselectstart=function(){return false}}
function mv(d){if(splitting){a.style.width=Math.max(0,(ie?event.clientX+document.body.scrollLeft:d.pageX)-c.offsetLeft-s.offsetWidth-5)+"px";b.style.width=c.offsetWidth-a.offsetWidth-s.offsetWidth-bl-br+"px"}}function mu(){splitting=false;document.body.onselectstart=function(){return true}}function dmu(d){if(!splitting)return true;s.offsetLeft>(ie?event.clientX+document.body.scrollLeft:d.pageX)&&mu(d)}
function search(){textbox=document.getElementById("keyword");resultbox=document.getElementById("searchresult");ajax("handlers/search.php?lang="+lang,"search="+encodeURIComponent(textbox.value),function(d){var e=d.responseXML.documentElement.getElementsByTagName("file"),f="";for(d=0;d<e.length;d++){var g=e[d];f+="<div class='list' onclick='searchClick(\""+g.getAttribute("name")+"\")'>"+g.getAttribute("title")+"</div>"}resultbox.innerHTML=f});return false}
function searchClick(d){ajax("handlers/getpage.php?id="+d+"&lang="+lang,null,function(e){e=e.responseText;e=Wiky.toHtml(e);for(var f=textbox.value.split(" "),g=0;g<f.length;g++){pattern="("+f[g]+")(?=[^>]*<)";e=e.replace(RegExp(pattern,"gi"),"<span style='background:yellow;display:inline-block;'>$1</span>")}document.getElementById("help").innerHTML=e;RedirectLocation("LocationAnchor",d,"#"+d)});tree.updateNode(d);cl="search"}
function plugins(){unrendered=[];for(var d in Wiky.plugins){var e=Wiky.plugins[d];unrendered.push(e);var f="plugins/"+e[0]+"/"+e[0]+".js";loadPlugin(f,e);ArrayContains(loaded_plugins,f)&&renderplugin(e)}}function clone(d){var e=function(){};e.prototype=d;return new e}function renderplugin(d){try{var e=eval("("+d[0].replace("-","_")+")"),f=clone(e);f.init(d[1],d[2]);f.render();ArrayRemove(unrendered,d)}catch(g){}}
function updateplugins(d){loaded_plugins.push(d);for(d=unrendered.length-1;d>-1;d--){var e=unrendered[d];ArrayContains(loaded_plugins,"plugins/"+e[0]+"/"+e[0]+".js")&&renderplugin(e)}}
function loadPlugin(d){if(!ArrayContains(plugin_urls,d)){plugin_urls.push(d);var e=document.createElement("script");e.src=d;e.type="text/javascript";if(e.readyState)e.onreadystatechange=function(){if(e.readyState=="loaded"||e.readyState=="complete"){e.onreadystatechange=null;updateplugins(d)}};else e.onload=function(){updateplugins(d)};document.getElementsByTagName("head")[0].appendChild(e)}}var last="";
function CheckForHash(){if(document.location.hash){var d=document.location.hash;d=d.substring(1);if(last!=d){parseInt(d)!=tree.activenode&&tree.activenode!=""&&tree.click(d);var e=d.split("-");if(e.length>1)if(e[1].substring(0,2)!=lang){d=document.getElementById("langsel");for(i=0;i<d.length;i++)if(d[i].value==e[1].substring(0,2)){d.selectedIndex=i;break}lang=e[1].substring(0,2);changelanguage();return}anchor(d);crumbs();last=d}}}function RenameAnchor(d,e){document.getElementById(d).name=e}
function RedirectLocation(d,e,f){var g=f.split("-"),h=e.toString().split("#");h=h.length>1?"#"+h[1]:"";RenameAnchor(d,e);document.location.hash=g[0]+"-"+lang+h;editable();plugins();anchor(f);menucontrol("view");gettags()}
function gettags(){ajax("handlers/gettags.php","id="+tree.activenode+"&lang="+lang,function(d){var e=d.responseText.split(","),f="";for(i=0;i<e.length;i++){var g=e[i];f+=", <a href='javascript:tagselect(\""+g+"\")'>"+g+"</a>"}document.getElementById("taglist").innerHTML=d.responseText!=""?language.menu.tags+" : "+f.substring(2):""})}function tagselect(d){document.getElementById("index").value=d;tab1.setTab(1);haveindex?indexkey():getindex()}
function anchor(d){anum=d.substring(1);d=document.getElementById("help");d.scrollTop=0;if(parseInt(anum)!=anum){var e=anum.split("#");if(e.length>1){e=document.getElementsByName(e[1]);if(e.length>0)d.scrollTop=e[0].offsetTop}}}
function editable(){ajax("handlers/editable.php","id="+tree.activenode+"&lang="+lang,function(d){var e=document.getElementById("edita"),f=document.getElementById("tagsm");if(d.responseText=="1"||d.responseText=="2"){e.innerHTML=language.menu.edit;f.style.display="block"}else{if(language!=null)e.innerHTML=language.sourceview;f.style.display="none"}})}function forward(){history.go(1)}function back(){history.go(-1)}
function validateRegister(){var d=document.getElementById("user");if(d.value==""){alert("Please provide a user name");d.focus();d.select();return false}d=document.getElementById("pass");if(d.value==""){alert("Please provide a password");d.focus();d.select();return false}d=document.getElementById("confirm");if(d.value==""||d.value!=document.getElementById("pass").value){alert("Password and Confirmation must match.");d.focus();d.select();return false}d=document.getElementById("email");if(d.value==""){alert("Please provide a valid email address");
d.focus();d.select();return false}if(!/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(d.value)){alert("Please provide a valid email address");d.focus();d.select();return false}return true}
function validateFolder(){var d=document.getElementById("fname"),e=document.getElementById("faddpage"),f=document.getElementById("fpaste"),g=document.getElementById("fremovefolder"),h=document.getElementById("frename"),j=document.getElementById("fcut"),k=document.getElementById("commentf");if(e.checked||h.checked)if(d.value==""){alert("Please enter a name");d.focus();d.select();return false}if(g.checked&&user=="anonymous"){alert("You do not have permission to remove folders!");return false}if(g.checked&&
k.value.length<10){alert("Please enter reason for deletion (min 10 characters)");k.focus();k.select();return false}d=document.getElementById("action");if(e.checked)d.value="addpage";if(f.checked){if(clipboard==""){alert("Clipboard is empty!");return false}d.value="paste"}if(g.checked)d.value="remove";if(h.checked)d.value="rename";if(j.checked){d.value="cut";clipboard=tree.activenode}else if(d.value=="paste")clipboard="";e=document.getElementById("position");if(document.getElementById("before").checked)e.value=
"before";if(document.getElementById("after").checked)e.value="after";if(document.getElementById("in").checked)e.value="in";return true}function pageFromPath(d){ajax("handlers/pageFromPath.php","lang="+lang+"&path="+encodeURIComponent(d),function(e){e=e.responseText.replace(/^\s+|\s+$/g,"");if(e>0)tree.click(e);else e==-1&&edit(d)})}
function adminpage(){loadscript("script/admin.js",function(){ajax("admin/adminpage.php","lang="+lang+"&page="+tree.activenode,function(d){document.getElementById("help").innerHTML=d.responseText;menucontrol("admin")})})}
function ajax(d,e,f,g,h){if(g==null)g="POST";if(h==null)h=true;if(h==false)d=d+"&dummy="+(new Date).getTime();var j=getRequestObject();j.onreadystatechange=function(){j.readyState==4&&f(j)};j.open(g,d,true);g=="POST"&&j.setRequestHeader("Content-type","application/x-www-form-urlencoded");j.send(e)}
function crumbs(){var d=tree.crumbs;d.length==0&&setTimeout("crumbs()",50);for(var e="",f=0;f<d.length;f++){var g=d[f];if(f>0)e+="<div class='crumbsep'>:</div>";e+="<div class='"+(f==d.length-1?"endcrumb":"crumb")+"'"+(f==d.length-1?"":"onclick='tree.click(\""+g.ref+"\")'")+">"+g.label+"</div>"}document.getElementById("crumbs").innerHTML=e}
function updatefldrfrm(d){var e=document.getElementById("fldrfrmname"),f=document.getElementById("fldrfrmaddpaste"),g=document.getElementById("fldrfrmcmt");if(d.id=="faddpage"){e.style.display="block";f.style.display="block";g.style.display="block"}if(d.id=="fremovefolder"){e.style.display="none";f.style.display="none";g.style.display="block"}if(d.id=="fcut"){e.style.display="none";f.style.display="none";g.style.display="none"}if(d.id=="fpaste"){e.style.display="none";f.style.display="block";g.style.display=
"block"}if(d.id=="frename"){e.style.display="block";f.style.display="none";g.style.display="block"}}function ArrayContains(d,e){for(var f=0;f<d.length;f++)if(d[f]==e)return true;return false}function ArrayRemove(d,e){for(var f=0;f<d.length;f++)d[f]==e&&d.splice(f,1)}var HashCheckInterval=setInterval("CheckForHash()",250);var popup={postmode:null,showing:false,blocker:null,show:function(d,e,f,g,h,j){if(!this.showing){if(this.blocker==null){this.blocker=document.createElement("div");this.blocker.style.position="absolute";this.blocker.style.left=0;this.blocker.style.right=0;this.blocker.style.top=0;this.blocker.style.bottom=0;this.blocker.style.background="#ccc";this.blocker.id="blocker";document.body.appendChild(this.blocker)}this.blocker.onmousedown=function(){popup.closeform(e)};this.blocker.style.display="block";
var k=document.getElementById(e),l=j==null?"":',"'+j+'"';ajax(d,null,function(n){k.innerHTML="<form  onsubmit='popup.submitform(this,\""+f+'","'+e+'","'+h+'"'+l+");return false;'><div style='width:"+g+";position:absolute;'><div class='popup'><div style='float:right;'><a href='javascript:popup.closeform(\""+e+"\");' class='close'>x</a></div><div style='clear:both;'></div>"+n.responseText+"</div></div></form>";k.style.display="block"});this.showing=true}},closeform:function(d){document.getElementById(d).style.display=
"none";this.blocker.style.display="none";this.showing=false},submitform:function(d,e,f,g,h){if(h!=null)if(!eval(h))return false;h="";var j=document.getElementById(f);for(i=0;i<d.elements.length;i++)if(d.elements[i].name!=""){h+=+i==0?"":"&";h+=d.elements[i].name+"="+encodeURIComponent(d.elements[i].value)}var k=this;ajax(e,h,function(l){if(eval("("+l.responseText+")").response=="ok"){j.style.display="none";k.showing=false;k.blocker.style.display="none"}eval(""+g+"("+l.responseText+")")})}};var tree={n:0,target:null,home:"",nodes:[],crumbs:[],activenode:"",activetitle:"",highlight:"#ccccff",normal:"#ffffff",updateNode:function(d){if(d&&this.nodes.length>0){d=parseInt(d);this.crumbs=[];this.expandnode(this.nodes[d]);this.crumbs.reverse();if(d in this.nodes){if(this.activenode!="")document.getElementById(this.nodes[this.activenode].id).style.backgroundColor=this.normal;this.activenode=d;this.activetitle=this.nodes[d].label;d=document.getElementById(this.nodes[d].id);d.style.backgroundColor=
this.highlight;var e=d.parentNode.parentNode,f=document.getElementById("toc");if(e.offsetTop-f.scrollTop+d.offsetHeight>f.offsetHeight)f.scrollTop=e.offsetTop+d.offsetHeight-f.offsetHeight;if(f.scrollTop>e.offsetTop)f.scrollTop=e.offsetTop}}},click:function(d){handler="handlers/getpage.php?id="+parseInt(d)+"&lang="+lang;ajax(handler,null,function(e){e=e.responseText;document.getElementById("help").innerHTML=Wiky.toHtml(e);RedirectLocation("LocationAnchor",d,"#"+d)},"GET");this.updateNode(d);cl="tree"},
getTree:function(d,e,f){this.nodes=[];this.home="";this.activenode=f==null?"":f;this.target=document.getElementById(e);var g=this;ajax(d,null,function(h){h=h.responseXML.documentElement;g.target.innerHTML="";tree.parseTree(h,g.target,0);if(document.location.hash)g.activenode==""&&!document.location.hash.substring(1)in g.nodes?tree.click(tree.home):tree.click(document.location.hash.substring(1));else tree.click(tree.home)},"GET",false)},parseTree:function(d,e,f){for(var g=0;g<d.childNodes.length;g++){var h=
d.childNodes[g];if(h.nodeName=="folder"){if(this.home=="")this.home=h.getAttribute("ref");this.n++;var j=document.createElement("div");j.className="node";var k="<div style='overflow:hidden;width:1000px;'><div class='toggle closed' id='toggle"+h.getAttribute("ref")+"' onclick='tree.togglenode("+h.getAttribute("ref")+")'></div><div id='treelabel"+h.getAttribute("ref")+"' class='closelabel' onclick='tree.click(\""+h.getAttribute("ref")+"\");' onselectstart='return false;' ondblclick='tree.togglenode("+
h.getAttribute("ref")+")'>"+h.getAttribute("label")+"</div></div>",l=document.createElement("div");l.id="treenode"+h.getAttribute("ref");j.innerHTML=k;j.appendChild(l);l.style.display="none";if(h.getAttribute("ref")!="")this.nodes[h.getAttribute("ref")]={ref:h.getAttribute("ref"),id:"treelabel"+h.getAttribute("ref"),label:h.getAttribute("label"),pid:e.id,owner:f};this.parseTree(h,l,h.getAttribute("ref"));e.appendChild(j)}if(h.nodeName=="leaf"){if(this.home=="")this.home=h.getAttribute("ref");this.n++;
j=document.createElement("div");j.innerHTML="<div style='overflow:hidden;width:1000px;'><div id='treelabel"+h.getAttribute("ref")+"' class='leaf' onclick='tree.click(\""+h.getAttribute("ref")+"\");' onselectstart='return false;'>"+h.getAttribute("label")+"</div></div>";j.getAttribute("ref");e.appendChild(j);if(h.getAttribute("ref")!="")this.nodes[h.getAttribute("ref")]={id:"treelabel"+h.getAttribute("ref"),label:h.getAttribute("label"),pid:e.id,owner:f}}}},togglenode:function(d){var e=document.getElementById("treenode"+
d),f=document.getElementById("treelabel"+d);d=document.getElementById("toggle"+d);if(e.style.display=="block"){e.style.display="none";f.className="closelabel";d.className="toggle closed"}else{e.style.display="block";f.className="nodelabel";d.className="toggle open"}},expandnode:function(d){if(d){this.crumbs.push(d);if(d.owner!=0){var e=document.getElementById(d.pid),f=d.pid.substring(8),g=document.getElementById("treelabel"+f);f=document.getElementById("toggle"+f);e.style.display="block";g.className=
"nodelabel";f.className="toggle open";this.expandnode(this.nodes[d.owner])}}}};var tabs=function(){return{info:null,target:null,name:null,height:null,width:null,tabs:[],selected:0,setLabel:function(d,e){this.tabs[d].innerHTML=e},setTab:function(d){for(var e=0;e<this.info.length;e++){var f=this.tabs[e],g=document.getElementById(this.info[e].content);if(d==e){f.className="tabon";g.style.display="block";this.info[e].foc!=""&&document.getElementById(this.info[e].foc).focus()}else{g.style.display="none";f.className="taboff"}}},create:function(d){this.target=document.getElementById(d.target);
this.info=d.info;this.name=d.name;this.height=d.height;this.width=d.width;this.target.style.height=this.height;for(d=0;d<this.info.length;d++){var e=document.createElement("div");e.setAttribute("id",this.name+"tab"+d);e.className="taboff";e.innerHTML=this.info[d].label;var f=this;e.onmouseup=function(){for(var h=0;h<f.info.length;h++){var j=f.tabs[h],k=document.getElementById(f.info[h].content);if(j==this){j.className="tabon";k.style.display="block";f.info[h].foc!=""&&document.getElementById(f.info[h].foc).focus();
f.selected=h}else{k.style.display="none";j.className="taboff"}}};e.style.height="22px";this.tabs[d]=e;this.target.appendChild(e);var g=document.getElementById(this.info[d].content);g.style.left=0;g.style.display="none";g.style.top=e.offsetHeight-1+"px"}this.tabs[0].className="tabon";document.getElementById(this.info[0].content).style.display="block"}}};var Wiky={version:0.95,blocks:null,plugins:null,rules:{all:["Wiky.rules.pre","Wiky.rules.nonwikiblocks","Wiky.rules.wikiblocks"],pre:[{rex:/(\r?\n)/g,tmplt:"\u00b6"}],post:[{rex:/(^\xB6)|(\xB6$)/g,tmplt:""},{rex:/@([0-9]+)@/g,tmplt:function(d,e){return Wiky.restore(e)}},{rex:/\xB6/g,tmplt:"\n"}],nonwikiblocks:[{rex:/\\([%])/g,tmplt:function(d,e){return Wiky.store(e)}},{rex:/\[%(.*?)%\]/g,tmplt:function(d,e){return Wiky.store("<pre>"+Wiky.apply(e,Wiky.rules.code)+"</pre>")+"[p:"}}],wikiblocks:["Wiky.rules.nonwikiinlines",
"Wiky.rules.escapes",{rex:/(?:^|\xB6)(={1,6})(.*?)[=]*(?=\xB6|$)/g,tmplt:function(d,e,f){d=e.length;return":p]\u00b6<h"+d+">"+f+"</h"+d+">\u00b6[p:"}},{rex:/(?:^|\xB6)[-]{4}(?:\xB6|$)/g,tmplt:"\u00b6<hr/>\u00b6"},{rex:/\\\\([ \xB6])/g,tmplt:"<br/>$1"},{rex:/`(?:\{(.*?)\})([^`]+)`/g,tmplt:function(d,e,f){return Wiky.store("<span"+Wiky.style(e)+">"+f+"</span>")}},{rex:/(^|\xB6)([*01aAiIg]*[\.*])[ ]/g,tmplt:function(d,e,f){d=f.replace(/([*])/g,"u").replace(/([\.])/,"");return":"+d+"]"+e+"["+d+":"}},
{rex:/(?:^|\xB6);[ ](.*?):[ ]/g,tmplt:"\u00b6:l][l:$1:d][d:"},{rex:/\[(?:\{([^}]*)\})?(?:\(([^)]*)\))?\"/g,tmplt:function(d,e,f){return":p]<blockquote"+Wiky.attr(f,"cite",0)+Wiky.attr(f,"title",1)+Wiky.style(e)+">[p:"}},{rex:/\"\]/g,tmplt:":p]</blockquote>[p:"},{rex:/\[(\{[^}]*\})?\|/g,tmplt:":t]$1[r:"},{rex:/\|\]/g,tmplt:":r][t:"},{rex:/\|\xB6[ ]?\|/g,tmplt:":r]\u00b6[r:"},{rex:/\|/g,tmplt:":c][c:"},{rex:/^(.*)$/g,tmplt:"[p:$1:p]"},{rex:/(([\xB6])([ \t\f\v\xB6]*?)){2,}/g,tmplt:":p]$1[p:"},{rex:/\[([01AIacdgilprtu]+)[:](.*?)[:]([01AIacdgilprtu]+)\]/g,
tmplt:function(d,e,f,g){return Wiky.sectionRule(e==undefined?"":e,"",Wiky.apply(f,Wiky.rules.wikiinlines),!g?"":g)}},{rex:/\[[01AIacdgilprtu]+[:]|[:][01AIacdgilprtu]+\]/g,tmplt:""},{rex:/<td>(?:([0-9]*)[>])?([ ]?)(.*?)([ ]?)<\/td>/g,tmplt:function(d,e,f,g,h){return"<td"+(e?' colspan="'+e+'"':"")+(f==" "?' style="text-align:'+(f==h?"center":"right")+';"':h==" "?' style="text-align:left;"':"")+">"+f+g+h+"</td>"}},{rex:/<(p|table|h[1-6])>(?:\xB6)?(?:\{(.*?)\})/g,tmplt:function(d,e,f){return Wiky.store("<"+
e+Wiky.style(f)+">")}},{rex:/<p>([ \t\f\v\xB6]*?)<\/p>/g,tmplt:"$1"},"Wiky.rules.shortcuts"],nonwikiinlines:[{rex:/%(?:\{([^}]*)\})?(?:\(([^)]*)\))?(.*?)%/g,tmplt:function(d,e,f,g){return Wiky.store("<code"+(f?' lang="x-'+Wiky.attr(f)+'"':"")+Wiky.style(e)+">"+Wiky.apply(g,f?Wiky.rules.lang[Wiky.attr(f)]:Wiky.rules.code)+"</code>")}},{rex:/%(.*?)%/g,tmplt:function(){return Wiky.store("<code>"+Wiky.apply($2,Wiky.rules.code)+"</code>")}}],wikiinlines:[{rex:/\*([^*]+)\*/g,tmplt:"<strong>$1</strong>"},
{rex:/\(\+(.+?)\+\)/g,tmplt:"<ins>$1</ins>"},{rex:/(((http(s?))\:\/\/)?[A-Za-z0-9\._\/~\-: ]+\.(?:png|jpg|jpeg|gif|bmp))/gi,tmplt:function(d,e){return Wiky.store('<img src="'+e+'" alt="'+e+'"/>')}},{rex:/_([^_]+)_/g,tmplt:"<em>$1</em>"},{rex:/\^([^^]+)\^/g,tmplt:"<sup>$1</sup>"},{rex:/~([^~]+)~/g,tmplt:"<sub>$1</sub>"},{rex:/\(-(.+?)-\)/g,tmplt:"<del>$1</del>"},{rex:/\?([^ \t\f\v\xB6]+)\((.+)\)\?/g,tmplt:'<abbr title="$2">$1</abbr>'},{rex:/\[(?:\{([^}]*)\})?[Ii]ma?ge?\:([^ ,\]]*)(?:[, ]([^\]]*))?\]/g,
tmplt:function(d,e,f,g){return Wiky.store("<img"+Wiky.style(e)+' src="'+f+'" alt="'+(g?g:f)+'" title="'+(g?g:f)+'"/>')}},{rex:/#\]/g,tmplt:"</div>"},{rex:/\[#\s*([^ ,]+)[, ]([^\]]*);/g,tmplt:function(d,e,f){return Wiky.plug(e,f)}},{rex:/\[(\/[a-zA-z0-9_\u00A1-\uFFFF\-\/]+)\]/g,tmplt:function(d,e){return Wiky.store("<a href=\"javascript:pageFromPath('"+e+"');\">"+Wiky.word(e)+"</a>")}},{rex:/\[(http(s?)\:\/\/[^ ,]+)[, ]([^\]]*)\]/g,tmplt:function(d,e,f,g){return Wiky.store("<a class='extlink' href=\""+
e+f+'">'+g+"</a>")}},{rex:/\[([^ ,]+)[, ]([^\]]*)\]/g,tmplt:function(d,e,f){return Wiky.store('<a href="'+e+'">'+f+"</a>")}},{rex:/((mailto\:|javascript\:|(news|file|(ht|f)tp(s?))\:\/\/)[A-Za-z0-9\.:_\/~%\-+&#?!=()@\x80-\xB5\xB7\xFF]+)/g,tmplt:'<a href="$1">$1</a>'}],escapes:[{rex:/\\([|*_~\^])/g,tmplt:function(d,e){return Wiky.store(e)}},{rex:/\\&/g,tmplt:"&amp;"},{rex:/\\>/g,tmplt:"&gt;"},{rex:/\\</g,tmplt:"&lt;"}],shortcuts:[{rex:/---/g,tmplt:"&#8212;"},{rex:/--/g,tmplt:"&#8211;"},{rex:/[\.]{3}/g,
tmplt:"&#8230;"},{rex:/<->/g,tmplt:"&#8596;"},{rex:/<-/g,tmplt:"&#8592;"},{rex:/->/g,tmplt:"&#8594;"}],code:[{rex:/&/g,tmplt:"&amp;"},{rex:/</g,tmplt:"&lt;"},{rex:/>/g,tmplt:"&gt;"}],lang:{}},toHtml:function(d){Wiky.blocks=[];Wiky.plugins=[];return Wiky.apply(Wiky.sanitize(Wiky.apply(d,Wiky.rules.all)),Wiky.rules.post)},apply:function(d,e){if(d&&e)for(var f in e)d=typeof e[f]=="string"?Wiky.apply(d,eval(e[f])):d.replace(e[f].rex,e[f].tmplt);return d},plug:function(d,e){var f=eval("("+e+")"),g=d+"_"+
Wiky.plugins.length;Wiky.plugins.push([d,g,f]);return"<div id='"+g+"'>"},sanitize:function(d){var e=document.createElement("div");e.innerHTML=d;d=e.getElementsByTagName("*");for(var f=d.length-1;f>-1;f--){var g=d.item(f),h=g.tagName.toLowerCase();if(h=="object"||h=="iframe"||h=="embed")g.parentNode.removeChild(g);else{if(g.style.position=="absolute"||g.style.position=="relative")g.style.position="static";h=g.attributes;for(var j=h.length-1;j>-1;j--){var k=h[j].name;k.indexOf("on")==0&&g.removeAttribute(k)}}}return e.innerHTML},
store:function(d,e){return e?"@"+(Wiky.blocks.push(d)-1)+"@":"@"+(Wiky.blocks.push(d.replace(/@([0-9]+)@/g,function(f,g){return Wiky.restore(g)}))-1)+"@"},restore:function(d){return Wiky.blocks[d]},attr:function(d,e,f){return(d=d&&d.split(",")[f||0])?e?" "+e+'="'+d+'"':d:""},hasAttr:function(d,e){return RegExp(e+"=").test(d)},attrVal:function(d,e){return d.replace(RegExp("^.*?"+e+'="(.*?)".*?$'),"$1")},invAttr:function(d,e){var f=[];for(var g in e)d.indexOf(e[g]+"=")>=0&&f.push(d.replace(RegExp("^.*?"+
e[g]+'="(.*?)".*?$'),"$1"));return f.length?"("+f.join(",")+")":""},style:function(d){d=d&&d.split(/,|;/);var e,f="";for(var g in d)if(d[g]!=null){e=d[g].split(":");f+=e[0]==">"?"margin-left:4em;":e[0]=="<"?"margin-right:4em;":e[0]==">>"?"float:right;":e[0]=="<<"?"float:left;":e[0]=="="?"display:block;margin:0 auto;":e[0]=="_"?"text-decoration:underline;":e[0]=="b"?"border:solid 1px;":e[0]=="c"?"color:"+e[1]+";":e[0]=="C"?"background:"+e[1]+";":e[0]=="w"?"width:"+e[1]+";":e[0]+":"+e[1]+";"}return f?
' style="'+f+'"':""},invStyle:function(d){d=(d=/style=/.test(d)?d.replace(/^.*?style=\"(.*?)\".*?$/,"$1"):"")&&d.split(";");var e,f=[];for(var g in d){e=d[g].split(":");if(e[0]=="margin-left"&&e[1]=="4em")f.push(">");else if(e[0]=="margin-right"&&e[1]=="4em")f.push("<");else if(e[0]=="float"&&e[1]=="right")f.push(">>");else if(e[0]=="float"&&e[1]=="left")f.push("<<");else if(e[0]=="margin"&&e[1]=="0 auto")f.push("=");else if(e[0]=="text-decoration"&&e[1]=="underline")f.push("_");else if(e[0]=="border"&&
e[1]=="solid 1px")f.push("b");else if(e[0]=="color")f.push("c:"+e[1]);else if(e[0]=="background")f.push("C:"+e[1]);else if(e[0]=="width")f.push("w:"+e[1]);else e[0]&&f.push(e[0]+":"+e[1])}return f.length?"{"+f.join(",")+"}":""},sectionRule:function(d,e,f,g){e={p_p:"<p>$1</p>",p_u:"<p>$1</p><ul$3>",p_o:"<p>$1</p><ol$3>",u_p:"<li$2>$1</li></ul>",u_c:"<li$2>$1</li></ul></td>",u_r:"<li$2>$1</li></ul></td></tr>",uu_p:"<li$2>$1</li></ul></li></ul>",uo_p:"<li$2>$1</li></ol></li></ul>",uuu_p:"<li$2>$1</li></ul></li></ul></li></ul>",
uou_p:"<li$2>$1</li></ul></li></ol></li></ul>",uuo_p:"<li$2>$1</li></ol></li></ul></li></ul>",uoo_p:"<li$2>$1</li></ol></li></ol></li></ul>",u_u:"<li$2>$1</li>",uu_u:"<li$2>$1</li></ul></li>",uo_u:"<li$2>$1</li></ol></li>",uuu_u:"<li$2>$1</li></ul></li></ul></li>",uou_u:"<li$2>$1</li></ul></li></ol></li>",uuo_u:"<li$2>$1</li></ol></li></ul></li>",uoo_u:"<li$2>$1</li></ol></li></ol></li>",u_uu:"<li$2>$1<ul$3>",u_o:"<li$2>$1</li></ul><ol$3>",uu_o:"<li$2>$1</li></ul></li></ul><ol$3>",uo_o:"<li$2>$1</li></ol></li></ul><ol$3>",
uuu_o:"<li$2>$1</li></ul></li></ul></li></ul><ol$3>",uou_o:"<li$2>$1</li></ul></li></ol></li></ul><ol$3>",uuo_o:"<li$2>$1</li></ol></li></ul></li></ul><ol$3>",uoo_o:"<li$2>$1</li></ol></li></ol></li></ul><ol$3>",u_uo:"<li$2>$1<ol$3>",o_p:"<li$2>$1</li></ol>",oo_p:"<li$2>$1</li></ol></li></ol>",ou_p:"<li$2>$1</li></ul></li></ol>",ooo_p:"<li$2>$1</li></ol></li></ol>",ouo_p:"<li$2>$1</li></ol></li></ul></li></ol>",oou_p:"<li$2>$1</li></ul></li></ol></li></ol>",ouu_p:"<li$2>$1</li></ul></li></ul></li></ol>",
o_u:"<li$2>$1</li></ol><ul$3>",oo_u:"<li$2>$1</li></ol></li></ol><ul$3>",ou_u:"<li$2>$1</li></ul></li></ol><ul$3>",ooo_u:"<li$2>$1</li></ol></li></ol></li></ol><ul$3>",ouo_u:"<li$2>$1</li></ol></li></ul></li></ol><ul$3>",oou_u:"<li$2>$1</li></ul></li></ol></li></ol><ul$3>",ouu_u:"<li$2>$1</li></ul></li></ul></li></ol><ul$3>",o_ou:"<li$2>$1<ul$3>",o_o:"<li$2>$1</li>",oo_o:"<li$2>$1</li></ol></li>",ou_o:"<li$2>$1</li></ul></li>",ooo_o:"<li$2>$1</li></ol></li></ol></li>",ouo_o:"<li$2>$1</li></ol></li></ul></li>",
oou_o:"<li$2>$1</li></ul></li></ol></li>",ouu_o:"<li$2>$1</li></ul></li></ul></li>",o_oo:"<li$2>$1<ol$3>",l_d:"<dt>$1</dt>",d_l:"<dd>$1</dd>",d_u:"<dd>$1</dd></dl><ul>",d_o:"<dd>$1</dd></dl><ol>",p_l:"<p>$1</p><dl>",u_l:"<li$2>$1</li></ul><dl>",o_l:"<li$2>$1</li></ol><dl>",uu_l:"<li$2>$1</li></ul></li></ul><dl>",uo_l:"<li$2>$1</li></ol></li></ul><dl>",ou_l:"<li$2>$1</li></ul></li></ol><dl>",oo_l:"<li$2>$1</li></ol></li></ol><dl>",d_p:"<dd>$1</dd></dl>",p_t:"<p>$1</p><table>",p_r:"<p>$1</p></td></tr>",
p_c:"<p>$1</p></td>",t_p:"</table><p>$1</p>",r_r:"<tr><td>$1</td></tr>",r_p:"<tr><td><p>$1</p>",r_c:"<tr><td>$1</td>",r_u:"<tr><td>$1<ul>",c_p:"<td><p>$1</p>",c_r:"<td>$1</td></tr>",c_c:"<td>$1</td>",u_t:"<li$2>$1</li></ul><table>",o_t:"<li$2>$1</li></ol><table>",d_t:"<dd>$1</dd></dl><table>",t_u:"</table><p>$1</p><ul>",t_o:"</table><p>$1</p><ol>",t_l:"</table><p>$1</p><dl>"};for(var h="",j="",k=Math.max(d.length,g.length),l=true,n={"0":"decimal-leading-zero","1":"decimal",a:"lower-alpha",A:"upper-alpha",
i:"lower-roman",I:"upper-roman",g:"lower-greek"}[g.charAt(g.length-1)],m=0;m<k;m++)if(d.charAt(m+1)!=g.charAt(m+1)||!l||m==k-1){h+=d.charAt(m)==undefined?" ":d.charAt(m);j+=g.charAt(m)==undefined?" ":g.charAt(m);l=false}g=(h+"_"+j).replace(/([01AIagi])/g,"o");return!e[g]?"?("+g+")":e[g].replace(/\$2/,' class="'+d+'"').replace(/\$3/,!n?"":' style="list-style-type:'+n+';"').replace(/\$1/,f).replace(/<p><\/p>/,"")},word:function(d){d=d.split("/");return d[d.length-1].replace(/([a-z])([A-Z])/g,"$1 $2")}};
