<?php

	function buttons ()	{
	    global $language;
		$html = "<div id='editbar'><div style='border:1px solid #ccc;float:left;height: 26px;background:url(\"".URL_DIR."/wiki/images/system/button-bar.png\");'>";
		$html .= "<div style='float:left;' onmouseover='editmenu(\"\");'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/format-text-bold.png' title='"._('Gras')."' onclick='editbuttons(\"*\");' /></div>";
		$html .= "<div style='float:left;' onmouseover='editmenu(\"\");'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/format-text-italic.png' title='"._('Italique')."' onclick='editbuttons(\"_\");' /></div>";
		$html .= "<div style='float:left;' onmouseover='editmenu(\"\");'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/insert-horizontal-rule.png' title='"._('Barre horizontale')."' onclick='editbuttons(\"HR\");' /></div>";
		$html .= "<div style='float:left;' onmouseover='editmenu(\"\");'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/format-list-unordered.png' title='"._('Liste')."' onclick='editbuttons(\"LI\");' /></div>";
		$html .= "<div style='float:left;' onmouseover='editmenu(\"\");'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/format-list-ordered.png' title='"._('Liste ordonnée')."' onclick='editbuttons(\"OL\");' /></div>";
		$html .= "<div style='float:left;' onmouseover='editmenu(\"\");'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/insert-link.png' title='"._('Lien')."' onclick='editbuttons(\"A\");' /></div>";
		/*$html .= "<div style='float:left;'><input type='image' class='image' src='".URL_DIR."/wiki/images/system/insert-image.png' title='".$language->insertimage."' onclick='imagepage()'; /></div>";
		$html .= "<div style='float:left;'><input  type='image' class='image' src='".URL_DIR."/wiki/images/system/plugin.png' title='Plugin - For advanced users only' onclick='editbuttons(\"Plugin\")'; /></div>";*/
		
		// dropdown menus
		$html .= "<div class='menuitem'><a href='javascript:dummy();' onmouseover='editmenu(\"hmenu\");' >Style</a>";
		$html .= "<div id='hmenu' class='editmenu'>";
		$html .= "<ul class='menu'><li class='menu'><a href='javascript:editbuttons(\"=\",\"hmenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />"._('Titre 1')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"==\",\"hmenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />"._('Titre 2')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"===\",\"hmenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />"._('Titre 3')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"====\",\"hmenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />"._('Titre 4')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"=====\",\"hmenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />"._('Titre 5')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"======\",\"hmenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />"._('Titre 6')."</a></li></ul></div></div>";

		/*$html .= "<div class='menuitem'><a href='javascript:dummy()'  onmouseover='editmenu(\"codemenu\");'>Code</a>";
		$html .= "<div id='codemenu' class='editmenu'>";
		$html .= "<ul class='menu' style='min-width:76px;><li class='menu'><a href='javascript:editbuttons(\"%\",\"codemenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />&lt;CODE&gt;</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"PRE\",\"codemenu\");'><img src='".URL_DIR."/wiki/images/system/bullet_white.png' class='menu' />&lt;PRE&gt;</a></li></ul></div></div>";*/
		
		$html .= "<div class='menuitem'><a href='javascript:dummy()' onmouseover='editmenu(\"textmenu\");' >"._('Texte')."</a>";
		$html .= "<div id='textmenu'  class='editmenu'>";
		$html .= "<ul class='menu'><li class='menu'><a href='javascript:editbuttons(\"^\",\"textmenu\");'><img src='".URL_DIR."/wiki/images/system/format-text-superscript.png' class='menu' />"._('Exposant')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"~\",\"textmenu\");'><img src='".URL_DIR."/wiki/images/system/format-text-subscript.png' class='menu' />"._('Indice')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"INS\",\"textmenu\");'><img src='".URL_DIR."/wiki/images/system/format-text-underline.png' class='menu' />"._('Souligné')."</a></li>";
		$html .= "<li class='menu'><a href='javascript:editbuttons(\"DEL\",\"textmenu\");'><img src='".URL_DIR."/wiki/images/system/format-text-strikethrough.png' class='menu' />"._('Barré')."</a></li>";
		//$html .= "<li class='menu'><a href='javascript:editbuttons(\"CSS\",\"textmenu\");'><img src='".URL_DIR."/wiki/images/system/css_add.png' class='menu' />CSS</a></li></ul></div></div>";
		
		$html .= "<div style='clear:both'></div></div></div></div>";

		return $html;
	}

?>