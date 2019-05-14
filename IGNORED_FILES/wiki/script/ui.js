var VERSION = "Wiki Web Help Version 0.3.9";
var ie = document.all ? true : false;
var splitting = false;
var a; 					// left split pane
var s; 					// splitter
var b; 					// right pane
var c; 					// split pane control
var textbox; 			// search input
var resultbox; 			// search result
var bt; 				// border top
var br; 				// right
var bl; 				// left
var bb; 				// bottom
var cl;					// keep track of click
var lang;				// the current language
var user;				// who is logged on
var uid;				// their id
var ip;					// the current user's ip address
var clipboard;			// for cutting and pasting of folders
var imageclip;			// for pasting images
var currenttitle;		// hoder for image uploads
var tab1 ;
var language;			// language object that holds the translations
var plugin_urls;		// hold loaded scripts here to not duplicate
var loaded_plugins;		// script is fully loaded
var unrendered;			// holder for multiple use of same plugin on page
var css_urls;			// hold loaded style sheets to not duplicate
var _replaceContext = false; 
var haveindex = false;		// flag to control index loading
var help;

function loading(){


	lang='fr';
	CheckForHash();
	window.onresize = pack;
	document.onmousemove=mv;
	document.onmouseup = dmu;
	document.body.onmousedown = ContextMouseDown;
	document.body.oncontextmenu = treecontext;

	a = document.getElementById("pane_a");
	b = document.getElementById("pane_b");
	s = document.getElementById("splitter");
	c = document.getElementById("control");
	help = document.getElementById("help");

	pack();

	plugin_urls = [];
	loaded_plugins = [];
	css_urls = [];
	
	tab1 = tabs();
	tab1.create(
		{
			name : "tabs",
			target:"tabdiv",
			width : "100%",
			height : "100%",
			info:[
				{label:"Contenu" , content: "ctab1",foc : ""},
				{label:"Chercher" , content: "ctab3", foc : "keyword"}
			]
		}
	);
	tab1.tabs[0].onclick = tabchange;
	tab1.tabs[1].onclick = tabchange;

	var thispage = '';
	tree.getTree(url_dir+'/wiki/handlers/gettree.php?lang='+lang+'&id_grp='+id_group,'tree',tree.activenode);
	haveindex = false;
	if(tab1.selected==1)
		getindex();
	
	// clear search
	document.getElementById('keyword').value = '';
	document.getElementById('searchresult').innerHTML = '';
	document.getElementById('adminmenu').style.display = 'none';

	user = "anonymous";
	uid = 0;
	
	cl="tree";
	clipboard = '';
	imageclip = '';
	crumbs();
    
	
    // is user logged in?  This would happen on page refresh
    ajax(url_dir+'/wiki/handlers/logincheck.php',null, 
        function(x){
            var obj = eval('(' + x.responseText + ')');
            if(obj.response=='ok')
                loginresponse(obj);
            else
                logout();
        } 
    );

    // <iframe id="printFrame" style="display:none"></iframe>
    var printFrame = document.createElement("iframe");
    printFrame.setAttribute("id", "printFrame");
    printFrame.setAttribute("style", "display:none");
    document.getElementsByTagName('body') [0].appendChild(printFrame);
	
	
  

}

function togglediff(to, from, page, dtype,a){
	//alert(to);
	var el = document.getElementById('revdiff_'+from);
	var link = document.getElementById(a);
	if(el.style.display=='none') {
		el.style.display='block';
	    link.className='histexpand';
		if(el.innerHTML=='' && from > 0) {
			ajax(url_dir+'/wiki/handlers/getdiff.php',
				'to='+to+'&from='+from+'&page='+page+'&type='+dtype+'&lang=fr',
				function(x){
					el.innerHTML = x.responseText;
				}
			);
		}
	}else{
		el.style.display='none';
		link.className = 'histcollapse'
	}
}

function tabchange(){
	if(tab1.selected==0)
		tree.updateNode(tree.activenode);

	if(tab1.selected==1 && !haveindex)
		getindex();
}

function currentStyle(){
	// loop through all link elements to find theme
	var targetelement="link";
	var targetattr="href";
	var allsuspects=document.getElementsByTagName(targetelement);
	for (var i=allsuspects.length; i>=0; i--){ //search backwards within nodelist for matching elements
		if (allsuspects[i] && allsuspects[i].getAttribute(targetattr)!=null && allsuspects[i].getAttribute(targetattr).indexOf("ui.css") > -1) {
			var paths = allsuspects[i].getAttribute(targetattr).split("/");
			return paths[1];
		}
	}
}

function printIframePage(){
	
    var content = document.getElementById('help').innerHTML;
    var win = document.getElementById("printFrame");
    alert(printFrame);
    win.contentWindow.document.write("<html><head><title>"+impression_txt+"</title>");
    win.document.write("<link rel='stylesheet' type='text/css' href='"+url_dir+"/css/task-comm.css' /></head>");
    win.document.write("<link rel='stylesheet' type='text/css' href='"+url_dir+"/css/color"+dir_color+".css' /></head>");
    win.contentWindow.document.write("<body><div style='padding:10px;'>"+content + "</div></body></html>");
    win.contentWindow.print();
   
}

function printpage(){
    if ((verOffset=navigator.userAgent.indexOf("Chrome"))!=-1) {
		if(parseInt(navigator.userAgent.substring(verOffset+7)) > 12) {
			printIframePage();
			return;
		}
    }
	
    var content = document.getElementById('help').innerHTML;
    win = window.open("","mywindow","width=500,height=500");
    win.document.open();
    win.document.write("<html><head><title>"+impression_txt+"</title>");
   // win.document.write("<link rel='stylesheet' type='text/css' href='"+url_dir+"/wiki/theme/"+currentStyle()+"/css/ui.css' /></head>");
    win.document.write("<link rel='stylesheet' type='text/css' href='"+url_dir+"/css/task-comm.css' /></head>");
    win.document.write("<link rel='stylesheet' type='text/css' href='"+url_dir+"/css/color"+dir_color+".css' /></head>");
	win.document.write("<body><div style='padding:10px;'>"+content + "</div></body></html>");	    
    win.document.close();
    setTimeout(function(){showPrint(win)},500);
	
}

function showPrint(win) {
    win.print();
    win.close();            
}

function setclipboard(id){
	clipboard = id;
}

function dummy(){}

function getPageMobile(id_page){

	jQuery.post(url_dir+"/wiki/handlers/getpage-wiki.php", { "id": parseInt(id_page)},function(data){
			
		jQuery('#page_wiki_history').removeClass('submenu_bold');
		jQuery('#page_wiki_content').addClass('submenu_bold');
		html = Wiky.toHtml(data.page.page_text);    
		//console.log(html); 
		jQuery('#zone_link').html('<div class="normal_text" id="info_text">'+html+'</div>');			
					
	},'json');
	
}			
			
function revertMobile(rev){
	
	if (confirm(would_you_return_version_txt)) { 
	
		jQuery.post(url_dir+"/wiki/handlers/reverttorev.php", { "rev": rev, "comment":''},function(data){
		
			if(data.success==1){
				getPageMobile(data.node_id);	
			}else if(data.success==2){
				alert(no_change_actual_and_ask_txt);
			}
			else{
				alert(error_renversement_txt);
			}
			
						
		},'json');
	
	}
	
}
// history related functions
function revert(rev){

	var signal_zone=' \
	<div id="inline_add_friends" class="box_type" style="width:260px;padding:10px 15px 14px;"> \
		<div class="title_notif"><strong>'+renverser_txt+'</strong></div> \
		<div id="ligne_separation" style="margin-top:10px;margin-bottom:10px;width:260px;"></div> \
		'+comm_renversement_txt+' \
		<textarea class="input_design" style="width:98%;height:80px;" id="comment_rev"></textarea> \
		<div id="ligne_separation" style="margin-top:10px;margin-bottom:10px;width:260px;"></div> \
		<div class="button_comm" style="width:100px;float:left;" onclick="javascript:jQuery.fancybox.close();"> \
			<div class="text_button_comm">'+cancel_txt+'</div> \
			<div class="icon_button_comm"><img src="'+url_dir+'/images/btn_cross.png"></div> \
		</div> \
		<div class="button_comm" style="width:100px;float:right;" onclick="javascript:launch_revert_process(\''+rev+'\');"> \
			<div class="text_button_comm">'+valid_txt+'</div> \
			<div class="icon_button_comm"><img src="'+url_dir+'/images/btn_check.png"></div> \
		</div> \
		<div class="clear"></div> \
	</div> \
	';

	jQuery.fancybox.open(
		signal_zone,
		{
			'autoSize'	: false,
			'width' : 290,
			'height'  : 'auto',
			'scrolling'		: 'no',
			'afterShow':function(){
				jQuery('.fancybox-outer').css({'border':'1px solid #CCCCCC'});
			}
		}
	);
	
	jQuery('.fancybox-outer').css({'border':'5px solid '+second_color_comm,'border-bottom':'6px solid '+second_color_comm});
	jQuery('.fancybox-inner').css({'top':'0px','left':'0px', 'width':'290px'});
	var heigt_real = (jQuery('.fancybox-inner').height()-5);
	jQuery('.fancybox-wrap').height(heigt_real);
		
	
}
	
function launch_revert_process(rev_to_go){

	var comm_rev=jQuery('#comment_rev').val();
	jQuery.post(url_dir+"/wiki/handlers/reverttorev.php", { "rev": rev_to_go, "comment":comm_rev},function(data){
		
		if(data.success==1){
			tree.click(data.node_id);	
			jQuery.fancybox.close();
		}else if(data.success==2){
			alert(no_change_actual_and_ask_txt);
		}
		else{
			alert(error_renversement_txt);
		}
		
					
	},'json');
	
}
	
function getrev(id_rev,rt,off){
  
	jQuery.post(url_dir+"/wiki/handlers/getrev.php", { "id": id_rev },function(data){
		
		document.getElementById('help').innerHTML = "<div class='titre_group' style='float:left;'>"+revision_txt+" "+id_rev+" : "+rt+"</div><div style='float:right;'><a href='javascript:gethistory("+off+")'>"+back_txt+"</a></div><div class='clear'></div><div style='margin-top:10px;margin-bottom:10px;width:100%;' id='ligne_separation'></div>"+Wiky.toHtml(data.page);
		plugins();
		document.getElementById('help').scrollTop = 0;			
					
	},'json');
		
  /* ajax(url_dir+"/wiki/handlers/getrev.php",
           "id="+id+"&rt="+encodeURIComponent(rt),
           function(x){
				
                document.getElementById('help').innerHTML = "<div class='titre_group'>Revision "+id+" : "+rt+"</div><div style='float:right;'><a href='javascript:gethistory("+off+")'>Retour</a></div><div class='clear'></div><div style='margin-top:10px;margin-bottom:10px;width:100%;' id='ligne_separation'></div>"+Wiky.toHtml(x.responseText);
                plugins();
                document.getElementById('help').scrollTop = 0;
           },
           "POST"
    );*/
}

function getrevMobile(id_rev,rt,off,page){
  
	jQuery.post(url_dir+"/wiki/handlers/getrev.php", { "id": id_rev },function(data){
		
		document.getElementById('zone_link').innerHTML = "<div class=\"normal_text_12\" style=\"border-bottom:1px solid #CCCCCC;padding:9px 0px 5px;\"> \
			<div style=\"float:left;margin-right:10px;\"><a href=\"javascript:;\" onclick=\"gethistoryMobile("+off+","+page+");\"><img src=\""+url_smt_dir+"/images/btn_prec.png\"></a></div> \
			<div style=\"font-weight:bold;overflow:hidden;line-height:22px;\" onclick=\"javascript:gethistoryMobile("+off+","+page+");\">"+revision_txt+" "+id_rev+" : "+rt+"</div> \
			<div class=\"clear\"></div> \
		</div> \
		<div class='normal_text'>"+Wiky.toHtml(data.page)+'</div>';	


	},'json');
		
}

function gethistory(offset){
    if(offset==null) offset=0;
    ajax(url_dir+"/wiki/handlers/history.php",
           "id="+tree.activenode+"&lang="+lang+"&offset="+offset,
           function(x){
                document.getElementById('help').innerHTML = x.responseText;
				menucontrol('history');
				jQuery(".histrevert a[title]").tooltip({position 	:"top right", offset : [-5, -5]});
           }
    );
}

function gethistoryMobile(offset,id_page){
    if(offset==null) offset=0;
    ajax(url_dir+"/wiki/handlers/history.php",
           "id="+id_page+"&lang=fr&mobile=1&offset="+offset,
           function(x){
				jQuery('#page_wiki_content').removeClass('submenu_bold');
				jQuery('#page_wiki_history').addClass('submenu_bold');
				jQuery('#zone_link').html(x.responseText);
           }
    );
}

function getnodehistory(){
	
    ajax(url_dir+"/wiki/handlers/nodehistory.php?lang="+lang,
           "id="+tree.activenode,
           function(x){
                var html=x.responseText;                
                document.getElementById('help').innerHTML = html;
		   }
	);
}

function profile(){
	popup.show(url_dir+'/wiki/getprofile.php?id='+uid+'&lang='+lang, 'profile', url_dir+'/wiki/handlers/updateprofile.php?id='+uid, '260px','profileresponse','validateprofile()');
}

function profileresponse(obj){
	if(obj.response != 'ok')
		alert(obj.response);	
}

function validateprofile(){
	document.getElementById('subscribe').value= document.getElementById('sub').checked;
	return true;
}

function indexkey(){
	var pane = document.getElementById('indexpane');
	var text = document.getElementById('index').value.replace(">","&gt;");
	var pattern="id=['\"](tag-"+text+"[^('|\")]*?)['\"]";

	var re = new RegExp(pattern,"i");

	var m = re.exec(pane.innerHTML);
	if (m != null) {
		var s = m[1];
		pane.scrollTop = document.getElementById(s).offsetTop;
	}
}

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
	                var labid = lab.replace(/\</g,'&lt;').replace(/\>/g,'&gt;').replace(/'/g,"&#39;").replace(/\&/g,'&amp;').replace(/"/g,'&quot;');

	                html += "<div id='tag-"+lab+"' class='indextag'>"+lab+"</div>";
	                var nodes = tag.getElementsByTagName('node');
	                for (var n = 0; n < nodes.length; n++) {
	                    var anode = nodes[n];
	                    var nlab = anode.getAttribute('label');
	                    var nid = anode.getAttribute('id');
	                    html += "<div style='margin-left:20px;'><a href='javascript:indexpage("+nid+")'>"+nlab+"</a></div>";
	                }
	            }
	            
	            target.innerHTML = html;
				haveindex = true;
				indexkey();
	       },
	       "GET"
	);
}

function indexpage(id_page){
       
	jQuery.post(url_dir+"/wiki/handlers/getpage.php", { "id": id_page},function(data){
		
		html = Wiky.toHtml(data.page.page_text);                    
		document.getElementById('help').innerHTML ='<div class="titre_group title_contact">'+data.page.label+'</div>'+html;
		RedirectLocation("LocationAnchor", id_page, "#"+id_page);
		tree.updateNode(id_page);
		cl="index";				
					
	},'json');
	
   /* ajax(url_dir+"/wiki/handlers/getpage.php?id="+id+"&lang="+lang,
		   null,
		   function(x){
				//console.log('lalal');
				var html=x.responseText;    
				html = Wiky.toHtml(html);                    
				document.getElementById('help').innerHTML = html;
				RedirectLocation("LocationAnchor", id, "#"+id);
		   },
		   "GET"
	);*/

		
}

function previewcancel(title){
	document.getElementById('imgpane').style.display = 'none';
	document.getElementById('editbar').style.display = "block";
	//if(imageclip !='')  document.getElementById('imgins').style.display = "block";
}

function menucontrol(sel){
	var v = document.getElementById('viewa');
	//var e = document.getElementById('edita');
	var h = document.getElementById('historya');
	var a = document.getElementById('adminmenu');
	
	var cls = "menupage";
	v.className = sel == 'view' ? cls + " select" : cls;
	//e.className = sel == 'edit' ? cls + " select" : cls;

	h.className = sel == 'history' ? cls + " select" : cls;
	a.className = sel == 'admin' ? cls + " select" : cls;
	
	
}

function editpage(path){
	var pathinfo;
	var nodeid = tree.activenode;
	if (path == null) {
		pathinfo = '';
	}else {
		pathinfo = "&path="+path;
	}			
		
	ajax(url_dir+"/wiki/handlers/editpage.php?id="+nodeid+pathinfo+"&lang="+lang+"&clip="+encodeURIComponent(imageclip),
		null,
		function(x){
			var html=x.responseText;    
			var id=tree.activenode;
			document.getElementById('help').innerHTML = html;
			document.getElementById('edittext').focus();
			menucontrol('edit');
			
			jQuery('#link_edit_fancy').click();
			
		},
		"GET"
	);
}

function loadscript(src,callback){
	if(!ArrayContains(loaded_plugins, src)){
		var script = document.createElement("script");
		script.src = src;
		script.type="text/javascript";
		if (script.readyState){  //IE
			script.onreadystatechange = function(){
				if (script.readyState == "loaded" ||
						script.readyState == "complete"){
					script.onreadystatechange = null;
					callback();
					loaded_plugins.push(src);
				}
			};
		} else {  //Others
			script.onload = function(){
				loaded_plugins.push(src);
				callback();
			};
		}
		document.getElementsByTagName("head")[0].appendChild(script);
	}else{
		callback();
	}
}

function edit(path){
	loadscript(url_dir+"/wiki/script/edit.js", function(){editpage(path)});
}

function sysclipboard(clip){
	imageclip = clip;
    previewcancel("");
    insertimage();
    inlinepreview();
}

function ContextMouseDown(event)
{
    // IE doesn't pass the event object
    if (event == null)
        event = window.event;

    // standard compliant or IE
    var target = event.target != null ? event.target : event.srcElement;

    // right mouse button and tree node clicked
    if (event.button == 2 && target.id.indexOf('treelabel')==0){
        _replaceContext = true;
    }   
}

function treecontext(event){ 
    if (event == null)
        var event = window.event;

    var target = event.target != null ? event.target : event.srcElement;

    // right mouse button and tree node clicked
    if (_replaceContext){
        var id=parseInt(target.id.substring(9));
		var type = 'folder';
		
        if(id != tree.activenode) // load page if not selected
		    tree.click(id);

		/*popup.show(url_dir+'/wiki/foldermenu.php?target=' + id + '&t=' + type + '&lang=' + lang + '&clip=' + clipboard, 'treemenu', url_dir+'/wiki/handlers/folder.php?target=' + id + '&lang=' + lang + '&uid=' + uid + '&clip=' + clipboard, '220px', 'folderresponse', 'validateFolder()');*/
		_replaceContext = false;
		
		return false;
	}	
    return true;       
}

function folderresponse(obj){
	if(obj.response=='ok'){
		if(clipboard == ''){
			if(obj.node==-1){ // removed node
				tree.getTree(url_dir+'/wiki/handlers/gettree.php?lang='+lang+'&id_grp='+id_group,'tree');				
			}else{
				tree.getTree(url_dir+'/wiki/handlers/gettree.php?lang='+lang+'&id_grp='+id_group,'tree',obj.node);				
			}
			
		}
	}else{
		alert(obj.response);
	}
	
}

function tags(){
	popup.show(url_dir+'/wiki/tagedit.php?id='+tree.activenode+'&lang='+lang, 'tags', url_dir+'/wiki/handlers/tagsave.php?id='+tree.activenode+"&uid="+uid+'&lang='+lang, '260px','tagresponse');
}
function tagresponse(obj){
	haveindex = false;
	//gettags();
	if(tab1.selected==1)
		getindex();
}

function logout(){
		document.getElementById('adminmenu').style.display = 'none';
		//document.getElementById('status').innerHTML = 'anonymous@';
		user = 'anonymous';
		uid=-1;
        ajax(url_dir+'/wiki/handlers/logout.php',null,function(){});
}

function loginform(){
	popup.show(url_dir+'/wiki/login.php?lang='+lang, 'login', 'handlers/login.php', '260px','loginresponse');
}

function registerform(){
	popup.show(url_dir+'/wiki/register.php?lang='+lang, 'register', url_dir+'/wiki/handlers/register.php', '260px','registerresponse','validateRegister()');
}

function loginresponse(obj){
	if (obj.response == 'ok') {
		//document.getElementById('status').innerHTML = "<span>"+obj.user+'@'+obj.ip+"</span>";
		document.getElementById('loginmenu').style.display = 'none';
		document.getElementById('logoutmenu').style.display = 'block';
		document.getElementById('profilemenu').style.display = 'block';
		document.getElementById('registermenu').style.display = 'none';
		if(obj.level=='admin'){
			document.getElementById('adminmenu').style.display = 'block';
		}else{
			document.getElementById('adminmenu').style.display = 'none';			
		}

		user = obj.user;
		uid = obj.uid;
		ip = obj.ip;
		editable();
	}else{
		console.log(obj.response);
	}
}

function registerresponse(obj){
	loginresponse(obj);
}

function searchkey(e) {
 	e = e || window.event;   
 	var code = e.keyCode || e.which;    
	if(code == 13){     
		search();
		tab1.setTab(2);
		document.getElementById('keyword').select();
	}
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

function pack(){
	/*c.style.height = "auto";
	//a.style.overflow = "hidden"; // prevents flicker
	
	
    if (c.currentStyle) {
		bt = parseInt(c.currentStyle.borderTopWidth);
		br = parseInt(c.currentStyle.borderRightWidth);
		bb = parseInt(c.currentStyle.borderBottomWidth);
		bl = parseInt(c.currentStyle.borderLeftWidth);
	}
	else if (window.getComputedStyle) {
		bt = parseInt(document.defaultView.getComputedStyle(c, null).getPropertyValue('border-top-width'));
		br = parseInt(document.defaultView.getComputedStyle(c, null).getPropertyValue('border-right-width'));
		bb = parseInt(document.defaultView.getComputedStyle(c, null).getPropertyValue('border-bottom-width'));
		bl = parseInt(document.defaultView.getComputedStyle(c, null).getPropertyValue('border-left-width'));
	}
	
	//b.style.width = Math.max(0,c.offsetWidth - a.offsetWidth - s.offsetWidth-bl-br) + "px";*/
}

// Split Pane Control
var sp=300;
function dc(){
	var l = s.offsetLeft > 5 ? 0 : sp;
	sp = s.offsetLeft;
	a.style.width = Math.max(0,l) + "px";
	b.style.width = (c.offsetWidth - a.offsetWidth - s.offsetWidth-bl-br) + "px";
	
}

function md(e){
	splitting = true;
	document.body.onselectstart = function(){return false;};
}

function mv(e){
	if(!splitting) return;
	var l = ie ? event.clientX + document.body.scrollLeft : e.pageX;
	a.style.width = Math.max(0,l-c.offsetLeft - s.offsetWidth - 5) + "px";
	b.style.width = (c.offsetWidth - a.offsetWidth - s.offsetWidth-bl-br) + "px";
}

function mu(e){
	splitting = false;
	document.body.onselectstart = function(){return true;};
}

function dmu(e){
	if(!splitting) return true;
	var l = ie ? event.clientX + document.body.scrollLeft : e.pageX;
	if(s.offsetLeft > l) mu(e); 
}

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
					console.log('lulu');
                    var words = textbox.value.split(" ");
                    for (var i=0;i<words.length;i++){
                        var word=words[i];
                        // highlight the word ignoring elements
                        pattern = "(" + word + ")(?=[^>]*<)";
                        html = html.replace(new RegExp(pattern,"gi"), "<span style='background:yellow;display:inline-block;'>$1</span>")
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
				
				lang="fr";
				return;
			}
		}

		anchor(HashLocationName);
		//RedirectLocation("LocationAnchor", HashLocationName, "#"+HashLocationName)
		crumbs();
		last=HashLocationName;
	}
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
	//document.location.hash = "#"+parseInt(hashes[0].substring(1))+"-"+lang+"-"+tree.nodes[tree.activenode].label+anch;
	
	editable(); // sets menu
	plugins();
	anchor(HashName);
	menucontrol('view');
	//gettags();
}

function gettags(){
	/*ajax(url_dir+"/wiki/handlers/gettags.php", "id=" + tree.activenode  + "&lang=" + lang,
		function(x){
			/*var tags = x.responseText.split(',');
			var tagstring='';
			for(i=0;i<tags.length;i++){
				var tag = tags[i];
				tagstring += ", <a href='javascript:tagselect(\""+tag+"\")'>"+tag+"</a>";
			}
			document.getElementById('taglist').innerHTML = 
				x.responseText != '' ?
				"Mots clés : " + tagstring.substring(2)
				:
				''
				;
		}
	);*/
}

function tagselect(tag){
	document.getElementById('index').value=tag;
	tab1.setTab(1);
	if(haveindex){
		indexkey();
	}else{
		getindex();
	}
}

function anchor(aname){
	anum = aname.substring(1);
	var pane = document.getElementById('help');
	pane.scrollTop =0;
	if(parseInt(anum) == anum) {return;} // nothing to do
	var anchs = anum.split('#');
	if(anchs.length > 1){
		var tags = document.getElementsByName(anchs[1]);
		if(tags.length > 0) pane.scrollTop = tags[0].offsetTop;
	}

}

function editable(){
    ajax(url_dir+"/wiki/handlers/editable.php", "id=" + tree.activenode + "&lang=" + lang , function(x){
        var edita = document.getElementById('edita');
        var tagsm = document.getElementById('tagsm');
        
        if (x.responseText == "1" || x.responseText == "2") {
            edita.innerHTML = edit_content_txt;
            //tagsm.style.display = "block";
        }
        else {
            if(language != null) edita.innerHTML = source_preview_txt;
            tagsm.style.display = "none";
        }
    });
}

function forward(){
	history.go(1);
}

function back(){
	history.go(-1);
}

// validation

function validateRegister(){
	 // email
	var element = document.getElementById('user');
	if(element.value==''){
	  alert('Please provide a user name');
	  element.focus()
	  element.select();
	  return false;
	}
	
	var element = document.getElementById('pass');
	if(element.value==''){
	  alert('Please provide a password');
	  element.focus()
	  element.select();
	  return false;
	}
	
	var element = document.getElementById('confirm');
	if(element.value=='' || element.value != document.getElementById('pass').value){
	  alert('Password and Confirmation must match.');
	  element.focus()
	  element.select();
	  return false;
	}
	
	var element = document.getElementById('email');
	if(element.value==''){
	  alert('Please provide a valid email address');
	  element.focus()
	  element.select();
	  return false;
	}
	
	var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!filter.test(element.value)) {
	  alert('Please provide a valid email address');
	  element.focus()
	  element.select();
	  return false;
	}
	
	return true;
}

function validateFolder(){

	var n = document.getElementById("fname");
	
	var ap = document.getElementById("faddpage");
	var p = document.getElementById("fpaste");
	
	var r = document.getElementById("fremovefolder");
	var rn = document.getElementById("frename");
	var c = document.getElementById("fcut");
	
	var comment = document.getElementById("commentf");

	if(ap.checked || rn.checked){
		if(n.value==''){
		  alert('Please enter a name');
		  n.focus()
		  n.select();
		  return false;
		}		
	}
	
	if(r.checked && user=='anonymous'){
	  alert('You do not have permission to remove folders!');
	  return false;		
	}
	
	if(r.checked && comment.value.length < 10){
	  alert('Please enter reason for deletion (min 10 characters)');
	  comment.focus()
	  comment.select();
	  return false;		
	}
	
	var action = document.getElementById('action');
	
	if(ap.checked) action.value = "addpage";
	if (p.checked) {
		if (clipboard == '') {
			alert("Clipboard is empty!");
			return false;
		}
		action.value = "paste";
	}
	
	if(r.checked) action.value = "remove";
	if(rn.checked) action.value = "rename";
	if (c.checked) {
		action.value = "cut";
		clipboard = tree.activenode;
	}else{
		if(action.value == 'paste') clipboard = ''; // clipboard parameter already set in treedblckl()
	}
	
	var pos = document.getElementById('position');
	if(document.getElementById('before').checked) pos.value = "before";
	if(document.getElementById('after').checked) pos.value = "after";
	
	if(document.getElementById('in').checked) pos.value = "in";		
	
	return true;
}

// utility

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

function adminpage(){
	loadscript(url_dir+"/wiki/script/admin.js", 
				function(){
					ajax(url_dir+"/wiki/admin/adminpage.php",
					   'lang='+lang+"&page="+tree.activenode,
					   function(x){
						   document.getElementById('help').innerHTML = x.responseText;
						   menucontrol('admin');
					   }
					)
				});
}

function ajax(handler,postparameters,callback,method,cache){
    if(method==null)
        method = "POST";
    if(cache==null)
        cache = true;

    // TODO: need to handle no ?a=b, for now false is selectively added
    if(cache == false)
        handler = handler + '&dummy=' + new Date().getTime();

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

function crumbs(){
	var c = tree.crumbs;
	if(c.length==0) setTimeout("crumbs()",50);
	var html = '';
	for(var i=0; i< c.length; i++){
		var crumb = c[i];
		if(i>0) html+='<img src="'+url_dir+'/images/arrow_pagination.png" style="margin-left:3px;margin-right:3px;float:left;margin-top:7px;">';
		var click = i==(c.length-1) ? "" : "onclick='tree.click(\""+crumb.ref+"\")'";
		var cls = i==(c.length-1) ? "endcrumb" : "crumb";
		
		html+="<div class='"+cls+"'"+click+"><a href='javascript:;'>"+crumb.label+"</a></div>";
	}
	document.getElementById("crumbs").innerHTML = html;
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
