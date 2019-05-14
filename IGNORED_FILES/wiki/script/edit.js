function editdiff(){
	var text = document.getElementById('edittext').value.replace(/\r/g,"");

	ajax(url_dir+"/wiki/handlers/editdiff.php",
	   "id="+tree.activenode+"&text="+encodeURIComponent(text)+"&lang="+lang,
	   function(x){
			var div = document.getElementById('editdiff');
			div.style.display = 'block';
			div.innerHTML=x.responseText;    
	   }
	);
}

function inlinepreview(){
	if(!document.getElementById('showpreview').checked) return;;
	var previewbox = document.getElementById('previewbox');
	var editbox = document.getElementById('edittext');
	var value_to_insert=editbox.value;
	
	previewbox.innerHTML = Wiky.toHtml(value_to_insert);
	
	plugins();
	previewscroll();
	return true;
}

function previewscroll(){
	if(!document.getElementById('autoscroll').checked) return;;
	var previewbox = document.getElementById('previewbox');
	var editbox = document.getElementById('edittext');

	if (editbox.curScroll != editbox.scrollTop) {
	   //editbox.curScroll = editbox.scrollTop;
	   previewbox.scrollTop = previewbox.scrollHeight/editbox.scrollHeight*editbox.scrollTop;
	}
}

var menublocker;
var menushowing='';
function editmenu(div){


	if(menushowing != '')
		document.getElementById(menushowing).style.display='none';
		
	if(div != ''){	
		document.getElementById(div).style.display='block';
		
		if(menublocker==null){
			menublocker = document.createElement("div");
			menublocker.style.position = "absolute";
			menublocker.style.left = 0;
			menublocker.style.right = 0;
			menublocker.style.top = 0;
			menublocker.style.bottom = 0;
			menublocker.style.background = "none";
			menublocker.style.zIndex = 100;
			document.body.appendChild(menublocker);			
		}
		
		menublocker.style.display='block';
		menublocker.onmousedown = function(){
			document.getElementById(div).style.display='none';
			document.getElementById(menushowing).style.display='none';
			menublocker.style.display='none';
			menushowing = '';
			document.getElementById('edittext').focus();
		};
	
		menushowing = div;
	}
}

function selecttext (ta, start, end){
	ta.focus();
	if(ta.setSelectionRange)
		ta.setSelectionRange(start, end);
	else if(ta.createTextRange) {
		var e = ta.createTextRange();
		e.collapse(true);
		var pre = ta.value.substring(0,start);
		
		// for some reason ie position is off by the crlf count, tweak here
		cr = pre.length - pre.replace(new RegExp("\r","g"), '').length; //crlf count
		e.moveEnd('character', end - cr);
		e.moveStart('character', start - cr);
		e.select();
		ta.focus();
	}
	 
}

function textareaselection(ta) {
	if (document.selection) { //IE
		ta.focus();
		var c = "\001";
		var sel = document.selection.createRange();
		var cnt = sel.text.length;
		if(cnt==0){
			sel.moveEnd('character', 0)
		}
		var save = sel.text;
		var dul = sel.duplicate();
		len = 0;
		dul.moveToElementText(ta);
		sel.text = c;
		len = dul.text.indexOf(c);
		sel.moveStart('character',-1);
		sel.text = save;
 
	   return [len, len+cnt];
	}
	else { //others
		return[ta.selectionStart,ta.selectionEnd];
	}
}


function editbuttons(symbol,menu){

	//console.log(symbol);
	if(menu!=null) document.getElementById(menu).style.display='none';
	if(menublocker!=null) menublocker.style.display='none';
	if(menushowing!='') document.getElementById(menushowing).style.display='none';
	menushowing='';
	
	var ta = document.getElementById('edittext');
	var ends = textareaselection(ta);
	var start = ends[0];
	var end = ends[1];
	var pre = ta.value.substring(0,start);
	var post = ta.value.substring(end);
	var cnt = end - start;
	var sel = ta.value.substring(start,end);

	if (symbol == 'INS'){
		ta.value = pre + '(+' + sel +'+)' + post;	
		selecttext(ta, start + 2 + sel.length, start + 2 + sel.length);
		ta.selectionStart = start + 2 + sel.length;
		return;
	}
	
	if (symbol == 'DEL'){
		ta.value = pre + '(-' + sel +'-)' + post;	
		selecttext(ta, start + 2 + sel.length, start + 2 + sel.length);
		ta.selectionStart = start + 2 + sel.length;
		return;
	}
	
	if (symbol == 'PRE'){
		ta.value = pre + '\n[%\n' + sel +'\n%]\n' + post;	
		selecttext(ta, start + 4, start + 4);
		return;
	}
	
	if (symbol == 'CSS'){
		if(sel.length==0) return;

		ta.value = pre + '`{style}' + sel +'`' + post;
		selecttext(ta, start + 2, start + 7);
		return;
	}
	
	if (symbol == 'Plugin'){
		ta.value = pre + '[#Name,{};' + sel +'#]' + post;
		selecttext(ta, start + 2, start + 6);
		return;
	}
	
	if (symbol == 'A'){
		
		var saisie = prompt("Copiez ici votre lien internet :", "");
		//console.log(saisie);
		if(saisie=='' || saisie == null)saisie='url';
		if((cnt == 0)) {
			sel = 'text';
		}
		
		ta.value = pre + '['+saisie+',' + sel +']' + post;	
		selecttext(ta, start + 1, start + 4);
		return;
	}
	
	if (symbol == 'HR'){
		if(!(cnt == 0)) {
			ta.focus();
			return;
		}
		ta.value = pre + '\n----\n' + post;	
		selecttext(ta, start + 6, start + 6);
		return;
	}
	
	if (symbol == 'LI' || symbol == 'OL'){
            var s = symbol == 'LI' ? '* ' : '1. ' ;
            var li = cnt == 0 ? "\n" + s : s;
            if(sel.length==0){
                ta.value = pre + li + sel + post;
                cnt += li.length;
            } else {
                var lines = sel.split("\n")
                var newsel = "";
                for(l=0;l<lines.length;l++){
                    var line = lines[l];
                    if(line!=""){
                        newsel += li + line + "\n";
                        cnt += li.length;
                    }
                }
                ta.value = pre + newsel + post;
            }
            selecttext(ta, start + cnt, start + cnt);
            return;
	}
	
	ta.value = pre + symbol + sel + symbol + post;	
	selecttext(ta, start + symbol.length + cnt, start + symbol.length + cnt);
}

function subscribe(){
	var box = document.getElementById('esub');
	ajax(url_dir+"/wiki/handlers/subscribe.php",
		"uid="+uid+"&pid="+tree.activenode+"&val="+box.checked+"&lang="+lang,
		function(x){
		},
		"POST"
	);
}

function editsave(source){

	if(source==null) source='edittext';
	
	// restore commas saved in hidden element
	var text = document.getElementById(source).value.replace(/&#39;/g,"'").replace(/\r/g,""); // keep ie changes consistent with others
	
	var path = '';
	var comment = '';
	if(jQuery('#path').length)path=jQuery('#path').val();
	if(jQuery('#commente').val() != '')comment=jQuery('#commente').val();
	
	jQuery.post(url_dir+"/wiki/handlers/editsave.php", { "text": text, "id":tree.activenode, "comment": comment, "path" : path},
	function(data){
		
		var html=data;    
		var reload = false;

		if (jQuery('#path').length) {
			reload = true;
			path = path;
		}
		document.getElementById('help').innerHTML = Wiky.toHtml(html);
		if (reload) {
			tree.getTree(url_dir+'/wiki/handlers/gettree.php?lang=' + lang+'&id_grp='+id_group, 'tree');
			pageFromPath(path);
		}
		plugins();
		menucontrol('view');
		destroy_edit(tree.activenode);
	
	},'html');
	
	
   /*
	parms = "text="+encodeURIComponent(text)+"&id="+tree.activenode+"&lang="+lang+path;
	parms += "&user="+encodeURIComponent(user)+"&ip="+ip+"&uid="+uid+"&comment="+encodeURIComponent(document.getElementById('commente').value);
	ajax(url_dir+"/wiki/handlers/editsave.php",
		   parms,
		   function(x){
				var html=x.responseText;    
				var reload = false;var path = '';
				if (document.getElementById('path')) {
					reload = true;
					path = document.getElementById('path').value;
				}
				document.getElementById('help').innerHTML = Wiky.toHtml(html);
				if (reload) {
					tree.getTree(url_dir+'/wiki/handlers/gettree.php?lang=' + lang, 'tree');
					pageFromPath(path);
				}
				plugins();
				menucontrol('view');
		   }
	);*/
}

function imagepage(){
	document.getElementById('editbar').style.display = "none";
	if(document.getElementById('imgpane')){
		document.getElementById('imgpane').style.display = "block";
	}else{
		var imgpane = document.createElement("div");
		imgpane.id = "imgpane";
		imgpane.style.position = "absolute";
		imgpane.style.left = 0;
		imgpane.style.right = 0;
		imgpane.style.top = 0;
		imgpane.style.bottom = '-100px'; // TODO: why do I need this hack???
		imgpane.style.background = "#fff";
		document.getElementById('help').appendChild(imgpane);
	}

	ajax(url_dir+"/wiki/handlers/imagepage.php?id="+tree.activenode+"&lang="+lang+"&title="+encodeURIComponent(tree.activetitle),
	       null,
	       function(x){
                var html=x.responseText;    
                var id=tree.activenode;
                document.getElementById('imgpane').innerHTML = html;
	       },
	       "GET",
               false
	);
}

function insertimage(){
	var edittext = document.getElementById('edittext');
	//IE
	if (document.selection) {
		edittext.focus();
		var sel = document.selection.createRange();
		sel.text = "uri/"+imageclip;
	}
	//Mozilla
	else if (edittext.selectionStart || edittext.selectionStart == '0') {
		var startPos = edittext.selectionStart;
		var endPos = edittext.selectionEnd;
		edittext.value = edittext.value.substring(0, startPos)
	              + "uri/"+imageclip
	              + edittext.value.substring(endPos, edittext.value.length);
	} else {
	
		edittext.value += imageclip;
	
	}
	
}

function toggleedit(){
	var ta = document.getElementById('edittext');
	var pb = document.getElementById('previewbox');
	var half = "48%";
	//console.log('lalal');
	if(ta.rows==12){
		ta.rows = 28;
		ta.style.width=half;
		
		pb.style.height="450px";
		pb.style.width=half;
		pb.style.float="right";
		pb.style.margin="0 0 2px 1px;";
	}else{
		ta.rows = 12;
		ta.style.width="500px";
		
		pb.style.height="240px";
		pb.style.width="500px";
		pb.style.float="left";
		pb.style.margin="2px 0 2px 0;";
	}
}

function validateupload(title){
	currenttitle = title;
}

function uploadDone() { 
	var ret = frames['upload_target'].document.getElementsByTagName("body")[0].innerHTML;
	var data = eval("("+ret+")"); // parse json
	
	if(data.response=='ok') { 
		imagepage();
	}
	else { 
		alert(data.response);
	}	
}


