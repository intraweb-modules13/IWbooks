<?php

class IWbooks_Controller_Admin extends Zikula_AbstractController {

    public function postInitialize() {
        $this->view->setCaching(false);
    }

    public function main() {
        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        $view = Zikula_View::getInstance('IWbooks');
        return $view->fetch('IWbooks_admin_main.htm');
    }

    public function newItem() {
        // Security check
        if (!SecurityUtil::checkPermission('IWbooks::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        $aplans = ModUtil::apiFunc('IWbooks', 'user', 'plans', array('tots' => false));
        $anivells = ModUtil::apiFunc('IWbooks', 'user', 'nivells', array('blanc' => true));
        $amateries = ModUtil::apiFunc('IWbooks', 'user', 'materies', array('nou' => 1));
        $avaluacions = array('' => '---',
            '1' => '1a',
            '2' => '2a',
            '3' => '3a');
        return $this->view->assign('anyini', ModUtil::getVar('IWbooks', 'any'))
                        ->assign('aplans', $aplans)
                        ->assign('anivells', $anivells)
                        ->assign('amateries', $amateries)
                        ->assign('avaluacions', $avaluacions)
                        ->fetch('IWbooks_admin_new.htm');
    }

    public function create($args) {
        list($name,
                $number) = FormUtil::getPassedValue('name', 'number');
        extract($args);
        // Confirm authorisation code
        $this->checkCsrfToken();
        $tid = ModUtil::apiFunc('IWbooks', 'admin', 'create', array('name' => $name, 'number' => $number));
        if ($tid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The new book has been created') . $codi_mat);
        }
        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
    }

    public function modify($args) {
        $tid = (int) FormUtil::getPassedValue('tid');
        $objectid = (int) FormUtil::getPassedValue('objectid');

        if (!empty($objectid))
            $tid = $objectid;

        $item = ModUtil::apiFunc('IWbooks', 'user', 'get', array('tid' => $tid));
        if (!$item) {
            return LogUtil::registerError($this->__('No such item found.'), 404);
        }
        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $aplans = ModUtil::apiFunc('IWbooks', 'user', 'plans', array('tots' => false));
        $anivells = ModUtil::apiFunc('IWbooks', 'user', 'nivells', array('blanc' => true));
        $amateries = ModUtil::apiFunc('IWbooks', 'user', 'materies', array('nou' => 1));
        $separa = explode("|", $item['etapa']);
        $aavaluacions = array('' => '---',
            '1' => '1a',
            '2' => '2a',
            '3' => '3a');

        return $this->view->assign('aplans', $aplans)
                        ->assign('plaselec', $separa)
                        ->assign('anivells', $anivells)
                        ->assign('nivellselec', $item['nivell'])
                        ->assign('amateries', $amateries)
                        ->assign('materiaselec', $item['codi_mat'])
                        ->assign('aavaluacions', $aavaluacions)
                        ->assign('avaluacioselec', $item['avaluacio'])
                        ->assign('item', $item)
                        ->fetch('IWbooks_admin_modify.htm');
    }

    public function update($args) {
        $item = FormUtil::getPassedValue('item');
        if (isset($args['objectid']) && !empty($args['objectid'])) {
            $item['tid'] = $args['objectid'];
        }
        // Confirm authorisation code
        $this->checkCsrfToken();
        if (ModUtil::apiFunc('IWbooks', 'admin', 'update', array('item' => $item))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Item updated.'));
        }
        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
    }

    // Esborrar un element
    public function delete($args) {
        list($tid,
                $objectid,
                $confirmation,
                $titol) = FormUtil::getPassedValue('tid', 'objectid', 'confirmation', 'titol');

        extract($args);
        if (!empty($objectid))
            $tid = $objectid;

        // Load API.  Note that this is loading the user API, that is because the
        if (!ModUtil::loadApi('IWbooks', 'user')) {
            $output->Text($this->__('Error! Could not load module.'));
            return $output->GetOutput();
        }

        // The user API function is called.  This takes the item ID which we
        $item = ModUtil::apiFunc('IWbooks', 'user', 'get', array('tid' => $tid));

        if ($item == false) {
            $output->Text(_LLIBRESNOSUCHITEM);
            return $output->GetOutput();
        }

        // Security check - important to do this as early as possible to avoid
        if (!SecurityUtil::checkPermission('IWbooks::Item', "$item[name]::$tid", ACCESS_DELETE)) {
            $output->Text($this->__('You are not allowed to enter this module'));
            return $output->GetOutput();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            $output = & new pnHTML();
            $output->SetInputMode(_PNH_VERBATIMINPUT);
            $output->Text(IWbooks_adminmenu());
            $output->SetInputMode(_PNH_PARSEINPUT);
            $output->Title($this->__('Remove selected book'));
            $output->ConfirmAction($this->__('Confirm the elimination of the selected book') . ": " . $titol, ModUtil::url('IWbooks', 'admin', 'delete'), $this->__('Cancel the elimination'), DataUtil::formatForDisplay(
                            ModUtil::url('IWbooks', 'admin', 'view')), array('tid' => $tid));
            // Return the output that has been generated by this function
            return $output->GetOutput();
        }
        // Confirm authorisation code
        $this->checkCsrfToken();
        // Load API.  All of the actual work for the deletion of the item is done
        if (!ModUtil::loadApi('IWbooks', 'admin')) {
            $output->Text($this->__('Error! Could not load module.'));
            return $output->GetOutput();
        }
        // The API function is called.  Note that the name of the API function and
        if (ModUtil::apiFunc('IWbooks', 'admin', 'delete', array('tid' => $tid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The book has been deleted'));
        }
        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
    }

    /**
     * Veure llibres
     */
    public function view() {
        include_once('modules/IWbooks/pnfpdf.php');
        if (FormUtil::getPassedValue('pdf') != '') {
            $any = FormUtil::getPassedValue('curs');
            $etapa = FormUtil::getPassedValue('etapa');
            $materia = FormUtil::getPassedValue('materia');
            $nivell = FormUtil::getPassedValue('nivell');
            $file = generapdfadmin(array('any' => $any,
                'materia' => $materia,
                'etapa' => $etapa,
                'nivell' => $nivell));
        }

        $view = Zikula_View::getInstance('IWbooks');

        if (FormUtil::getPassedValue('curs') != "") {
            $any = FormUtil::getPassedValue('curs');
            $etapa = FormUtil::getPassedValue('etapa');
            $nivell = FormUtil::getPassedValue('nivell');
            $materia = FormUtil::getPassedValue('materia');

            $view->assign('cursselec', $any);
            $view->assign('plaselec', $etapa);
            $view->assign('nivellselec', $nivell);
            $view->assign('materiaselec', $materia);

            $view->assign('cursacad', ModUtil::apiFunc('IWbooks', 'user', 'cursacad', array('any' => $any)));
            $view->assign('nivell_abre', ModUtil::apiFunc('IWbooks', 'user', 'reble', array('nivell' => $nivell)));
            if ($etapa == "TOT") {
                $view->assign('mostra_pla', "| Tots els plans");
            } else {
                $view->assign('mostra_pla', " | " . ModUtil::apiFunc('IWbooks', 'user', 'descriplans', array('etapa' => $etapa)));
            }
            if ($materia == "TOT") {
                $view->assign('mostra_mat', " | Totes les matèries ");
            } else {
                $view->assign('mostra_mat', " | " . ModUtil::apiFunc('IWbooks', 'user', 'nommateria', array('codi_mat' => $materia)));
            }
        } else {
            $any = ModUtil::getVar('IWbooks', 'any');
            $etapa = 'TOT';
            $nivell = '';
            $materia = 'TOT';

            $view->assign('cursselec', $any);
            $view->assign('plaselec', $etapa);
            $view->assign('nivellselec', $nivell);
            $view->assign('materiaselec', $materia);

            $view->assign('cursacad', ModUtil::apiFunc('IWbooks', 'user', 'cursacad', array('any' => $any)));
            $view->assign('nivell_abre', ModUtil::apiFunc('IWbooks', 'user', 'reble', array('nivell' => $nivell)));
            //		$view->assign('mostra_pla', " | ".ModUtil::apiFunc('IWbooks', 'user', 'descriplans', array('etapa' => $etapa)) );
            $view->assign('mostra_pla', " | Tots els plans");
            //$view->assign('mostra_mat', " | ".ModUtil::apiFunc('IWbooks', 'user', 'nommateria', array('codi_mat' => $materia)) );
            $view->assign('mostra_mat', " | Totes les matèries ");
        }

        $startnum = (int) FormUtil::getPassedValue('startnum', 0) - 1;

        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $aanys = ModUtil::apiFunc('IWbooks', 'user', 'anys');
        asort($aanys);
        $view->assign('aanys', $aanys);

        $aplans = ModUtil::apiFunc('IWbooks', 'user', 'plans', array('tots' => true));
        // array_unshift($aplans['TOT'], 'Tots'));
        $view->assign('aplans', $aplans);

        $anivells = ModUtil::apiFunc('IWbooks', 'user', 'nivells', array('blanc' => true));
        $view->assign('anivells', $anivells);

        $amateries = ModUtil::apiFunc('IWbooks', 'user', 'materies', array('tots' => true));
        $view->assign('amateries', $amateries);

        $items = ModUtil::apiFunc('IWbooks', 'user', 'getall', array('startnum' => $startnum,
                    'numitems' => ModUtil::getVar('IWbooks', 'itemsperpage'),
                    'flag' => 'admin',
                    'any' => $any,
                    'etapa' => $etapa,
                    'nivell' => $nivell,
                    'materia' => $materia,
                    'lectura' => '1'));

        foreach ($items as $key => $item) {
            $items[$key]['lectura'] = ($items[$key]['lectura'] == 1) ? "Sí" : "No";
            $items[$key]['optativa'] = ($items[$key]['optativa'] == 1) ? "Sí" : "No";
            $items[$key]['materials'] = ($items[$key]['materials'] != "") ? "x" : "-";

            if (SecurityUtil::checkPermission('IWbooks::', "$item[titol]::$item[tid]", ACCESS_READ)) {
                $options = array();
                if (SecurityUtil::checkPermission('IWbooks::', "$item[titol]::$item[tid]", ACCESS_EDIT)) {
                    $options[] = array('url' => ModUtil::url('IWbooks', 'admin', 'modify', array('tid' => $item['tid'])),
                        'image' => 'xedit.gif',
                        'title' => $this->__('Edit'));
                    if (SecurityUtil::checkPermission('IWbooks::', "$item[titol]::$item[tid]", ACCESS_DELETE)) {
                        $options[] = array('url' => ModUtil::url('IWbooks', 'admin', 'delete', array('tid' => $item['tid'])),
                            'image' => '14_layer_deletelayer.gif',
                            'title' => $this->__('Delete'));
                    }
                    if (SecurityUtil::checkPermission('IWbooks::', "$item[titol]::$item[tid]", ACCESS_DELETE)) {
                        $options[] = array('url' => ModUtil::url('IWbooks', 'admin', 'copia', array('tid' => $item['tid'])),
                            'image' => 'editcopy.gif',
                            'title' => $this->__('Copy the following year'));
                    }
                }
                $items[$key]['options'] = $options;
            }
        }

        $view->assign('IWbooksitems', $items);

        $numitems = ModUtil::apiFunc('IWbooks', 'user', 'countitemsselect', array('any' => $any,
                    'etapa' => $etapa,
                    'nivell' => $nivell,
                    'materia' => $materia,
                    'lectura' => 1));

        $view->assign('pager', array('numitems' => $numitems,
            'itemsperpage' => ModUtil::getVar('IWbooks', 'itemsperpage')));

        $view->assign('llegenda', ModUtil::apiFunc('IWbooks', 'user', 'llistaplans'));

        return $view->fetch('IWbooks_admin_view.htm');
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     */
    public function modifyconfig() {

        if (!SecurityUtil::checkPermission('activitats::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $view = Zikula_View::getInstance('IWbooks', false);
        $view->caching = false;
        $view->assign(ModUtil::getVar('IWbooks'));
        // Check if IWmain module is available and root_dir exists
        $root_dir = '';
        $dir_exists = '1';
        $file_exists = '1';
        $modid = ModUtil::getIdFromName('IWmain');
        $info = ModUtil::getInfo($modid);
        if ($info['state'] == 3) {
            $root_dir = ModUtil::getVar('IWmain', 'documentRoot');
        }
        if (!file_exists($root_dir))
            $dir_exists = '0';

        //Check if declared header image exists
        $image = ModUtil::getVar('IWbooks', 'encap');
        if ($image != '') {
            if (!file_exists($root_dir . '/' . $image))
                $file_exists = '0';
        }

        $multizk = (isset($GLOBALS['PNConfig']['Multisites']['multi']) && $GLOBALS['PNConfig']['Multisites']['multi'] == 1) ? 1 : 0;

        $view->assign('multizk', $multizk);
        $view->assign('file_exists', $file_exists);
        $view->assign('dir_exists', $dir_exists);
        $view->assign('root_dir', $root_dir);
        return $view->fetch('IWbooks_admin_modifyconfig.htm');
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     */
    public function updateconfig() {
        list($itemsperpage,
                $fpdf,
                $any,
                $encap,
                $darrer_nivell,
                $nivells,
                $plans,
                $mida_font,
                $llistar_materials,
                $marca_aigua) = FormUtil::getPassedValue('itemsperpage', 'fpdf', 'any', 'encap', 'darrer_nivell', 'nivells', 'plans', 'mida_font', 'llistar_materials', 'marca_aigua');

        // Confirm authorisation code
        $this->checkCsrfToken();

        if (!(file_exists($fpdf . 'fpdf.php'))) {
            SessionUtil::setVar('errormsg', "El camí per a la biblioteca 'fpdf' no és correcte: '" . $fpdf . "'");
            System::redirect(ModUtil::url('IWbooks', 'admin', 'modifyconfig'));
            return true;
        }

        if ($llistar_materials == "")
            $llistar_materials = 0;

        if ($marca_aigua == "")
            $marca_aigua = 0;

        if (!isset($itemsperpage))
            $itemsperpage = 10;

        $this->setVar('any', $any)
                ->setVar('itemsperpage', $itemsperpage)
                ->setVar('fpdf', $fpdf)
                ->setVar('encap', $encap)
                ->setVar('darrer_nivell', $darrer_nivell)
                ->setVar('nivells', $nivells)
                ->setVar('plans', $plans)
                ->setVar('mida_font', $mida_font)
                ->setVar('llistar_materials', $llistar_materials)
                ->setVar('marca_aigua', $marca_aigua);

        SessionUtil::setVar('statusmsg', $this->__('Configuration correctly updated'));
        return System::redirect(ModUtil::url('IWbooks', 'admin', 'modifyconfig'));
    }

    /**
     * Main administration menu
     */
    function IWbooks_adminmenu() {
        $output = & new pnHTML();

        $output->Text(LogUtil::getStatusMessages());
        //    $output->Linebreak(2);
        // Start options menu
        // $output->TableStart($this->__('Textbooks and lectures'));
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('<h1>' . $this->__('Textbooks and lectures') . '</h1>');
        $output->SetOutputMode(_PNH_RETURNOUTPUT);

        // Menu options.  These options are all added in a single row, to add
        // multiple rows of options the code below would just be repeated
        $columns = array();
        $columns[] = $output->URL(DataUtil::formatForDisplay(
                        ModUtil::url('IWbooks', 'admin', 'new')), $this->__('Add a new book'));
        $columns[] = $output->URL(DataUtil::formatForDisplay(
                        ModUtil::url('IWbooks', 'admin', 'view')), $this->__('See all the books entered'));

        if (SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADMIN)) {
            $columns[] = $output->URL(DataUtil::formatForDisplay(
                            ModUtil::url('IWbooks', 'admin', 'new_mat')), $this->__('Enter new subject'));
        }

        $columns[] = $output->URL(DataUtil::formatForDisplay(
                        ModUtil::url('IWbooks', 'admin', 'view_mat')), $this->__('Show subjects'));

        if (SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADMIN)) {
            $columns[] = $output->URL(DataUtil::formatForDisplay(
                            ModUtil::url('IWbooks', 'admin', 'modifyconfig')), $this->__('Config'));

            $columns[] = $output->URL(DataUtil::formatForDisplay(
                            ModUtil::url('IWbooks', 'admin', 'copia_prev')), $this->__('Copy the following year'));

            $columns[] = $output->URL(DataUtil::formatForDisplay(
                            ModUtil::url('IWbooks', 'admin', 'exporta_csv')), $this->__('Export books (CSV)'));
        }

        $output->SetOutputMode(_PNH_KEEPOUTPUT);

        $output->SetInputMode(_PNH_VERBATIMINPUT);
        //    $output->TableAddRow($columns);
        //    $output->TableEnd();

        $output->Text('<div class="pn-menu"> <span class="pn-menuitem-title"> [ ');

        $compta = 0;
        foreach ($columns as $item) {
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

    public function copia($args) {
        $tid = (int) FormUtil::getPassedValue('tid');
        $objectid = (int) FormUtil::getPassedValue('objectid');

        if (!empty($objectid)) {
            $tid = $objectid;
        }

        $item = ModUtil::apiFunc('IWbooks', 'user', 'get', array('tid' => $tid));

        if (!$item) {
            return LogUtil::registerError($this->__('No such item found.'), 404);
        }

        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View::getInstance('IWbooks', false);
        $view->caching = false;

        $aplans = ModUtil::apiFunc('IWbooks', 'user', 'plans', array('tots' => false));
        $view->assign('aplans', $aplans);
        $separa = explode("|", $item['etapa']);
        $view->assign('plaselec', $separa);

        $anivells = ModUtil::apiFunc('IWbooks', 'user', 'nivells', array('blanc' => true));
        $view->assign('anivells', $anivells);
        $view->assign('nivellselec', $item['nivell']);

        $amateries = ModUtil::apiFunc('IWbooks', 'user', 'materies', array('nou' => 1));
        $view->assign('amateries', $amateries);
        $view->assign('materiaselec', $item['codi_mat']);

        $aavaluacions = array('' => '---',
            '1' => '1a',
            '2' => '2a',
            '3' => '3a');
        $view->assign('aavaluacions', $aavaluacions);
        $view->assign('avaluacioselec', $item['avaluacio']);

        $view->assign('copia', 1);

        $view->assign($item);


        return $view->fetch('IWbooks_admin_modify.htm');
    }

    public function new_mat() {
        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADD)) {
            return DataUtil::formatForDisplayHTML($this->__('Sorry! No authorization to access this module.'));
        }
        return $this->view->fetch('IWbooks_admin_new_mat.htm');
    }

    public function create_mat($args) {
        $item = FormUtil::getPassedValue('item');

        if (!SecurityUtil::checkPermission('IWbooks::', "$item[materia]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        // Confirm authorisation code
        $this->checkCsrfToken();

        $tid = ModUtil::apiFunc('IWbooks', 'admin', 'create_mat', array('item' => $item));
        // Success
        if ($tid)
            LogUtil::registerStatus($this->__('Done! Item created.'));

        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));
    }

    public function view_mat($args) {
        // $startnum = FormUtil::getPassedValue('startnum')-1;
        $startnum = (int) FormUtil::getPassedValue('startnum', 0);


        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View::getInstance('IWbooks');

        $items = ModUtil::apiFunc('IWbooks', 'user', 'getall_mat', array('startnum' => $startnum,
                    'numitems' => ModUtil::getVar('IWbooks', 'itemsperpage')));

        foreach ($items as $key => $item) {

            if (SecurityUtil::checkPermission('IWbooks::', "$item[materia]::$item[tid]", ACCESS_READ)) {
                $options = array();
                if (SecurityUtil::checkPermission('IWbooks::', "$item[materia]::$item[tid]", ACCESS_EDIT)) {
                    $options[] = array('url' => ModUtil::url('IWbooks', 'admin', 'modify_mat', array('tid' => $item['tid'])),
                        'image' => 'xedit.gif',
                        'title' => $this->__('Edit'));
                    if (SecurityUtil::checkPermission('IWbooks::', "$item[materia]::$item[tid]", ACCESS_DELETE)) {
                        $options[] = array('url' => ModUtil::url('IWbooks', 'admin', 'delete_mat', array('tid' => $item['tid'])),
                            'image' => '14_layer_deletelayer.gif',
                            'title' => $this->__('Delete'));
                    }
                }

                $items[$key]['options'] = $options;
            }
        }

        $view->assign('IWbooksitems', $items);
        $view->assign('pager', array('numitems' => ModUtil::apiFunc('IWbooks', 'user', 'countitemsmat'),
            'itemsperpage' => ModUtil::getVar('IWbooks', 'itemsperpage')));

        return $view->fetch('IWbooks_admin_view_mat.htm');
    }

    public function delete_mat($args) {
        list($tid,
                $objectid,
                $confirmation) = FormUtil::getPassedValue('tid', 'objectid', 'confirmation');

        extract($args);
        if (!empty($objectid)) 
            $tid = $objectid;

        if (!ModUtil::loadApi('IWbooks', 'user')) {
            $output->Text($this->__('Error! Could not load module.'));
            return $output->GetOutput();
        }

        $item = ModUtil::apiFunc('IWbooks', 'user', 'get_mat', array('tid' => $tid));

        if ($item == false) {
            $output->Text(_LLIBRESNOSUCHITEM);
            return $output->GetOutput();
        }
        if (!SecurityUtil::checkPermission('IWbooks::Item', "$item[name]::$tid", ACCESS_DELETE)) {
            $output->Text($this->__('You are not allowed to enter this module'));
            return $output->GetOutput();
        }

        if (empty($confirmation)) {
            $output = & new pnHTML();

            $output->SetInputMode(_PNH_VERBATIMINPUT);
            $output->Text(IWbooks_adminmenu());
            $output->SetInputMode(_PNH_PARSEINPUT);

            $output->Title($this->__('Delete selected subject'));

            $output->ConfirmAction($this->__('Do you really want to delete?'), ModUtil::url('IWbooks', 'admin', 'delete_mat'), $this->__('Cancel the elimination'), DataUtil::formatForDisplay(ModUtil::url('IWbooks', 'admin', 'view_mat')), array('tid' => $tid));

            // Return the output that has been generated by this function
            return $output->GetOutput();
        }

        // Confirm authorisation code
        $this->checkCsrfToken();
        if (!ModUtil::loadApi('IWbooks', 'admin')) {
            $output->Text($this->__('Error! Could not load module.'));
            return $output->GetOutput();
        }

        if (ModUtil::apiFunc('IWbooks', 'admin', 'delete_mat', array('tid' => $tid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The subject has been cleared'));
        }
        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));
    }

    public function modify_mat($args) {
        $tid = (int) FormUtil::getPassedValue('tid');
        $objectid = (int) FormUtil::getPassedValue('objectid');

        if (!empty($objectid)) {
            $tid = $objectid;
        }

        $item = ModUtil::apiFunc('IWbooks', 'user', 'get_mat', array('tid' => $tid));

        if (!$item) {
            return LogUtil::registerError($this->__('No such item found.'), 404);
        }

        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        return $this->view->assign($item)
                ->fetch('IWbooks_admin_modify_mat.htm');
    }

    public function update_mat($args) {
        $item = FormUtil::getPassedValue('item');
        if (isset($args['objectid']) && !empty($args['objectid'])) {
            $item['tid'] = $args['objectid'];
        }

        // Confirm authorisation code
        $this->checkCsrfToken();

        if (ModUtil::apiFunc('IWbooks', 'admin', 'update_mat', array('item' => $item))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Item updated.'));
        }

        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));
    }

    /**
     * generate menu fragment
     */
    function IWbooks_adminmenu1() {
        $output = & new pnHTML();

        // Start options menu
        $output->Text(LogUtil::getStatusMessages());
        $output->Linebreak(2);
        $output->TableStart($this->__('Textbooks and lectures'));
        $output->TableEnd();
        $output->Linebreak(1);
        $output->URL(DataUtil::formatForDisplay(ModUtil::url('IWbooks', 'admin', 'main')), $this->__('Back'));
        $output->Linebreak(2);

        return $output->GetOutput();
    }

    public function exporta_csv() {
        $any = ModUtil::getVar('IWbooks', 'any');

        $where = " WHERE pn_any = '$any' ";
        $orderBy = ' pn_etapa, pn_nivell, pn_codi_mat ';

        $items = DBUtil::selectObjectArray('IWbooks', $where, $orderBy);

        $export .= '"ID","' . $this->__('Autor') . '","' . $this->__('Title') . '","' . $this->__('Editorial') . '","' . $this->__('Release Year')
                . '","' . $this->__('ISBN') . '","' . $this->__('Mat') . '","' . $this->__('Pla') . '","' . $this->__('Level') . '","' . $this->__('Opt?')
                . '","' . $this->__('Lect?') . '","' . $this->__('Aval') . '","' . $this->__('Any') . '","' . $this->__('Comment') . '","' . $this->__('Materials')
                . '"' . chr(13) . chr(10);

        foreach ($items as $item) {
            $export .= '"' . $item['tid'] . '","' .
                    $item['autor'] . '","' .
                    $item['titol'] . '","' .
                    $item['editorial'] . '","' .
                    $item['any_publi'] . '","' .
                    $item['isbn'] . '","' .
                    $item['codi_mat'] . '","' .
                    $item['etapa'] . '","' .
                    $item['nivell'] . '","' .
                    $item['optativa'] . '","' .
                    $item['lectura'] . '","' .
                    $item['avaluacio'] . '","' .
                    $item['any'] . '","' .
                    $item['observacions'] . '","' .
                    $item['materials'] . '"' .
                    chr(13) . chr(10);
        }

        header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=llibres$any.csv");
        echo $export;

        return SessionUtil::setVar('statusmsg', "S'ha realitzat l'exportació del llibres de l'any $any al fitxer 'llibres$any.csv'");
    }

    public function copia_prev() {
        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADD)) {
            return DataUtil::formatForDisplayHTML($this->__('Sorry! No authorization to access this module.'));
        }

        $view = Zikula_View::getInstance('IWbooks', false);

        $view->assign('any', ModUtil::getVar('IWbooks', 'any'));
        $view->assign('noucurs', ModUtil::getVar('IWbooks', 'any') + 1);

        return $view->fetch('IWbooks_admin_copi_prev.htm');
    }

    public function copia_tot($args) {
        extract($args);
        $any = FormUtil::getPassedValue('any');
        $noucurs = FormUtil::getPassedValue('noucurs');

        $where = " WHERE pn_any = '$any' ";
        $orderBy = "";

        $items = DBUtil::selectObjectArray('IWbooks', $where, $orderBy);

        $total = 0;
        foreach ($items as $item) {
            $total++;
            $item['any'] = $noucurs;
            $result = DBUtil::insertObject($item, 'IWbooks', 'tid');
        }

        SessionUtil::setVar('statusmsg', "S'ha copiat la totalitat dels llibres ($total) de l'any " . $any . " a l'any " . $noucurs);

        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
    }

}