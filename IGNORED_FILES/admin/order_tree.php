<?
$id_module=3;
require_once('../config/configuration.php');
$array_answer=array();
if($_POST['moved_node']!='' && $_POST['target_node']!='' && $_POST['operation']!=''){
	$success=0;
	$tree = new nestedTree($handle);
	
	//récupération des chiffres !
	$moved=substr($_POST['moved_node'],5,(strlen($_POST['moved_node'])-6));
	$target=substr($_POST['target_node'],5,(strlen($_POST['target_node'])-6));
	
	
	//vérification de l'existence des deux noeuds.	
	$moved_node=$tree->nstGetNodeWhere('ID='.$moved);
	if($_POST['operation']=='append'){
		$target_node=$tree->nstGetNodeWhere('ID='.$target.' AND TYPE = 0');
	}else $target_node=$tree->nstGetNodeWhere('ID='.$target);
	//pr($target_node);
	
	$target_node_lvl=$tree->nstLevel($target_node);
	$array_answer['target_node_lvl']=$target_node_lvl;
	
	if($target_node_lvl>2 && $_POST['operation']=='append'){
		$success='Votre arborescence ne peut pas dÃ©passer 3 niveaux de dossier.';
	}
	else if($tree->nstValidNode($moved_node) && $tree->nstValidNode($target_node) && (is_numeric($moved_node['l']) && is_numeric($moved_node['r'])) && (is_numeric($target_node['l']) && is_numeric($target_node['r']))){
		if($_POST['operation']=='append'){//si on insère le noeud au sein d'un autre
			if($tree->nstMoveToLastChild($moved_node,$target_node)){
				$success=1;
			}
			else $success='Erreur lors de l\'append';
		}
		elseif($_POST['operation']=='below'){//si on insère le noeud en dessous d'un autre
			if($tree->nstMoveToNextSibling($moved_node,$target_node)){
				$success=1;
			}
			else $success='Erreur lors de l\'append';
		}
		elseif($_POST['operation']=='above'){//si on insère le noeud au dessus d'un autre
			if($tree->nstMoveToPrevSibling($moved_node,$target_node)){
				$success=1;
			}
			else $success='Erreur lors de l\'append';
		}
		else{
			$success='Aucune opération reconnue !';
		}
	}else{
		$success='Erreur dans les noeuds : Invalide';
	}
	
	$array_answer['succes']=$success;
	$array_answer['moved']=$moved;
	$array_answer['target']=$target;
	$array_answer['operation']=$_POST['operation'];

}
echo json_encode($array_answer);
?>