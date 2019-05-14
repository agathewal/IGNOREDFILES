

function lockpage(box){

    var lock = box.checked;
    ajax(url_dir+"/wiki/admin/lockpage.php",'lock='+lock+"&page="+tree.activenode+"&lang="+lang,adminpage);
}

function lockall(lock){

    ajax(url_dir+"/wiki/admin/lockpage.php",'lock='+lock+"&page="+tree.activenode+"&all=true",adminpage);
}

function clearhistory(all){
    var pages = "";
    if(all=='days'){
	var days = document.getElementById('clear_days');
	var d = parseInt(days.value);
	if(days.value=='' || isNaN(d)){
		alert('Invalid number of days');
		days.focus()
		days.select();
		return false;
	}
	pages = " pages older than "+d+"days";
	all = d;
    }

    if(all==true) pages = "all pages";
    if(all==false) pages =  "the current page" ;

    if(!confirm("This will clear all history for "+pages+"!!! This can not be undone.  Are you sure?"))
       return;
	   
    ajax(url_dir+"/wiki/admin/clearhistory.php","page="+tree.activenode+"&all="+all,adminpage);       
}

function purge(selected){
	if(selected==null){
		if(!confirm("This will purge all deleted pages. This can not be undone.  Are you sure?"))
		   return;
		   
		ajax(url_dir+"/wiki/admin/purge.php",null,adminpage);
	}else{
		   
		var c = []; var did = '';
		c = document.getElementsByTagName('input');
		for (var i = 0; i < c.length; i++){
			if (c[i].type == 'checkbox' && c[i].id.substring(0,8)=='deleted_' && c[i].checked == true){
				var did = did+","+c[i].id.substring(8);
			}
		}
		if(did.length > 1) {
			if(!confirm("This will purge selected pages. This can not be undone.  Are you sure?"))
				return;

			did = did.substring(1);
			ajax(url_dir+"/wiki/admin/purge.php",'did='+did,adminpage);
		}else{
			alert("nothing selected");
		}
	}
}

function optimize(){

    ajax(url_dir+"/wiki/admin/optimize.php",null,adminpage);       
}

