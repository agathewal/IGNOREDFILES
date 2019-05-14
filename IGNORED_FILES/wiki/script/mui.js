var ie = document.all ? true : false;
var textbox; 			// search input
var resultbox; 			// search result
var cl;				// keep track of click
var lang;			// the current language
var user;			// who is logged on
var uid;			// their id
var currenttitle;		// hoder for image uploads
var tab1 ;
var language;			// language object that holds the translations
var plugin_urls;		// hold loaded scripts here to not duplicate
var loaded_plugins;		// script is fully loaded
var unrendered;			// holder for multiple use of same plugin on page
var css_urls;			// hold loaded style sheets to not duplicate
var currentWidth;		// hold to check orientation changes
var haveindex = false;		// flag to control index loading

function loading(){
	
	plugin_urls = [];
	loaded_plugins = [];
	css_urls = [];
	
	lang='';
	tab1 = tabs();
	tab1.create(
		{
			name : "tabs",
			target:"tabdiv",
			width : "100%",
			height : "100%",
			info:[
				{label:"Contents" , content: "ctab1",foc : ""},
				{label:"Index" , content: "ctab2",foc : ""},
                {label:"Search" , content: "ctab3", foc : "keyword"},
                {label:"Help" , content: "ctab4", foc : ""}
			]
		}
	);
    	tab1.tabs[0].onclick = tabchange;
    	tab1.tabs[1].onclick = tabchange;
	
	//tree.handlerpath = '../';
	
	if (readCookie('lang')) {
		lang = readCookie('lang');
	}
	
	languages();

	cl="tree";
}

var updateLayout = function() {
  if (window.innerWidth != currentWidth) {
    currentWidth = window.innerWidth;
    var orient = (currentWidth == 320) ? "profile" : "landscape";
    document.body.setAttribute("orient", orient);
    window.scrollTo(0, 1);
  }
};

function tabchange(){
	if(tab1.selected==0)
		tree.updateNode(tree.activenode);

	if(tab1.selected==1 && !haveindex)
		getindex();
}

function printpage(){
    var content = document.getElementById('help').innerHTML;
    win = window.open("","mywindow","width=500,height=500");
    self.focus();
    win.document.open();
    win.document.write("<html><head><title>Help Page</title>");
    win.document.write("<link rel='stylesheet' type='text/css' href='"+url_dir+"/wiki/theme/default/css/ui.css'><link></head>");
	win.document.write(content + "</body></html>");	    
    win.document.close();
    win.print();
    win.close();
	
}

function dummy(){}

function getindex(){
	ajax(url_dir+"/wiki/handlers/getindex.php?lang="+lang,
	       null,
	       function(x){
	            var xmlDoc=x.responseXML.documentElement;
	            var tags = xmlDoc.getElementsByTagName('tag');
	            var target = document.getElementById('indexpane');
	            var html='';
	            for(var t=0;t<tags.length;t++){
	                var tag=tags[t];
	                var lab = tag.getAttribute('label');
	                html += "<div id='tag-"+lab.replace(' ','_')+"' class='indextag'>"+lab+"</div>";
	                var nodes = tag.getElementsByTagName('node');
	                for (var n = 0; n < nodes.length; n++) {
	                    var anode = nodes[n];
	                    var nlab = anode.getAttribute('label');
	                    var nid = anode.getAttribute('id');
	                    html += "<div style='margin-left:20px;'><a href='javascript:indexpage("+nid+",\""+lab+"\")'>"+nlab+"</a></div>";
	                }
	            }
	            
	            target.innerHTML = html;
		    haveindex = true;
	       },
	       "GET"
	);
}

function indexpage(id,lab){
        ajax(url_dir+"/wiki/handlers/getpage.php?id="+id+"&lang="+lang,
               null,
               function(x){
                    var html=x.responseText;    
                    html = Wiky.toHtml(html);                    
                    document.getElementById('help').innerHTML = html;
                    RedirectLocation("LocationAnchor", id, "#"+id);
               },
               "GET"
        );

		tree.updateNode(id);
		cl="index";	
}

function changelanguage(){
	var sel = document.getElementById('langsel');
	lang = sel.options[sel.selectedIndex].value;
	if(lang=='') return;
	var date = new Date();
	date.setTime(date.getTime()+(30*24*60*60*1000)); // expire in 30 days
	document.cookie = "lang="+lang+"; expires="+date.toGMTString();

	ajax(url_dir+"/wiki/language/"+lang+".json",
	 	 null,
		function(x){
			var obj = eval('(' + x.responseText + ')');
			tab1.setLabel(0,obj.tabs.contents);
	 		tab1.setLabel(1,obj.tabs.index);
			tab1.setLabel(2,obj.tabs.search);
			document.getElementById('searchtype').innerHTML = obj.tabs.type;
			document.getElementById('searchlist').value = obj.tabs.list;
			language = obj;
			tree.getTree(url_dir+'/wiki/handlers/gettree.php?lang='+lang,'tree');
			haveindex = false;
			if(tab1.selected==1)
				getindex();
					// clear search
			document.getElementById('keyword').value = '';
			document.getElementById('searchresult').innerHTML = '';
		}
    	);
	return false;
}

function languages(){
	
    ajax(url_dir+"/wiki/language/languages.json",
          null,
          function(x){
	            var obj = eval('(' + x.responseText + ')');
	            
	            var html = '';
	            for ( var i=0; i < obj.languages.length; i++ ){
	                 html+= "<option value='"+obj.languages[i].symbol+"'>"+obj.languages[i].text+"</option>" ;
	            }   
	            var lng = document.getElementById('lang');
	            lng.innerHTML = '<select id="langsel" onchange="changelanguage();">'+html+'</select>'; // ie hack
	            var l = document.getElementById('langsel');
	            if(lang=='') lang=obj.languages[0].symbol;
	            l.value = lang;
	            changelanguage();
          }
    );

	return false;
}
function getRequestObject() {
	var xmlHttp=null;
	try{
	    this.postmode = true;
		xmlHttp=new XMLHttpRequest();
	}catch (e){
	  // Internet Explorer
	  try{ 
		this.postmode = false;
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	  } catch (e){
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	  }
	return xmlHttp;		
}

// Resize

// Search functions

function search(){
	textbox = document.getElementById("keyword");
	resultbox = document.getElementById("searchresult");

    ajax(url_dir+"/wiki/handlers/search.php?lang="+lang,
          "search="+encodeURIComponent(textbox.value),
          function(x){
            var xmlDoc=x.responseXML.documentElement;
            var nodes = xmlDoc.getElementsByTagName("file");
            var list = '';
            for (var x = 0; x < nodes.length; x++) {
                var el = nodes[x];
                list += "<div class='list' onclick='searchClick(\""
                            +el.getAttribute('name')+"\")'>"
                            +el.getAttribute('title')+"</div>";
            }
            resultbox.innerHTML = list;
		  	
          }
    );

	return false;
}

function searchClick(file){
		ajax(url_dir+"/wiki/handlers/getpage.php?id="+file+"&lang="+lang,
		      null,
			  function(x){
                    var html=x.responseText;
                    html = Wiky.toHtml(html);                    
    
                    var words = textbox.value.split(" ");
                    for (var i=0;i<words.length;i++){
                        var word=words[i];
                        // highlight the word ignoring elements
                        pattern = "(" + word + ")(?=[^>]*<)";
                        html = html.replace(new RegExp(pattern,"gi"), "<span style='background:#ffff00;display:inline-block;'>$1</span>");
                    }
                    document.getElementById('help').innerHTML = html;
                    RedirectLocation("LocationAnchor", file, "#"+file);			  	
			  }
		);
		tree.updateNode(file);
		cl = "search";
}

function plugins(){ // this gets called after wiky conversion
	unrendered = []; // queue for plugins loading
	for (var p in Wiky.plugins) {
		var w = Wiky.plugins[p];
		
		unrendered.push(w); // automatically removed when rendered
		var src = 'plugins/'+w[0]+'/'+w[0]+'.js'; // path to plugin
		loadPlugin(src,w);  
		
		// not finished loading yet, remains queued
		if (!ArrayContains(loaded_plugins, src)) {
			continue;
		}
		
		renderplugin(w); // render specific instance of plugin
	}
}

function clone(o)
{
    var ClonedObject = function(){};
    ClonedObject.prototype = o;
    return new ClonedObject;
}

function renderplugin(w){
	// w[0] = plugin name, w[1] = unique id, w[2] = json parameters 
	try {
		var p = eval('(' + w[0].replace('-','_') + ')');
		var c = clone(p);	
		c.init(w[1],w[2]);
		c.render();
		ArrayRemove(unrendered,w);
		c=null;
	} catch (e) {}
}

function updateplugins(url){
	
	// this plugin has finished loading
    loaded_plugins.push(url);	
	
	// render all instances of the plugin?
	for(var p=unrendered.length-1;p>-1;p--){
		var w = unrendered[p];
		var src = 'plugins/'+w[0]+'/'+w[0]+'.js';
		
		if (!ArrayContains(loaded_plugins, src)) // different plugin finished loading
			continue;
			
		renderplugin(w); 
	}	
}

function loadPlugin(url,wp){
	// check to see if plugin has been called to load, if so no need to continue
	if (ArrayContains(plugin_urls,url)) 
		return;

	plugin_urls.push(url); // track loading calls
	
	// add plugin script
    var script = document.createElement("script");
	script.src = url;
	script.type="text/javascript";

	// once fully loaded, update instances
    if (script.readyState){  //IE
        script.onreadystatechange = function(){
            if (script.readyState == "loaded" ||
                    script.readyState == "complete"){
                script.onreadystatechange = null;
                updateplugins(url);
            }
        };
    } else {  //Others
        script.onload = function(){
			updateplugins(url);
        };
    }
	
	document.getElementsByTagName("head")[0].appendChild(script);
}


// Navigation functions

var last='';
function CheckForHash(){
	if(document.location.hash){ 
		var HashLocationName = document.location.hash;
		HashLocationName = HashLocationName.substring(1);
		if(last==HashLocationName) return;
		
		if(parseInt(HashLocationName) != tree.activenode) {
			if(tree.activenode != '')
				tree.click(HashLocationName);
		}
			
		var hashes = HashLocationName.split('-');
		if(hashes.length > 1){
			if(hashes[1].substring(0,2) != lang){
				var sel = document.getElementById('langsel');
				for(i=0;i<sel.length;i++){
					if(sel[i].value == hashes[1].substring(0,2)){
						sel.selectedIndex = i;
						
						break;
					}
				}
				lang=hashes[1].substring(0,2);
				changelanguage();
				return;
			}
		}

		anchor(HashLocationName);
		tab1.setTab(3);
		last=HashLocationName;
	}
	updateLayout();
}

function RenameAnchor(anchorid, anchorname){
	document.getElementById(anchorid).name = anchorname; //this renames the anchor
}

function RedirectLocation(anchorid, anchorname, HashName){
	var hashes = HashName.split('-');
	var anchors = anchorname.toString().split('#');
	var anch = anchors.length > 1 ? "#"+anchors[1] : '';
	RenameAnchor(anchorid, anchorname);
	document.location.hash = hashes[0]+"-"+lang+anch;
	
	plugins();
	anchor(HashName);

}

function anchor(aname){
	anum = aname.substring(1);
	var pane = document.body;
	pane.scrollTop =0;
	if(parseInt(anum) == anum) { return;} // nothing to do
	var anchs = anum.split('%23');
	if(anchs.length > 1){
		var tags = document.getElementsByName(anchs[1]); 
		if(tags.length > 0) pane.scrollTop = tags[0].offsetTop + 80; // should calculate but too lazy
	}

}

function forward(){
	history.go(1);
}

function back(){
	history.go(-1);
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function pageFromPath(path){

    ajax(url_dir+"/wiki/handlers/pageFromPath.php",
           'lang='+lang+"&path="+encodeURIComponent(path),
           function(x){
	            var id=x.responseText.replace(/^\s+|\s+$/g, ''); //trim
	
	            if(id > 0){
	                tree.click(id);
	            }else{
	                if(id==-1){
	                    edit(path);
	                }
	            }
           }
    );    
}

function ajax(handler,postparameters,callback,method){
	if(method==null)
	   method = "POST";
    var xmlHttp=getRequestObject();

    xmlHttp.onreadystatechange=function (){
        if (xmlHttp.readyState == 4) {
            callback(xmlHttp);
        }
    };
    
    xmlHttp.open(method,handler,true);
    if(method == "POST") xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.send(postparameters); 
}

function updatefldrfrm(rad){
	
    var divname = document.getElementById("fldrfrmname");
    var divap = document.getElementById("fldrfrmaddpaste");
    var divcmt = document.getElementById("fldrfrmcmt");
	
    if(rad.id=="faddpage"){
        divname.style.display="block";
        divap.style.display="block";
        divcmt.style.display="block";		
    }

    if(rad.id=="fremovefolder"){
        divname.style.display="none";
        divap.style.display="none";
        divcmt.style.display="block";       
    }

    if(rad.id=="fcut"){
        divname.style.display="none";
        divap.style.display="none";
        divcmt.style.display="none";       
    }

    if(rad.id=="fpaste"){
        divname.style.display="none";
        divap.style.display="block";
        divcmt.style.display="block";       
    }

    if(rad.id=="frename"){
        divname.style.display="block";
        divap.style.display="none";
        divcmt.style.display="block";       
    }

}

function ArrayContains(ar, value) {
	for (var i = 0;i < ar.length; i++) {
		if (ar[i] == value) {
			return true;
		}
	}	
	return false;
}

function ArrayRemove(ar, value) {
	for (var i = 0;i < ar.length; i++) {
		if (ar[i] == value) {
			ar.splice(i,1);
		}
	}	
}

var HashCheckInterval = setInterval("CheckForHash()", 250);
