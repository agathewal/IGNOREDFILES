var tree = {
	
	n : 0,
	target : null,
	home : '',
	nodes : [],
	crumbs : [],
	activenode : '',
	activetitle : '',
	highlight : '#ccccff',
	normal: '#ffffff',		
	updateNode : function(ref){

	        if (ref && this.nodes.length > 0) {
	        	ref = parseInt(ref);
	            this.crumbs = [];
	            this.expandnode(this.nodes[ref]);
	            this.crumbs.reverse();
	            
	            if (!(ref in this.nodes)) 
	                return;
	            if (this.activenode != '') {
					//console.log(this.nodes[this.activenode].id);
					jQuery('#'+this.nodes[this.activenode].id).removeClass('title_fst_color');
	                //document.getElementById(this.nodes[this.activenode].id).style.backgroundColor = this.normal;
	            }
	            this.activenode = ref;
	            this.activetitle = this.nodes[ref].label;
	            var selectednode = document.getElementById(this.nodes[ref].id);
	            
	            // TODO: this is very sloppy, need better way
	            
	            // scroll to node if not visible
	            //selectednode.style.backgroundColor = this.highlight;
				jQuery('#'+this.nodes[ref].id).addClass('title_fst_color');
	            var parent = selectednode.parentNode.parentNode;
	            var toc = document.getElementById("toc");
				
	            if (parent.offsetTop - toc.scrollTop + selectednode.offsetHeight > toc.offsetHeight) 
	                toc.scrollTop = parent.offsetTop + selectednode.offsetHeight - toc.offsetHeight;
	            
	            if (toc.scrollTop > parent.offsetTop) 
	                toc.scrollTop = parent.offsetTop;
	            
	            // END TODO
	        }
	},
	
	click : function(id_page){
		
		//console.log('laloli');
		if(id_page!=''){
			jQuery.post(url_dir+"/wiki/handlers/getpage-wiki.php", { "id": parseInt(id_page)},function(data){
			
				html = Wiky.toHtml(data.page.page_text);    
				//console.log(html);
				var data_edit_html='';
				if(data.can_edit=='0')data_edit_html='display:none;'
				 
				document.getElementById('help').innerHTML ='<div class="titre_group" style="float:left;font-weight:bold;">'+data.page.label+'</div><div style="float:right;'+data_edit_html+'"><a id="edita" href="javascript:edit()" class="title_fst_color">'+edit_content_txt+'</a></div><div class="clear"></div><div style="margin-top:10px;margin-bottom:10px;width:100%;" id="ligne_separation"></div>'+html;
				RedirectLocation("LocationAnchor", id_page, "#"+id_page);
				tree.updateNode(id_page);
				cl="tree";				
							
			},'json');
		}
	
	},
	
	getTree : function(file, t, active){
		this.nodes = [];
		this.home = '';
		if(active==null) this.activenode=''; else this.activenode = active;
		this.target = document.getElementById(t);
		
		var self = this;
	    ajax(file,
	           null,
	           function(x){
	                var xmlDoc=x.responseXML.documentElement;
					self.target.innerHTML = '';
					
	                tree.parseTree(xmlDoc,self.target,0);
	                if (!document.location.hash) {
	                    tree.click(tree.home); // no #page requested get home page
	                }else{
	                    if (self.activenode=='' && !(document.location.hash.substring(1)) in self.nodes) {
	                        tree.click(tree.home);
							//this.activenode =
	                    }else{
	                        tree.click(document.location.hash.substring(1));
	                    }
	                }
	           },
		    "GET",
                    false
	    );
		
	},
	
	getTree_widget : function(file, t, active){
		this.nodes = [];
		this.home = '';
		if(active==null) this.activenode=''; else this.activenode = active;
		this.target = document.getElementById(t);
		
		var self = this;
	    ajax(file,
	           null,
	           function(x){
	                var xmlDoc=x.responseXML.documentElement;
					self.target.innerHTML = '';
					
	                tree.parseTree_widget(xmlDoc,self.target,0);
	               
	           },
		    "GET",
                    false
	    );
		
	},
	
	getTreeMobile : function(file, t, active){
		this.nodes = [];
		this.home = '';
		if(active==null) this.activenode=''; else this.activenode = active;
		this.target = document.getElementById(t);
		
		var self = this;
	    ajax(file,
	           null,
	           function(x){
	                var xmlDoc=x.responseXML.documentElement;
					self.target.innerHTML = '';
					
	                tree.parseTreeMobile(xmlDoc,self.target,0);
	               
	           },
		    "GET",
                    false
	    );
		
	},
	
	parseTree_widget : function(node,parent,pref){
		
		var url_to_go=url_dir+'/wikis/?id_group='+id_group+'&did='+id_project;
		var f = document.createElement("div");
		f.className = "node";
		var id_node_first=node.childNodes[0].getAttribute('id');
		if(id_node_first=='0'){
		
			var link_add_page='<a href="javascript:;" onclick="javascript:jQuery(\'#add_page_wiki\').click();" class="title_fst_color">';
			jQuery('#tree_wiki').html('<div style="margin-left:15px;">'+sprintf(no_wiki_page_txt,link_add_page)+'</div>');
		
		}else{
		
			for (var x = 0; x < node.childNodes.length; x++) {

				var el = node.childNodes[x];
				if (el.nodeName == 'folder') {
					if(this.home==''){
						this.home = el.getAttribute('ref');
						//this.activenode = el.getAttribute('ref');
					} 
					this.n++;
					var f = document.createElement("div");
					f.className = "node";
					var edit_zone='';
					if(el.getAttribute('editable')=='1')edit_zone="<div style='float:left;'> <span class='part_edit_page'><span class='separation_point'>.</span> <a href='"+url_dir+"/gest_wiki_page.php?action=edit&id="+el.getAttribute('ref')+"&"+url_suppl+"' class='menu_edit_page title_fst_color' data-fancybox-type='iframe'>"+editer_txt+"</a></span></div>";
					
					var html = "<div style='overflow:hidden;width:100%;' id='wiki_tree_"+el.getAttribute('ref')+"' class='wiki_page_arbo'><div class='toggle closed' id='toggle"+el.getAttribute('ref')
								+"' onclick='tree.togglenode("+el.getAttribute('ref')+")'></div><div id='"+"treelabel"
								+el.getAttribute('ref')+"' class='closelabel' onclick=\"redir('"+url_to_go+"#"+el.getAttribute('ref')+"-fr');\">"
								+el.getAttribute('label')+"</div>"+edit_zone+"</div><div class='clear'></div>";
					var b = document.createElement("div");
					b.id = "treenode"+el.getAttribute('ref');
					f.innerHTML = html;		
					f.appendChild(b);	
					b.style.display = "none";	
					
					if(el.getAttribute('ref') != "")
						this.nodes[el.getAttribute('ref')] = {"ref": el.getAttribute('ref'), "id" : "treelabel"+el.getAttribute('ref'), label : el.getAttribute('label'),"pid" : parent.id, "owner" : pref};
					
					this.parseTree_widget(el,b,el.getAttribute('ref'));
					parent.appendChild(f);
					
				}
				
				if (el.nodeName == 'leaf') {
					//console.log(el.getAttribute('ref'));
					if(this.home=='') this.home = el.getAttribute('ref');
					this.n++;
					var l = document.createElement("div");
					
					var edit_zone='';
					if(el.getAttribute('editable')=='1')edit_zone="<div style='float:left;'> <span class='part_edit_page'><span class='separation_point'>.</span> <a href='"+url_dir+"/gest_wiki_page.php?action=edit&id="+el.getAttribute('ref')+"&"+url_suppl+"' class='menu_edit_page title_fst_color' data-fancybox-type='iframe'>"+editer_txt+"</a></span></div>";
					
					
					l.innerHTML = "<div style='overflow:hidden;width:100%;' class='wiki_page_arbo' id='wiki_tree_"+el.getAttribute('ref')+"'><div id='"+"treelabel"+el.getAttribute('ref')+"' class='leaf' onclick=\"redir('"+url_to_go+"#"+el.getAttribute('ref')+"-fr');\">"
								+el.getAttribute('label')+'</div>'+edit_zone+"<div class='clear'></div>";
					var ref = l.getAttribute('ref');
					parent.appendChild(l);
					/*jQuery('.wiki_page_arbo').click(function(){
						console.log('lala');
					});*/
					if(el.getAttribute('ref') != "")
						this.nodes[el.getAttribute('ref')] = {id : "treelabel"+el.getAttribute('ref'), label : el.getAttribute('label'),pid : parent.id, owner : pref};
						
				}

			}
			
			jQuery('.wiki_page_arbo').each(function(index) {
		
				jQuery('#'+jQuery(this).attr("id")).mouseover(function() {
					jQuery(this).find('.part_edit_page').show();
				}).mouseout(function(){
					jQuery(this).find('.part_edit_page').hide();
				});	
				
				jQuery(".menu_edit_page").fancybox({
					'autoSize':false,
					'width':450,
					'height':'auto',
					'scrolling':'no'
				});
				
			});  
			
		}
		
	},
	
	parseTreeMobile : function(node,parent,pref){
		
		var f = document.createElement("div");
		f.className = "node";
		var id_node_first=node.childNodes[0].getAttribute('id');
		if(id_node_first=='0'){
		
			var link_add_page='<a href="javascript:;" onclick="javascript:jQuery(\'#add_page\').click();" class="title_fst_color">';
			jQuery('#tree').html('<div style="margin-left:15px;">'+sprintf(no_wiki_page_txt,link_add_page)+'</div>');
		
		}else{
		
			for (var x = 0; x < node.childNodes.length; x++) {

				var el = node.childNodes[x];
				if (el.nodeName == 'folder') {
				
					if(this.home==''){
						this.home = el.getAttribute('ref');
						//this.activenode = el.getAttribute('ref');
					} 
					this.n++;
					var f = document.createElement("div");
					f.className = "node";
					
					var html = "<div style='overflow:hidden;width:522px;' id='wiki_tree_"+el.getAttribute('ref')+"' class='wiki_page_arbo'><div class='toggle closed' id='toggle"+el.getAttribute('ref')
								+"' onclick='tree.togglenode("+el.getAttribute('ref')+")'></div><div id='"+"treelabel"
								+el.getAttribute('ref')+"' class='closelabel' onselectstart='return false;' ondblclick='tree.togglenode("+el.getAttribute('ref')+")'><a href='"+url_smt_dir+"/wiki.php?id="+el.getAttribute('ref')+"&id_group="+id_group+"' data-inline='true' data-rel='dialog' data-transition='slidedown'>"
								+el.getAttribute('label')+"</a></div></div>";
					var b = document.createElement("div");
					b.id = "treenode"+el.getAttribute('ref');
					f.innerHTML = html;		
					f.appendChild(b);	
					b.style.display = "none";	
					
					if(el.getAttribute('ref') != "")
						this.nodes[el.getAttribute('ref')] = {"ref": el.getAttribute('ref'), "id" : "treelabel"+el.getAttribute('ref'), label : el.getAttribute('label'),"pid" : parent.id, "owner" : pref};
					
					this.parseTreeMobile(el,b,el.getAttribute('ref'));
					parent.appendChild(f);
					
				}				
				if (el.nodeName == 'leaf') {
				
					//console.log(el.getAttribute('ref'));
					if(this.home=='') this.home = el.getAttribute('ref');
					this.n++;
					var l = document.createElement("div");
					
					l.innerHTML = "<div style='overflow:hidden;width:522px;' class='wiki_page_arbo' id='wiki_tree_"+el.getAttribute('ref')+"'><div id='"+"treelabel"+el.getAttribute('ref')+"' class='leaf' onselectstart='return false;'><a href='"+url_smt_dir+"/wiki.php?id="+el.getAttribute('ref')+"&id_group="+id_group+"' data-inline='true' data-rel='dialog' data-transition='slidedown'>"
								+el.getAttribute('label')+"</a></div></div>";
					var ref = l.getAttribute('ref');
					parent.appendChild(l);
					/*jQuery('.wiki_page_arbo').click(function(){
						console.log('lala');
					});*/
					if(el.getAttribute('ref') != "")
						this.nodes[el.getAttribute('ref')] = {id : "treelabel"+el.getAttribute('ref'), label : el.getAttribute('label'),pid : parent.id, owner : pref};
						
				}
			}
		}
		
	},
	
	parseTree : function(node,parent,pref){
		
		var f = document.createElement("div");
		f.className = "node";
		var id_node_first=node.childNodes[0].getAttribute('id');
		if(id_node_first=='0'){
		
			var link_add_page='<a href="javascript:;" onclick="javascript:jQuery(\'#add_page\').click();" class="title_fst_color">';
			jQuery('#tree').html('<div style="margin-left:15px;">'+sprintf(no_wiki_page_txt,link_add_page)+'</div>');
		
		}else{
		
			for (var x = 0; x < node.childNodes.length; x++) {

				var el = node.childNodes[x];
				if (el.nodeName == 'folder') {
				
					if(this.home==''){
						this.home = el.getAttribute('ref');
						//this.activenode = el.getAttribute('ref');
					} 
					this.n++;
					var f = document.createElement("div");
					f.className = "node";
					var edit_zone='';
					if(el.getAttribute('editable')=='1')edit_zone=" <span class='part_edit_page'><span class='separation_point'>.</span> <a href='"+url_dir+"/gest_wiki_page.php?action=edit&id="+el.getAttribute('ref')+"&"+url_suppl+"' class='menu_edit_page title_fst_color' data-fancybox-type='iframe'>"+editer_txt+"</a></span>";
					
					var html = "<div style='overflow:hidden;width:522px;' id='wiki_tree_"+el.getAttribute('ref')+"' class='wiki_page_arbo'><div class='toggle closed' id='toggle"+el.getAttribute('ref')
								+"' onclick='tree.togglenode("+el.getAttribute('ref')+")'></div><div id='"+"treelabel"
								+el.getAttribute('ref')+"' class='closelabel' onclick='tree.click(\""
								+el.getAttribute('ref')+"\");' onselectstart='return false;' ondblclick='tree.togglenode("+el.getAttribute('ref')+")'>"
								+el.getAttribute('label')+edit_zone+"</div></div>";
					var b = document.createElement("div");
					b.id = "treenode"+el.getAttribute('ref');
					f.innerHTML = html;		
					f.appendChild(b);	
					b.style.display = "none";	
					
					if(el.getAttribute('ref') != "")
						this.nodes[el.getAttribute('ref')] = {"ref": el.getAttribute('ref'), "id" : "treelabel"+el.getAttribute('ref'), label : el.getAttribute('label'),"pid" : parent.id, "owner" : pref};
					
					this.parseTree(el,b,el.getAttribute('ref'));
					parent.appendChild(f);
					
				}
				
				if (el.nodeName == 'leaf') {
					//console.log(el.getAttribute('ref'));
					if(this.home=='') this.home = el.getAttribute('ref');
					this.n++;
					var l = document.createElement("div");
					
					var edit_zone='';
					if(el.getAttribute('editable')=='1')edit_zone=" <span class='part_edit_page'><span class='separation_point'>.</span> <a href='"+url_dir+"/gest_wiki_page.php?action=edit&id="+el.getAttribute('ref')+"&"+url_suppl+"' class='menu_edit_page title_fst_color' data-fancybox-type='iframe'>"+editer_txt+"</a></span>";
					
					
					l.innerHTML = "<div style='overflow:hidden;width:522px;' class='wiki_page_arbo' id='wiki_tree_"+el.getAttribute('ref')+"'><div id='"+"treelabel"+el.getAttribute('ref')+"' class='leaf' onclick='tree.click(\""
								+el.getAttribute('ref')+"\");' onselectstart='return false;'>"
								+el.getAttribute('label')+edit_zone+"</div></div>";
					var ref = l.getAttribute('ref');
					parent.appendChild(l);
					/*jQuery('.wiki_page_arbo').click(function(){
						console.log('lala');
					});*/
					if(el.getAttribute('ref') != "")
						this.nodes[el.getAttribute('ref')] = {id : "treelabel"+el.getAttribute('ref'), label : el.getAttribute('label'),pid : parent.id, owner : pref};
						
				}

			}
			
			jQuery('.wiki_page_arbo').each(function(index) {
		
				jQuery('#'+jQuery(this).attr("id")).mouseover(function() {
					jQuery(this).find('.part_edit_page').show();
				}).mouseout(function(){
					jQuery(this).find('.part_edit_page').hide();
				});	
				
				jQuery(".menu_edit_page").fancybox({
					'autoSize':false,
					'width':450,
					'height':'auto',
					'scrolling':'no'
				});
				
			});  
			
		}
		
	},
	
	togglenode : function (id){
		var n = document.getElementById("treenode"+id);
		var s = document.getElementById("treelabel"+id);
		var t = document.getElementById("toggle"+id);
		if(n.style.display=='block'){
			n.style.display =  'none'; 
			s.className="closelabel";
            t.className="toggle closed"
		}else{
			n.style.display =  'block';
			s.className="nodelabel";
            t.className="toggle open"
		}
	},

	expandnode : function (node){
		if(!node) return;
		this.crumbs.push(node);
		if(node.owner == 0) return; // no more parents
		var n = document.getElementById(node.pid);
		var id = node.pid.substring(8); // treenodexxx
		var s = document.getElementById("treelabel"+id);
		var t = document.getElementById("toggle"+id);

		n.style.display =  'block';
		s.className="nodelabel";
        t.className="toggle open"

		this.expandnode(this.nodes[node.owner]);
	}
	
};
