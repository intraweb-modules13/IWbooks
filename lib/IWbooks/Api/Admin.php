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
// Autor del fitxer original: Jordi Fons (jfons@iespfq.org)
// --------------------------------------------------------------------------
// Prop�sit del fitxer:
//      Funcions API d'administraci� del m�dul  llibres
// --------------------------------------------------------------------------

/**
 Crear nova entrada de llibres
 */
function iw_books_adminapi_create($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
    $item = FormUtil::getPassedValue ('item');
    $item[etapa] = implode("|", $item[etapa]);
    
    if (!isset($item['lectura'])){
        $item['lectura'] = 0;
    }
    if (!isset($item['optativa'])){
        $item['optativa'] = 0;
    }
	
    if (!SecurityUtil::checkPermission('iw_books::Item', "$autor::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

  
    $result = DBUtil::insertObject ($item, 'iw_books','tid');
    if (!$result) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
    }

    ModUtil::callHooks('item', 'create', $item['tid'], array('module' => 'iw_books'));

    return $item['tid'];
	
	/*
	extract($args);
	$autor     = FormUtil::getPassedValue('autor');
	$titol     = FormUtil::getPassedValue('titol');
	$editorial = FormUtil::getPassedValue('editorial');
	$any_publi = FormUtil::getPassedValue('any_publi');
	$isbn      = FormUtil::getPassedValue('isbn');
	$codi_mat  = FormUtil::getPassedValue('codi_mat');
	$lectura   = FormUtil::getPassedValue('lectura');
	$any       = FormUtil::getPassedValue('any');
	$etapa     = implode("|",FormUtil::getPassedValue('etapa'));
	$nivell    = FormUtil::getPassedValue('nivell');
	$avaluacio = FormUtil::getPassedValue('avaluacio');
	$optativa  = FormUtil::getPassedValue('optativa');
	$observacions = FormUtil::getPassedValue('observacions');
	$materials = FormUtil::getPassedValue('materials');

	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', "$autor::", ACCESS_ADD)) {
		SessionUtil::setVar('errormsg', __('You are not allowed to enter this module', $dom));
		return false;
	}

	if (empty($titol) || empty($codi_mat) || empty($any) || empty($etapa) || empty($nivell)){
		SessionUtil::setVar('errormsg', __('ERROR: Some of the fields had no content. The book was not saved', $dom));
		return false;
	}

	$dbconn =& DBConnectionStack::getConnection*(true);
	$pntable =& DBUtil::getTables();

	$llibrestable = $pntable['llibres'];
	$llibrescolumn = &$pntable['llibres_column'];

	$nextId = $dbconn->GenId($llibrestable);

	$sql = "INSERT INTO $llibrestable (
	$llibrescolumn[tid],
	$llibrescolumn[autor],
	$llibrescolumn[titol],
	$llibrescolumn[editorial],
	$llibrescolumn[any_publi],
	$llibrescolumn[isbn],
	$llibrescolumn[codi_mat],
	$llibrescolumn[lectura],
	$llibrescolumn[any],
	$llibrescolumn[etapa],
	$llibrescolumn[nivell],
	$llibrescolumn[avaluacio],
	$llibrescolumn[optativa],
	$llibrescolumn[observacions],
	$llibrescolumn[materials])
            VALUES (
            $nextId,
              '" . DataUtil::formatForStore($autor) . "',
              '" . DataUtil::formatForStore($titol) . "',
              '" . DataUtil::formatForStore($editorial) . "',
              '" . DataUtil::formatForStore($any_publi) . "',
              '" . DataUtil::formatForStore($isbn) . "',
              '" . DataUtil::formatForStore($codi_mat) . "',
              '" . DataUtil::formatForStore($lectura) . "',
              '" . DataUtil::formatForStore($any) . "',
              '" . DataUtil::formatForStore($etapa) . "',
              '" . DataUtil::formatForStore($nivell) . "',
              '" . DataUtil::formatForStore($avaluacio) . "',
              '" . DataUtil::formatForStore($optativa) . "',
              '" . DataUtil::formatForStore($observacions) . "',
              '" . DataUtil::formatForStore($materials) . "')";

            $dbconn->Execute($sql);

            if ($dbconn->ErrorNo() != 0) {
            	SessionUtil::setVar('errormsg',  __('Error! Creation attempt failed.', $dom));
            	return false;
            }

            $tid = $dbconn->PO_Insert_ID($llibrestable, $llibrescolumn['tid']);

            ModUtil::callHooks('item', 'create', $tid, 'tid');

            return $tid;*/
}


// Esborrar llibre
function iw_books_adminapi_delete($args)
{
	$dom = ZLanguage::getModuleDomain('iw_books');
    extract($args);

	if (!isset($tid)) {
		SessionUtil::setVar('errormsg', __('Error! Could not do what you wanted. Please check your input.', $dom));
		return false;
	}

	$item = ModUtil::apiFunc('iw_books',
            'user',
            'get',
	array('tid' => $tid));

	if ($item == false) {
		$output->Text(_LLIBRESNOSUCHITEM);
		return $output->GetOutput();
	}

	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', "$item[etapa]::$tid", ACCESS_DELETE)) {
		SessionUtil::setVar('errormsg', __('You are not allowed to enter this module', $dom));
		return false;
	}

	$dbconn =& DBConnectionStack::getConnection*(true);
	$pntable =& DBUtil::getTables();

	$llibrestable = $pntable['iw_books'];
	$llibrescolumn = &$pntable['iw_books_column'];

	$sql = "DELETE FROM $llibrestable
            WHERE $llibrescolumn[tid] = '" . DataUtil::formatForStore($tid)."'";
	$dbconn->Execute($sql);

	if ($dbconn->ErrorNo() != 0) {
		SessionUtil::setVar('errormsg', __('Error! Sorry! Deletion attempt failed.', $dom));
		return false;
	}

	ModUtil::callHooks('item', 'delete', $tid, '');

	return true;
}

// Actualitzar llibre
function iw_books_adminapi_update($args)
{
	$dom = ZLanguage::getModuleDomain('iw_books');
    extract($args);
	
	$item = FormUtil::getPassedValue ('item');
    $item[etapa] = implode("|", $item[etapa]);
    
    if (!isset($item['lectura'])){
        $item['lectura'] = 0;
    }
    if (!isset($item['optativa'])){
        $item['optativa'] = 0;
    }
	
    if (!SecurityUtil::checkPermission('iw_books::Item', "$autor::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }
  
    $result = DBUtil::updateObject ($item, 'iw_books','', 'tid');
 
    if (!$result) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
     }

    ModUtil::callHooks('item', 'update', $item['tid'], array('module' => 'iw_books'));
    echo "WWWWWWWWWWWWWWWWWWWWWWWWWWW !.".$item['tid']; 
    return $item['tid'];

	
	
	/*

	if ((!isset($tid)) ||
	(!isset($autor)) ||
	(!isset($titol))) {
		SessionUtil::setVar('errormsg',  __('Error! Could not do what you wanted. Please check your input.', $dom));
		return false;
	}

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

	if (!SecurityUtil::checkPermission*(0, 'iw_books::Item', "$item[titol]::$tid", ACCESS_DELETE)) {
		SessionUtil::setVar('errormsg', __('You are not allowed to enter this module', $dom));
		return false;
	}

	$dbconn =& DBConnectionStack::getConnection*(true);
	$pntable =& DBUtil::getTables();

	$llibrestable = $pntable['iw_books'];
	$llibrescolumn = &$pntable['iw_books_column'];

	$sql = "UPDATE $llibrestable
            SET $llibrescolumn[autor]     = '" . DataUtil::formatForStore($autor) . "',
            $llibrescolumn[titol]     = '" . DataUtil::formatForStore($titol) . "',
            $llibrescolumn[editorial] = '" . DataUtil::formatForStore($editorial) . "',
            $llibrescolumn[any_publi] = '" . DataUtil::formatForStore($any_publi) . "',
            $llibrescolumn[isbn]      = '" . DataUtil::formatForStore($isbn) . "',
            $llibrescolumn[codi_mat]  = '" . DataUtil::formatForStore($codi_mat) . "',
            $llibrescolumn[lectura]   = '" . DataUtil::formatForStore($lectura) . "',
            $llibrescolumn[any]       = '" . DataUtil::formatForStore($any) . "',
            $llibrescolumn[etapa]     = '" . implode("|",FormUtil::getPassedValue('etapa')) . "',
            $llibrescolumn[nivell]    = '" . DataUtil::formatForStore($nivell) . "',
            $llibrescolumn[avaluacio] = '" . DataUtil::formatForStore($avaluacio) . "',
            $llibrescolumn[optativa]  = '" . DataUtil::formatForStore($optativa) . "',
            $llibrescolumn[observacions]  = '" . DataUtil::formatForStore($observacions) . "',
            $llibrescolumn[materials]  = '" . DataUtil::formatForStore($materials) . "'
            WHERE $llibrescolumn[tid] = '" . DataUtil::formatForStore($tid)."'";
            $dbconn->Execute($sql);

            if ($dbconn->ErrorNo() != 0) {
            	SessionUtil::setVar('errormsg', __('Error! Update attempt failed.', $dom));
            	return false;
            }

            return true;
*/
}

// Crear nova matèria
function iw_books_adminapi_create_mat($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	//print_r ($args);
    $item = FormUtil::getPassedValue ('item');


    if (!SecurityUtil::checkPermission('iw_books::', "$item[materia]::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    //$result = DBUtil::insertObject ($item, 'materies', 'tid');
    $result = DBUtil::insertObject ($item, 'iw_books_materies','tid');
 
    if (!$result) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
    }

    ModUtil::callHooks('item', 'create_mat', $item['tid'], array('module' => 'iw_books'));

    // Return the id of the newly created item to the calling process
    return $item['tid'];
		
}

// Esborrar matèria
function iw_books_adminapi_delete_mat($args)
{
    $dom = ZLanguage::getModuleDomain('iw_books');
	extract($args);

	if (!isset($tid)) {
		SessionUtil::setVar('errormsg', __('Error! Could not do what you wanted. Please check your input.', $dom));
		return false;
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
		SessionUtil::setVar('errormsg', __('You are not allowed to enter this module', $dom));
		return false;
	}

	$dbconn =& DBConnectionStack::getConnection*(true);
	$pntable =& DBUtil::getTables();

	$materiestable  = $pntable['materies'];
	$materiescolumn = &$pntable['materies_column'];

	$sql = "DELETE FROM $materiestable
            WHERE $materiescolumn[tid] = '" . DataUtil::formatForStore($tid)."'";
	$dbconn->Execute($sql);

	if ($dbconn->ErrorNo() != 0) {
		SessionUtil::setVar('errormsg', __('Error! Sorry! Deletion attempt failed.', $dom));
		return false;
	}

	ModUtil::callHooks('item', 'delete', $tid, '');

	return true;
}


// Actualitzar matèria
function iw_books_adminapi_update_mat($args)
{

	$dom = ZLanguage::getModuleDomain('iw_books');
	extract($args);
	
	$item = FormUtil::getPassedValue ('item');


    if (!SecurityUtil::checkPermission('iw_books::', "$item[materia]::$item[tid]", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }
  
    $result = DBUtil::updateObject ($item, 'iw_books_materies','', 'tid');
 
    if (!$result) {
        return LogUtil::registerError (__('Error! Update attempt failed.', $dom));
     }

    ModUtil::callHooks('item', 'update_mat', $item['tid'], array('module' => 'iw_books'));

    return $item['tid'];

	
}

?>
