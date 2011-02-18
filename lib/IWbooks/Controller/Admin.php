<?php
// ----------------------------------------------------------------------
// Copyright (C) 2006 per Jordi Fons
// ----------------------------------------------------------------------
// Aquest programa fa �s de les funcions de l'API de PostNuke
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// --------------------------------------------------------------------------
// Llic�ncia
//
// Aquest programa �s de software lliure. Pot redistribuir-lo i/o modificar-lo
// sota els termes de la Llic�ncia P�blica General de GNU segons est� publicada
// per la Free Software Foundation, b� de la versi� 2 de l'esmentada Llic�ncia
// o b� (segons la seva elecci�) de qualsevol versi� posterior.
//
// Aquest programa es distribueix amb l'esperan�a que sigui �til, per� sense
// cap garantia, fins i tot sense la garantia MERCANTIL impl�cita o sense
// garantir la conveni�ncia per a un prop�sit particular. Consulti la Llic�ncia
// General de GNU per a m�s detalls.
//
// Pots trobar la Llic�ncia a http://www.gnu.org/copyleft/gpl.html
// --------------------------------------------------------------------------
// Autor del fitxer original: Jordi Fons (jfons@iespfq.cat)
// --------------------------------------------------------------------------
// Prop�sit del fitxer:
//      Funcions d'administraci� del m�dul llibres
// --------------------------------------------------------------------------


function iw_books_admin_main()
{
   if (!SecurityUtil::checkPermission('iw_books::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
   }

   $view = Zikula_View::getInstance('iw_books');
 
   return $view->fetch('iw_books_admin_main.htm');


}

/**
 * Show the module information
 * @author	Albert Pérez Monfort (aperezm@xtec.cat)
 * @return	The module information
 */
function iw_books_admin_module(){
    $dom = ZLanguage::getModuleDomain('iw_books');
	// Security check
	if (!SecurityUtil::checkPermission('iw_books::', "::", ACCESS_READ)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_books',false);

	$module = ModUtil::func('iw_main', 'user', 'module_info', array('module_name' => 'iw_books', 'type' => 'admin'));

	$view -> assign('menu', iw_books_adminmenu1());
	$view -> assign('module', $module);
	return $view -> fetch('iw_books_admin_module.htm');
}

function iw_books_admin_new()
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	// Security check
	if (!SecurityUtil::checkPermission('iw_books::', "::", ACCESS_READ)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_books',false);
	
   $view->assign('anyini', ModUtil::getVar('iw_books', 'any'));
   
   $aplans = ModUtil::apiFunc('iw_books', 'user', 'plans', array('tots' => false));
   $view->assign('aplans', $aplans);
   
   $anivells = ModUtil::apiFunc('iw_books', 'user', 'nivells', array('blanc' => true));
   $view->assign('anivells', $anivells);
   
   $amateries = ModUtil::apiFunc('iw_books', 'user', 'materies',array('nou' => 1));
   $view->assign('amateries', $amateries);
	
	
   	$avaluacions = array('' => '---',
   						'1' => '1a',
   	 					'2' => '2a',
   						'3' => '3a');
   	   	
   	$view->assign('avaluacions', $avaluacions);

	return $view -> fetch('iw_books_admin_new.htm');	

}


function iw_books_admin_create($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	list($name,
	$number) = FormUtil::getPassedValue('name',
                                        'number');

	extract($args);

	if (!SecurityUtil::confirmAuthKey()) {
		SessionUtil::setVar('errormsg', __('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.', $dom));
		System::redirect(ModUtil::url('iw_books', 'admin', 'view'));
		return true;
	}

	$tid = ModUtil::apiFunc('iw_books',
                        'admin',
                        'create', array('name' => $name, 'number' => $number));

	if ($tid != false) {
		// Success
		SessionUtil::setVar('statusmsg', __('The new book has been created', $dom).$codi_mat);
	}

	System::redirect(ModUtil::url('iw_books', 'admin', 'view'));

	// Return
	return true;
}



function iw_books_admin_modify($args)
{
	$dom = ZLanguage::getModuleDomain('iw_books');
	$tid      = (int)FormUtil::getPassedValue ('tid');
    $objectid = (int)FormUtil::getPassedValue ('objectid');
  
    if (!empty($objectid)) {
        $tid = $objectid;
    }

    $item = ModUtil::apiFunc('iw_books', 'user', 'get',
                          array('tid' => $tid));

    if (!$item) {
        return LogUtil::registerError (__('No such item found.', $dom), 404);
    }

    if (!SecurityUtil::checkPermission('iw_books::', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    $view = Zikula_View::getInstance('iw_books', false);
    $view->caching = false;
   
    $aplans = ModUtil::apiFunc('iw_books', 'user', 'plans', array('tots' => false));
    $view->assign('aplans', $aplans);
    $separa = explode("|", $item['etapa']); 
    $view->assign('plaselec', $separa);
   
    $anivells = ModUtil::apiFunc('iw_books', 'user', 'nivells', array('blanc' => true));
    $view->assign('anivells', $anivells);
    $view->assign('nivellselec', $item['nivell']);
   
    $amateries = ModUtil::apiFunc('iw_books', 'user', 'materies',array('nou' => 1));
    $view->assign('amateries', $amateries);
    $view->assign('materiaselec', $item['codi_mat']);
	
    $aavaluacions = array('' => '---',
   						'1' => '1a',
   	 					'2' => '2a',
   						'3' => '3a');
    $view->assign('aavaluacions', $aavaluacions);
   	$view->assign('avaluacioselec', $item['avaluacio']);
    
   	//$view->assign('tid',$item['tid']);
    $view->assign($item);
    
    return $view->fetch('iw_books_admin_modify.htm');
}



function iw_books_admin_update($args)
{
	$dom = ZLanguage::getModuleDomain('iw_books');
    $item = FormUtil::getPassedValue ('item');
    
    if (isset($args['objectid']) && !empty($args['objectid'])) {
        $item['tid'] = $args['objectid'];
    }

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (ModUtil::url('iw_books', 'admin', 'view'));
    }
    
    if (ModUtil::apiFunc('iw_books', 'admin', 'update',
                    array('item' => $item))) {
        // Success
        LogUtil::registerStatus (__('Done! Item updated.', $dom));
    }

    return System::redirect(ModUtil::url('iw_books', 'admin', 'view'));
}

// Esborrar un element
function iw_books_admin_delete($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	list($tid,
	$objectid,
	$confirmation,
	$titol) = FormUtil::getPassedValue('tid',
                                              'objectid',
                                              'confirmation',
                                              'titol');

	extract($args);

	if (!empty($objectid)) {
		$tid = $objectid;
	}

	// Load API.  Note that this is loading the user API, that is because the
	if (!ModUtil::loadApi('iw_books', 'user')) {
		$output->Text(__('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}

	// The user API function is called.  This takes the item ID which we
	$item = ModUtil::apiFunc('iw_books',
                         'user',
                         'get',
	array('tid' => $tid));

	if ($item == false) {
		$output->Text(_LLIBRESNOSUCHITEM);
		return $output->GetOutput();
	}

	// Security check - important to do this as early as possible to avoid
	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', "$item[name]::$tid", ACCESS_DELETE)) {
		$output->Text(__('You are not allowed to enter this module', $dom));
		return $output->GetOutput();
	}

	// Check for confirmation.
	if (empty($confirmation)) {
		$output =& new pnHTML();

		$output->SetInputMode(_PNH_VERBATIMINPUT);
		$output->Text(iw_books_adminmenu());
		$output->SetInputMode(_PNH_PARSEINPUT);

		$output->Title(__('Remove selected book', $dom));

		$output->ConfirmAction(__('Confirm the elimination of the selected book', $dom).": ".$titol,
		ModUtil::url('iw_books',
                                        'admin',
                                        'delete'),
		__('Cancel the elimination', $dom),
		DataUtil::formatForDisplay(
		ModUtil::url('iw_books',
                                        'admin',
                                        'view')),
		array('tid' => $tid));

		// Return the output that has been generated by this function
		return $output->GetOutput();
	}

	if (!SecurityUtil::confirmAuthKey()) {
		SessionUtil::setVar('errormsg', __('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.', $dom));
		System::redirect(ModUtil::url('iw_books', 'admin', 'view'));
		return true;
	}

	// Load API.  All of the actual work for the deletion of the item is done
	if (!ModUtil::loadApi('iw_books', 'admin')) {
		$output->Text(__('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}

	// The API function is called.  Note that the name of the API function and
	if (ModUtil::apiFunc('iw_books',
                     'admin',
                     'delete',
	array('tid' => $tid))) {
		// Success
		SessionUtil::setVar('statusmsg', __('The book has been deleted', $dom));
	}

	System::redirect(ModUtil::url('iw_books', 'admin', 'view'));

	return true;
}

/**
 * Veure llibres
 */
function iw_books_admin_view()

{
    $dom = ZLanguage::getModuleDomain('iw_books');
	include_once('pnfpdf.php');
	
	if ( FormUtil::getPassedValue ('pdf') != ''){
		$any     = FormUtil::getPassedValue ('curs');
		$etapa   = FormUtil::getPassedValue ('etapa');
		$materia = FormUtil::getPassedValue ('materia');
		$nivell  = FormUtil::getPassedValue ('nivell');
				
		$file = generapdfadmin(array('any' => $any,
		'materia'  => $materia,
		'etapa'    => $etapa,
		'nivell'   => $nivell));
	}
		
	$view = Zikula_View::getInstance('iw_books');
	
	if (FormUtil::getPassedValue ('curs') != ""){
   		$any     = FormUtil::getPassedValue ('curs');
   		$etapa     = FormUtil::getPassedValue ('etapa');
   		$nivell  = FormUtil::getPassedValue ('nivell');
   		$materia = FormUtil::getPassedValue ('materia');
   		
   		$view->assign('cursselec', $any);   
		$view->assign('plaselec', $etapa);  
		$view->assign('nivellselec', $nivell);  
		$view->assign('materiaselec', $materia);

		$view->assign('cursacad', ModUtil::apiFunc('iw_books', 'user', 'cursacad', array('any' => $any)) );
		$view->assign('nivell_abre', ModUtil::apiFunc('iw_books', 'user', 'reble', array('nivell' => $nivell)) );
		if ($etapa == "TOT"){
			$view->assign('mostra_pla', "| Tots els plans" );
		}else{
			$view->assign('mostra_pla', " | ".ModUtil::apiFunc('iw_books', 'user', 'descriplans', array('etapa' => $etapa)) );
		}
		if ($materia == "TOT"){
			$view->assign('mostra_mat', " | Totes les matèries ");
		}else{
			$view->assign('mostra_mat', " | ".ModUtil::apiFunc('iw_books', 'user', 'nommateria', array('codi_mat' => $materia)) );
		}
	}else{
		$any  = ModUtil::getVar('iw_books', 'any');
		$etapa = 'TOT';
		$nivell = '';
		$materia = 'TOT';
		
		$view->assign('cursselec', $any );   
		$view->assign('plaselec', $etapa);  
		$view->assign('nivellselec', $nivell);  
		$view->assign('materiaselec', $materia);  	
		
		$view->assign('cursacad', ModUtil::apiFunc('iw_books', 'user', 'cursacad', array('any' => $any)) );
		$view->assign('nivell_abre', ModUtil::apiFunc('iw_books', 'user', 'reble', array('nivell' => $nivell)) );
	//		$view->assign('mostra_pla', " | ".ModUtil::apiFunc('iw_books', 'user', 'descriplans', array('etapa' => $etapa)) );
		$view->assign('mostra_pla', " | Tots els plans" );
		//$view->assign('mostra_mat', " | ".ModUtil::apiFunc('iw_books', 'user', 'nommateria', array('codi_mat' => $materia)) );
		$view->assign('mostra_mat', " | Totes les matèries ");
		
   }	
   
   $startnum = (int)FormUtil::getPassedValue ('startnum', 0)-1;
 
   if (!SecurityUtil::checkPermission('iw_books::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
   }

   //$view = Zikula_View::getInstance('iw_books');
   
   $aanys = ModUtil::apiFunc('iw_books', 'user', 'anys');
   asort($aanys);
   $view->assign('aanys', $aanys);
   
  /* if ($any == '') {
   		$view->assign('cursselec', ModUtil::getVar('iw_books', 'any'));
   }*/

   
   $aplans = ModUtil::apiFunc('iw_books', 'user', 'plans', array('tots' => true));
  // array_unshift($aplans['TOT'], 'Tots'));
   $view->assign('aplans', $aplans);
   
   $anivells = ModUtil::apiFunc('iw_books', 'user', 'nivells', array('blanc' => true));
   $view->assign('anivells', $anivells);
   
   $amateries = ModUtil::apiFunc('iw_books', 'user', 'materies', array('tots' => true));
   $view->assign('amateries', $amateries);
   
   $items = ModUtil::apiFunc('iw_books', 'user', 'getall',
                           array('startnum' => $startnum,
                                 'numitems' => ModUtil::getVar('iw_books', 'itemsperpage'),
                           		 'flag'     => 'admin',
                                 'any'      => $any,
                                 'etapa'    => $etapa,
                                 'nivell'   => $nivell,
                                 'materia'  => $materia,
                                 'lectura' => '1')) ;

                       
    foreach ($items as $key => $item) {
		if ( $items[$key]['lectura'] == 1){
			$items[$key]['lectura'] = "Sí";
		}else{
			$items[$key]['lectura'] = "No";
		}	
		
		if ( $items[$key]['optativa'] == 1){
			$items[$key]['optativa'] = "Sí";
		}else{
			$items[$key]['optativa'] = "No";
		}	
		
		if ( $items[$key]['materials'] != ""){
			$items[$key]['materials'] = "x";
		}else{
			$items[$key]['materials'] = "-";
		}	

    	if (SecurityUtil::checkPermission('iw_books::', "$item[titol]::$item[tid]", ACCESS_READ)) {
            $options = array();
            if (SecurityUtil::checkPermission('iw_books::', "$item[titol]::$item[tid]", ACCESS_EDIT)) {
                $options[] = array('url'   => ModUtil::url('iw_books', 'admin', 'modify', array('tid' => $item['tid'])),
                                   'image' => 'xedit.gif',
                                   'title' => __('Edit', $dom));
                if (SecurityUtil::checkPermission('iw_books::', "$item[titol]::$item[tid]", ACCESS_DELETE)) {
                    $options[] = array('url'   => ModUtil::url('iw_books', 'admin', 'delete', array('tid' => $item['tid'])),
                                       'image' => '14_layer_deletelayer.gif',
                                       'title' => __('Delete', $dom));
                }
            	if (SecurityUtil::checkPermission('iw_books::', "$item[titol]::$item[tid]", ACCESS_DELETE)) {
                    $options[] = array('url'   => ModUtil::url('iw_books', 'admin', 'copia', array('tid' => $item['tid'])),
                                       'image' => 'editcopy.gif',
                                       'title' => __('Copy the following year', $dom));
                }
            }

            $items[$key]['options'] = $options;
    	} 
    }

    $view->assign('iw_booksitems', $items);

	$numitems = ModUtil::apiFunc('iw_books', 'user', 'countitemsselect', array('any' => $any,
    													'etapa' => $etapa,
														'nivell' => $nivell,
    													'materia' => $materia,
														'lectura' => 1 ));
    												
    $view->assign('pager', array( 'numitems' => $numitems,
    								  'itemsperpage' => ModUtil::getVar('iw_books', 'itemsperpage')));

    $view->assign('llegenda',ModUtil::apiFunc('iw_books', 'user', 'llistaplans'));
   	
    return $view->fetch('iw_books_admin_view.htm');
}


/**
 * This is a standard function to modify the configuration parameters of the
 * module
 */
function iw_books_admin_modifyconfig()
{
	
    if (!SecurityUtil::checkPermission('activitats::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }
	
	$view = Zikula_View::getInstance('iw_books', false);
    $view->caching = false;
    $view->assign(ModUtil::getVar('iw_books'));
    
    // Check if iw_main module is available and root_dir exists
    $root_dir ='';
    $dir_exists = '1';
    $file_exists = '1';
    $modid = ModUtil::getIdFromName('iw_main');
    $info = ModUtil::getInfo($modid);
    if ($info['state'] == 3){
        $root_dir = ModUtil::getVar('iw_main', 'documentRoot');
    }
    if (!file_exists($root_dir)) $dir_exists = '0';
    
    //Check if declared header image exists
    $image = ModUtil::getVar('iw_books', 'encap');
    if ($image != ''){
        if (!file_exists($root_dir.'/'.$image)) $file_exists = '0';
    }  
    
    $multizk = (isset($GLOBALS['PNConfig']['Multisites']['multi']) && $GLOBALS['PNConfig']['Multisites']['multi'] == 1) ? 1 : 0;

	$view->assign('multizk', $multizk);
    $view->assign('file_exists', $file_exists);
    $view->assign('dir_exists', $dir_exists);
    $view->assign('root_dir', $root_dir);
    return $view->fetch('iw_books_admin_modifyconfig.htm');
}



/**
 * This is a standard function to update the configuration parameters of the
 * module given the information passed back by the modification form
 */
function iw_books_admin_updateconfig()
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	list($itemsperpage,
	$fpdf,
	$any,
	$encap,
	$darrer_nivell,
	$nivells,
	$plans,
	$mida_font,
	$llistar_materials,
	$marca_aigua) = FormUtil::getPassedValue('itemsperpage',
                                              'fpdf',
                                              'any',
                                              'encap',
                                              'darrer_nivell',
											  'nivells',	
                                              'plans',
                                              'mida_font',
                                              'llistar_materials',
                                              'marca_aigua');

	if (!SecurityUtil::confirmAuthKey()) {
		SessionUtil::setVar('errormsg', __('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.', $dom));
		System::redirect(ModUtil::url('iw_books', 'admin', 'modifyconfig'));
		return true;
	}

	if (!(file_exists($fpdf.'fpdf.php'))){
		SessionUtil::setVar('errormsg', "El camí per a la biblioteca 'fpdf' no és correcte: '".$fpdf."'");
		System::redirect(ModUtil::url('iw_books', 'admin', 'modifyconfig'));
		return true;
	}


	if ($llistar_materials == "") {
		$llistar_materials = 0;
	}

	if ($marca_aigua == "") {
		$marca_aigua = 0;
	}

	if (!isset($itemsperpage)) {
		$itemsperpage = 10;
	}

	ModUtil::setVar('iw_books', 'any', $any);
	ModUtil::setVar('iw_books', 'itemsperpage', $itemsperpage);
	ModUtil::setVar('iw_books', 'fpdf', $fpdf);
	ModUtil::setVar('iw_books', 'encap', $encap);
	ModUtil::setVar('iw_books', 'darrer_nivell', $darrer_nivell);
	ModUtil::setVar('iw_books', 'nivells', $nivells);
	ModUtil::setVar('iw_books', 'plans', $plans);
	ModUtil::setVar('iw_books', 'mida_font', $mida_font);
	ModUtil::setVar('iw_books', 'llistar_materials', $llistar_materials);
	ModUtil::setVar('iw_books', 'marca_aigua', $marca_aigua);

	SessionUtil::setVar('statusmsg', __('Configuration correctly updated', $dom));
	System::redirect(ModUtil::url('iw_books', 'admin', 'modifyconfig'));

	return true;
}


/**
 * Main administration menu
 */
function iw_books_adminmenu()
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	$output =& new pnHTML();

	$output->Text(LogUtil::getStatusMessages());
	//    $output->Linebreak(2);

	// Start options menu
	// $output->TableStart(__('Textbooks and lectures', $dom));
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->Text('<h1>'.__('Textbooks and lectures', $dom).'</h1>');
	$output->SetOutputMode(_PNH_RETURNOUTPUT);

	// Menu options.  These options are all added in a single row, to add
	// multiple rows of options the code below would just be repeated
	$columns = array();
	$columns[] = $output->URL(DataUtil::formatForDisplay(
	ModUtil::url('iw_books',
                                        'admin',
                                        'new')),
	__('Add a new book', $dom));
	$columns[] = $output->URL(DataUtil::formatForDisplay(
	ModUtil::url('iw_books',
                                        'admin',
                                        'view')),
	__('See all the books entered', $dom));

	if (SecurityUtil::checkPermission*(0, 'iw_books::', '::', ACCESS_ADMIN)) {
		$columns[] = $output->URL(DataUtil::formatForDisplay(
		ModUtil::url('iw_books',
                                        'admin',
                                        'new_mat')),
		__('Enter new subject', $dom));
	}

	$columns[] = $output->URL(DataUtil::formatForDisplay(
	ModUtil::url('iw_books',
                                        'admin',
                                        'view_mat')),
	__('Show subjects', $dom));

	if (SecurityUtil::checkPermission*(0, 'iw_books::', '::', ACCESS_ADMIN)) {
		$columns[] = $output->URL(DataUtil::formatForDisplay(
		ModUtil::url('iw_books',
                                        'admin',
                                        'modifyconfig')),
		__('Config', $dom));

		$columns[] = $output->URL(DataUtil::formatForDisplay(
		ModUtil::url('iw_books',
                                        'admin',
                                        'copia_prev')),
		__('Copy the following year', $dom));

		$columns[] = $output->URL(DataUtil::formatForDisplay(
		ModUtil::url('iw_books',
                                        'admin',
                                        'exporta_csv')),
		__('Export books (CSV)', $dom));
	}

	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	$output->SetInputMode(_PNH_VERBATIMINPUT);
	//    $output->TableAddRow($columns);

	//    $output->TableEnd();

	$output->Text('<div class="pn-menu"> <span class="pn-menuitem-title"> [ ');

	$compta = 0;
	foreach($columns as $item){
		$compta++;
		$output->Text($item);

		if ($compta < count($columns))
		$output->Text(' | ');
	}

	$output->Text(' ] </span> </div>');

	$output->SetInputMode(_PNH_PARSEINPUT);

	// Return the output that has been generated by this function
	return $output->GetOutput();
}


function iw_books_admin_copia($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	$tid      = (int)FormUtil::getPassedValue ('tid');
    $objectid = (int)FormUtil::getPassedValue ('objectid');
  
    if (!empty($objectid)) {
        $tid = $objectid;
    }

    $item = ModUtil::apiFunc('iw_books', 'user', 'get',
                          array('tid' => $tid));

    if (!$item) {
        return LogUtil::registerError (__('No such item found.', $dom), 404);
    }

    if (!SecurityUtil::checkPermission('iw_books::', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    $view = Zikula_View::getInstance('iw_books', false);
    $view->caching = false;
   
    $aplans = ModUtil::apiFunc('iw_books', 'user', 'plans', array('tots' => false));
    $view->assign('aplans', $aplans);
    $separa = explode("|", $item['etapa']); 
    $view->assign('plaselec', $separa);
   
    $anivells = ModUtil::apiFunc('iw_books', 'user', 'nivells', array('blanc' => true));
    $view->assign('anivells', $anivells);
    $view->assign('nivellselec', $item['nivell']);
   
    $amateries = ModUtil::apiFunc('iw_books', 'user', 'materies',array('nou' => 1));
    $view->assign('amateries', $amateries);
    $view->assign('materiaselec', $item['codi_mat']);
	
    $aavaluacions = array('' => '---',
   						'1' => '1a',
   	 					'2' => '2a',
   						'3' => '3a');
    $view->assign('aavaluacions', $aavaluacions);
   	$view->assign('avaluacioselec', $item['avaluacio']);

   	$view->assign('copia',1);

    $view->assign($item);

    
    return $view->fetch('iw_books_admin_modify.htm');
	
	
	
    
    
    /*
    
	list($tid,
	$objectid)= FormUtil::getPassedValue('tid',
                                'objectid');

	extract($args);

	if (!empty($objectid)) {
		$tid = $objectid;
	}

	$output =& new pnHTML();

	if (!ModUtil::loadApi('iw_books', 'user')) {
		$output->Text(__('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}

	$item = ModUtil::apiFunc('iw_books',
                         'user',
                         'get',
	array('tid' => $tid));

	if ($item == false) {
		$output->Text(_LLIBRESNOSUCHITEM);
		return $output->GetOutput();
	}

	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', "$item[name]::$tid", ACCESS_EDIT)) {
		$output->Text(__('You are not allowed to enter this module', $dom));
		return $output->GetOutput();
	}

	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->Text(iw_books_adminmenu());
	$output->SetInputMode(_PNH_PARSEINPUT);

	$output->Title(__('Copy the book', $dom));

	$output->FormStart(ModUtil::url('iw_books', 'admin', 'create'));

	$output->FormHidden('authid', SecurityUtil::generateAuthKey());

	$output->FormHidden('tid', DataUtil::formatForDisplay($tid));

	$output->TableStart();

	// Autor
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Author', $dom)));
	$row[] = $output->FormText('autor', DataUtil::formatForDisplay($item['autor']), 50, 50);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// T�tol
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Title', $dom)));
	$row[] = $output->FormText('titol', DataUtil::formatForDisplay($item['titol']), 50, 50);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Editorial
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Editorial', $dom)));
	$row[] = $output->FormText('editorial', DataUtil::formatForDisplay($item['editorial']), 50, 50);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Any de publicaci�
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Release Year', $dom)));
	$row[] = $output->FormText('any_publi', DataUtil::formatForDisplay($item['any_publi']), 4, 4);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// ISBN
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('ISBN', $dom)));
	$row[] = $output->FormText('isbn', DataUtil::formatForDisplay($item['isbn']), 20, 20);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Codi de mat�ria
	$data0 = ModUtil::apiFunc('iw_books', 'user', 'materies', array('nou' => '1'));
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Subject', $dom)));
	$row[] = $output->FormSelectMultiple('codi_mat', $data0, 0, 1, DataUtil::formatForDisplay($item['codi_mat']));
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	//Per al proc�s de c�pia, sumem un any al del llibre seleccionat per a copiar
	$noucurs = $item['any']+1;

	// Any acad�mic
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Academic Year', $dom)));
	$row[] = $output->FormText('any', DataUtil::formatForDisplay($noucurs), 4, 4);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);
	 
	// Etapa
	// Obtenim array amb els plans entrats
	$data = ModUtil::apiFunc('iw_books', 'user', 'plansselec', array('etapa' => $item['etapa']));
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Plan (Key 'Ctrl' for choose more plans)', $dom)));
	$row[] = $output->FormSelectMultiple('etapa[]', $data, 1, 6);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Nivell
	// Obtenir array amb els plans possibles
	$data2 = ModUtil::apiFunc('iw_books', 'user', 'nivells');
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Level', $dom)));
	$row[] = $output->FormSelectMultiple('nivell', $data2, 0, 1,DataUtil::formatForDisplay($item['nivell']));
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	//Optativa?
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Optional?', $dom)));
	$row[] = $output->FormCheckbox('optativa', $item['optativa']);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	//Lectura?
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Read?', $dom)));
	$row[] = $output->FormCheckbox('lectura', $item['lectura']);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Avaluaci� de lectura (si �s una lectura)
	$data3 = array( array('id'=>'', 'name' => '---'),
	array('id'=>'1', 'name' => '1a'),
	array('id'=>'2', 'name' => '2a'),
	array('id'=>'3', 'name' => '3a'));
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Evaluation (Read Only)', $dom)));
	$row[] = $output->FormSelectMultiple('avaluacio', $data3, 0, 1,DataUtil::formatForDisplay($item['avaluacio']));
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Observacions
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Comment', $dom)));
	$row[] = $output->FormText('obervacions', DataUtil::formatForDisplay($item['observacions']), 50, 100);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Complements (materials complementari)
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Home course materials (books, brushes ...)', $dom)));
	$row[] = $output->FormTextArea('materials', DataUtil::formatForDisplay($item['materials']), 5, 60);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	$output->TableEnd();

	// End form
	$output->Linebreak(2);
	$output->FormSubmit(__('Copy this book', $dom));
	$output->FormEnd();

	return $output->GetOutput();
	*/
}

function iw_books_admin_new_mat()
{
	$dom = ZLanguage::getModuleDomain('iw_books');
    if (!SecurityUtil::checkPermission*(0, 'iw_books::', '::', ACCESS_ADD)) {
        return DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom));
    }

    $view = Zikula_View::getInstance('iw_books', false);

    return $view->fetch('iw_books_admin_new_mat.htm');
	
	
	/*
	include('pnfpdf.php');

	$output =& new pnHTML();

	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', '::', ACCESS_ADMIN)) {
		$output->Text(__('You are not allowed to enter this module', $dom));
		return $output->GetOutput();
	}

	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->Text(iw_books_adminmenu());
	$output->SetInputMode(_PNH_PARSEINPUT);

	$output->Title(__('Enter new subject', $dom));

	$output->FormStart(ModUtil::url('iw_books', 'admin', 'create_mat'));

	$output->FormHidden('authid', SecurityUtil::generateAuthKey());

	$output->TableStart();

	// Codi mat�ria
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Code subject', $dom)));
	$row[] = $output->FormText('codimat', '', 3, 3);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Mat�ria
	$row = array();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(DataUtil::formatForDisplay(__('Subject', $dom)));
	$row[] = $output->FormText('materia', '', 50, 50);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddrow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	$output->TableEnd();

	// End form
	$output->Linebreak(2);
	$output->FormSubmit(__('Enter new subject', $dom));
	$output->FormEnd();

	return $output->GetOutput();
	
	*/
}

function iw_books_admin_create_mat($args)
{
	$dom = ZLanguage::getModuleDomain('iw_books');
	$item = FormUtil::getPassedValue ('item');

    if (!SecurityUtil::checkPermission('iw_books::', "$item[materia]::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (ModUtil::url('iw_books', 'admin', 'view_mat'));
    }

    $tid = ModUtil::apiFunc('iw_books', 'admin', 'create_mat',
                         array('item' => $item));
    if ($tid) {
        // Success
        LogUtil::registerStatus (__('Done! Item created.', $dom));
    }

    return System::redirect(ModUtil::url('iw_books', 'admin', 'view_mat'));
	
	
	/*
	
	list($name,
	$number) = FormUtil::getPassedValue('name',
                                        'number');
	extract($args);

	if (!SecurityUtil::confirmAuthKey()) {
		SessionUtil::setVar('errormsg', __('Invalid 'authkey':  this probably means that you pressed the 'Back' button, or that the page 'authkey' expired. Please refresh the page and try again.', $dom));
		System::redirect(ModUtil::url('iw_books', 'admin', 'view_mat'));
		return true;
	}
	if (!ModUtil::loadApi('iw_books', 'admin')) {
		SessionUtil::setVar('errormsg', __('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}
	$tid = ModUtil::apiFunc('iw_books',
                        'admin',
                        'create_mat',
	array('name' => $name,
                              'number' => $number));
	if ($tid != false) {
		// Success
		SessionUtil::setVar('statusmsg', __('The new suject has been created', $dom));
	}

	System::redirect(ModUtil::url('iw_books', 'admin', 'view_mat'));

	return true;
	*/
}


function iw_books_admin_view_mat($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	// $startnum = FormUtil::getPassedValue('startnum')-1;
	 $startnum = (int)FormUtil::getPassedValue ('startnum', 0);

	 
   if (!SecurityUtil::checkPermission('iw_books::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
   }

   $view = Zikula_View::getInstance('iw_books');
   
   $items = ModUtil::apiFunc('iw_books', 'user', 'getall_mat',
                           array('startnum' => $startnum,
                                 'numitems' => ModUtil::getVar('iw_books', 'itemsperpage')));
                           
    foreach ($items as $key => $item) {

    	if (SecurityUtil::checkPermission('iw_books::', "$item[materia]::$item[tid]", ACCESS_READ)) {
            $options = array();
            if (SecurityUtil::checkPermission('iw_books::', "$item[materia]::$item[tid]", ACCESS_EDIT)) {
                $options[] = array('url'   => ModUtil::url('iw_books', 'admin', 'modify_mat', array('tid' => $item['tid'])),
                                   'image' => 'xedit.gif',
                                   'title' => __('Edit', $dom));
                if (SecurityUtil::checkPermission('iw_books::', "$item[materia]::$item[tid]", ACCESS_DELETE)) {
                    $options[] = array('url'   => ModUtil::url('iw_books', 'admin', 'delete_mat', array('tid' => $item['tid'])),
                                       'image' => '14_layer_deletelayer.gif',
                                       'title' => __('Delete', $dom));
                }
            }

            $items[$key]['options'] = $options;
            
            
    	} 
    }

    $view->assign('iw_booksitems', $items);
    $view->assign('pager', array('numitems'     => ModUtil::apiFunc('iw_books', 'user', 'countitemsmat'),
                                     'itemsperpage' => ModUtil::getVar('iw_books', 'itemsperpage')));
 
    return $view->fetch('iw_books_admin_view_mat.htm');
	
	
/*	
 * 	$startnum = FormUtil::getPassedValue('startnum');
	
	
	$output =& new pnHTML();

	if (!SecurityUtil::checkPermission*(0, 'iw_books::', '::', ACCESS_READ)) {
		$output->Text(__('You are not allowed to enter this module', $dom));
		return $output->GetOutput();
	}

	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->Text(iw_books_adminmenu());
	$output->SetInputMode(_PNH_PARSEINPUT);

	$output->Title(__('Show subjects', $dom));

	if (!ModUtil::loadApi('iw_books', 'user')) {
		$output->Text(__('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}

	// $numelem = nombre d'elements que retorna la selecci� sol�licitada
	$numelem   = ModUtil::apiFunc('iw_books', 'user', 'countitemsmat');

	$items = ModUtil::apiFunc('iw_books',
                          'user',
                          'getall_mat',
	array('startnum' => $startnum,
                                'codi_mat' => $codi_mat,
                                'materia'  => $materia,
                                'numitems' => ModUtil::getVar('iw_books',
                                                          'itemsperpage')));

	$cap = array(__('Code subject', $dom),
	__('Subject', $dom));

	if (SecurityUtil::checkPermission*(0, 'iw_books::', "$item[autor]::$item[tid]", ACCESS_ADMIN)) {
		$cap[] = __('Options', $dom);
	}
	 
	$output->TableStart('', $cap, 3);

	foreach ($items as $item) {

		if (SecurityUtil::checkPermission*(0, 'iw_books::', "$item[titol]::$item[tid]", ACCESS_OVERVIEW)) {
			 
			$row = array();
			$row[] = $item['codi_mat'];
			$row[] = $item['materia'];

			$output->SetOutputMode(_PNH_RETURNOUTPUT);
			$output->SetInputMode(_PNH_VERBATIMINPUT);

			if (SecurityUtil::checkPermission*(0, 'iw_books::', "$item[autor]::$item[tid]", ACCESS_ADMIN)) {
				if ($item['codi_mat'] != "TOT"){
					$options = array();
					$options[] = $output->URL(DataUtil::formatForDisplay(
					ModUtil::url('iw_books',
                                                   'admin',
                                                   'modify_mat',
					array('tid' => $item['tid']))),
                                          '<img src="modules/iw_books/pnimages/edit.gif" alt="'.__('Edit', $dom).'" title="'.__('Edit', $dom).'">');

					if (SecurityUtil::checkPermission*(0, 'iw_books::', "$item[name]::$item[tid]", ACCESS_ADMIN)) {
						$options[] = $output->URL(DataUtil::formatForDisplay(
						ModUtil::url('iw_books',
                                                       'admin',
                                                       'delete_mat',
						array('tid' => $item['tid']))),
                                              '<img src="modules/iw_books/pnimages/edit_remove.gif" alt="'.__('Delete', $dom).'" title="'.__('Delete', $dom).'">');
					}
				}
			}

			$options = join(' | ', $options);
			$output->SetInputMode(_PNH_VERBATIMINPUT);
			if ( isset($options) ){
				$row[] = $output->Text($options);
			}
			$output->SetOutputMode(_PNH_KEEPOUTPUT);
			$output->TableAddRow($row, 'left');
			$output->SetInputMode(_PNH_PARSEINPUT);
		}
	}
	$output->TableEnd();

	$output->Pager($startnum,
	$numelem,
	ModUtil::url('iw_books',
                             'admin',
                             'view_mat',
	array('startnum' => '%%')),
	ModUtil::getVar('iw_books', 'itemsperpage'));
	$output->Text('  (Total: '.$numelem.' mat�ries)');

	// Return the output that has been generated by this function
	return $output->GetOutput();
	
	
*/	
}


function iw_books_admin_delete_mat($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	list($tid,
	$objectid,
	$confirmation) = FormUtil::getPassedValue('tid',
                                              'objectid',
                                              'confirmation');

	extract($args);
	if (!empty($objectid)) {
		$tid = $objectid;
	}

	if (!ModUtil::loadApi('iw_books', 'user')) {
		$output->Text(__('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}

	$item = ModUtil::apiFunc('iw_books',
                         'user',
                         'get_mat',
	array('tid' => $tid));

	if ($item == false) {
		$output->Text(_LLIBRESNOSUCHITEM);
		return $output->GetOutput();
	}
	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', "$item[name]::$tid", ACCESS_DELETE)) {
		$output->Text(__('You are not allowed to enter this module', $dom));
		return $output->GetOutput();
	}

	if (empty($confirmation)) {
		$output =& new pnHTML();

		$output->SetInputMode(_PNH_VERBATIMINPUT);
		$output->Text(iw_books_adminmenu());
		$output->SetInputMode(_PNH_PARSEINPUT);

		$output->Title(__('Delete selected subject', $dom));

		$output->ConfirmAction(__('Do you really want to delete?', $dom),
		ModUtil::url('iw_books','admin','delete_mat'), __('Cancel the elimination', $dom),
		DataUtil::formatForDisplay(ModUtil::url('iw_books', 'admin', 'view_mat')), array('tid' => $tid));

		// Return the output that has been generated by this function
		return $output->GetOutput();
	}

	if (!SecurityUtil::confirmAuthKey()) {
		SessionUtil::setVar('errormsg', __('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.', $dom));
		System::redirect(ModUtil::url('iw_books', 'admin', 'view_mat'));
		return true;
	}
	if (!ModUtil::loadApi('iw_books', 'admin')) {
		$output->Text(__('Error! Could not load module.', $dom));
		return $output->GetOutput();
	}

	if (ModUtil::apiFunc('iw_books',
                     'admin',
                     'delete_mat',
					 array('tid' => $tid))) {
		// Success
		SessionUtil::setVar('statusmsg', __('The subject has been cleared', $dom));
	}
	System::redirect(ModUtil::url('iw_books', 'admin', 'view_mat'));

	// Return
	return true;
}


function iw_books_admin_modify_mat($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	$tid      = (int)FormUtil::getPassedValue ('tid');
    $objectid = (int)FormUtil::getPassedValue ('objectid');
  
    if (!empty($objectid)) {
        $tid = $objectid;
    }

    $item = ModUtil::apiFunc('iw_books', 'user', 'get_mat',
                          array('tid' => $tid));
                          
                                          

    if (!$item) {
        return LogUtil::registerError (__('No such item found.', $dom), 404);
    }

    if (!SecurityUtil::checkPermission('iw_books::', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    $view = Zikula_View::getInstance('iw_books', false);
    $view->caching = false;
   
   	//$view->assign('tid',$item['tid']);
    $view->assign($item);
    
      
    return $view->fetch('iw_books_admin_modify_mat.htm');
}

function iw_books_admin_update_mat($args)
{
	$dom = ZLanguage::getModuleDomain('iw_books');
	$item = FormUtil::getPassedValue ('item');
    if (isset($args['objectid']) && !empty($args['objectid'])) {
        $item['tid'] = $args['objectid'];
    }

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (ModUtil::url('iw_books', 'admin', 'view_mat'));
    }
    
    if (ModUtil::apiFunc('iw_books', 'admin', 'update_mat',
                    array('item' => $item))) {
        // Success
        LogUtil::registerStatus (__('Done! Item updated.', $dom));
    }

    return System::redirect(ModUtil::url('iw_books', 'admin', 'view_mat'));

}

/**
 * generate menu fragment
 */
function iw_books_adminmenu1()
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	$output =& new pnHTML();

	// Start options menu
	$output->Text(LogUtil::getStatusMessages());
	$output->Linebreak(2);
	$output->TableStart(__('Textbooks and lectures', $dom));
	$output->TableEnd();
	$output->Linebreak(1);
	$output->URL(DataUtil::formatForDisplay(ModUtil::url('iw_books','admin','main')),__('Back', $dom));
	$output->Linebreak(2);

	return $output->GetOutput();
}


function iw_books_admin_exporta_csv()
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	$any = ModUtil::getVar('iw_books', 'any');

	$where   = " WHERE pn_any = '$any' "; 
	$orderBy = ' pn_etapa, pn_nivell, pn_codi_mat ' ;
	
	$items = DBUtil::selectObjectArray ('iw_books', $where, $orderBy);
	
	$export .= '"ID","'.__('Autor', $dom).'","'.__('Title', $dom).'","'.__('Editorial', $dom).'","'.__('Release Year', $dom)
	           .'","'.__('ISBN', $dom).'","'.__('Mat', $dom).'","'.__('Pla', $dom).'","'.__('Level', $dom).'","'.__('Opt?', $dom)
	           .'","'.__('Lect?', $dom).'","'.__('Aval', $dom).'","'.__('Any', $dom).'","'.__('Comment', $dom).'","'.__('Materials', $dom)
	           .'"'.chr(13).chr(10);
	
	foreach($items as $item){
		    $export .= '"'.$item['tid'].'","'.
		    				$item['autor'].'","'.
		    				$item['titol'].'","'.
		    				$item['editorial'].'","'.
		    				$item['any_publi'].'","'.
		    				$item['isbn'].'","'.
		    				$item['codi_mat'].'","'.
		    				$item['etapa'].'","'.
		    				$item['nivell'].'","'.
		    				$item['optativa'].'","'.
		    				$item['lectura'].'","'.
		    				$item['avaluacio'].'","'.
		    				$item['any'].'","'.
		    				$item['observacions'].'","'.
		    				$item['materials'].'"'.
		    				chr(13).chr(10);
	}
	
	header("Content-Description: File Transfer");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=llibres$any.csv");
    echo $export;
    
    SessionUtil::setVar('statusmsg',"S'ha realitzat l'exportació del llibres de l'any $any al fitxer 'llibres$any.csv'");
    
    return true;
	//    return System::redirect(ModUtil::url('iw_books', 'admin', 'view'));
}



function iw_books_admin_copia_prev()
{
    $dom = ZLanguage::getModuleDomain('iw_books');
    if (!SecurityUtil::checkPermission*(0, 'iw_books::', '::', ACCESS_ADD)) {
        return DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom));
    }

    $view = Zikula_View::getInstance('iw_books', false);
    
 	$view->assign('any', ModUtil::getVar('iw_books', 'any'));
 	$view->assign('noucurs', ModUtil::getVar('iw_books', 'any')+1);
  
    return $view->fetch('iw_books_admin_copi_prev.htm');
}



function iw_books_admin_copia_tot($args)
{
	extract($args);
	$any = FormUtil::getPassedValue ('any');
	$noucurs = FormUtil::getPassedValue ('noucurs');
	        
	
	//$items = ModUtil::apiFunc('iw_books', 'user', 'getall');
	
	$where   = " WHERE pn_any = '$any' "; 
	$orderBy = ""; 
	
	$items = DBUtil::selectObjectArray ('iw_books', $where, $orderBy);

	$total = 0;
	foreach($items as $item){
		$total++;
		$item['any'] = $noucurs;
		$result = DBUtil::insertObject ($item, 'iw_books','tid');
	}

	//SessionUtil::setVar('errormsg', "S'ha copiat la totalitat dels llibres ($total) de l'any ".$any." a l'any ".$noucurs);
	SessionUtil::setVar('statusmsg',"S'ha copiat la totalitat dels llibres ($total) de l'any ".$any." a l'any ".$noucurs);

	return System::redirect(ModUtil::url('iw_books', 'admin', 'view'));
}
?>
