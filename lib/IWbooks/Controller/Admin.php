<?php

class IWbooks_Controller_Admin extends Zikula_AbstractController {

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

        // Create output object
        $view = Zikula_View::getInstance('IWbooks', false);

        $view->assign('anyini', ModUtil::getVar('IWbooks', 'any'));

        $aplans = ModUtil::apiFunc('IWbooks', 'user', 'plans', array('tots' => false));
        $view->assign('aplans', $aplans);

        $anivells = ModUtil::apiFunc('IWbooks', 'user', 'nivells', array('blanc' => true));
        $view->assign('anivells', $anivells);

        $amateries = ModUtil::apiFunc('IWbooks', 'user', 'materies', array('nou' => 1));
        $view->assign('amateries', $amateries);


        $avaluacions = array('' => '---',
            '1' => '1a',
            '2' => '2a',
            '3' => '3a');

        $view->assign('avaluacions', $avaluacions);

        return $view->fetch('IWbooks_admin_new.htm');
    }

    public function create($args) {
        list($name,
                $number) = FormUtil::getPassedValue('name', 'number');

        extract($args);

        if (!SecurityUtil::confirmAuthKey()) {
            SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
            System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
            return true;
        }

        $tid = ModUtil::apiFunc('IWbooks', 'admin', 'create', array('name' => $name, 'number' => $number));

        if ($tid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The new book has been created') . $codi_mat);
        }

        System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));

        // Return
        return true;
    }

    public function modify($args) {
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

        //$view->assign('tid',$item['tid']);
        $view->assign($item);

        return $view->fetch('IWbooks_admin_modify.htm');
    }

    public function update($args) {
        $item = FormUtil::getPassedValue('item');

        if (isset($args['objectid']) && !empty($args['objectid'])) {
            $item['tid'] = $args['objectid'];
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWbooks', 'admin', 'view'));
        }

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

        if (!empty($objectid)) {
            $tid = $objectid;
        }

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

        if (!SecurityUtil::confirmAuthKey()) {
            SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
            System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
            return true;
        }

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

        System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));

        return true;
    }

    /**
     * Veure llibres
     */
    public function view() {
        include_once('pnfpdf.php');

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

        //$view = Zikula_View::getInstance('IWbooks');

        $aanys = ModUtil::apiFunc('IWbooks', 'user', 'anys');
        asort($aanys);
        $view->assign('aanys', $aanys);

        /* if ($any == '') {
          $view->assign('cursselec', ModUtil::getVar('IWbooks', 'any'));
          } */


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
            if ($items[$key]['lectura'] == 1) {
                $items[$key]['lectura'] = "Sí";
            } else {
                $items[$key]['lectura'] = "No";
            }

            if ($items[$key]['optativa'] == 1) {
                $items[$key]['optativa'] = "Sí";
            } else {
                $items[$key]['optativa'] = "No";
            }

            if ($items[$key]['materials'] != "") {
                $items[$key]['materials'] = "x";
            } else {
                $items[$key]['materials'] = "-";
            }

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

        if (!SecurityUtil::confirmAuthKey()) {
            SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
            System::redirect(ModUtil::url('IWbooks', 'admin', 'modifyconfig'));
            return true;
        }

        if (!(file_exists($fpdf . 'fpdf.php'))) {
            SessionUtil::setVar('errormsg', "El camí per a la biblioteca 'fpdf' no és correcte: '" . $fpdf . "'");
            System::redirect(ModUtil::url('IWbooks', 'admin', 'modifyconfig'));
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

        ModUtil::setVar('IWbooks', 'any', $any);
        ModUtil::setVar('IWbooks', 'itemsperpage', $itemsperpage);
        ModUtil::setVar('IWbooks', 'fpdf', $fpdf);
        ModUtil::setVar('IWbooks', 'encap', $encap);
        ModUtil::setVar('IWbooks', 'darrer_nivell', $darrer_nivell);
        ModUtil::setVar('IWbooks', 'nivells', $nivells);
        ModUtil::setVar('IWbooks', 'plans', $plans);
        ModUtil::setVar('IWbooks', 'mida_font', $mida_font);
        ModUtil::setVar('IWbooks', 'llistar_materials', $llistar_materials);
        ModUtil::setVar('IWbooks', 'marca_aigua', $marca_aigua);

        SessionUtil::setVar('statusmsg', $this->__('Configuration correctly updated'));
        System::redirect(ModUtil::url('IWbooks', 'admin', 'modifyconfig'));

        return true;
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





        /*

          list($tid,
          $objectid)= FormUtil::getPassedValue('tid',
          'objectid');

          extract($args);

          if (!empty($objectid)) {
          $tid = $objectid;
          }

          $output =& new pnHTML();

          if (!ModUtil::loadApi('IWbooks', 'user')) {
          $output->Text($this->__('Error! Could not load module.'));
          return $output->GetOutput();
          }

          $item = ModUtil::apiFunc('IWbooks',
          'user',
          'get',
          array('tid' => $tid));

          if ($item == false) {
          $output->Text(_LLIBRESNOSUCHITEM);
          return $output->GetOutput();
          }

          if (!SecurityUtil::checkPermission*(0, 'IWbooks::Item', "$item[name]::$tid", ACCESS_EDIT)) {
          $output->Text($this->__('You are not allowed to enter this module'));
          return $output->GetOutput();
          }

          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->Text(IWbooks_adminmenu());
          $output->SetInputMode(_PNH_PARSEINPUT);

          $output->Title($this->__('Copy the book'));

          $output->FormStart(ModUtil::url('IWbooks', 'admin', 'create'));

          $output->FormHidden('authid', SecurityUtil::generateAuthKey());

          $output->FormHidden('tid', DataUtil::formatForDisplay($tid));

          $output->TableStart();

          // Autor
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Author')));
          $row[] = $output->FormText('autor', DataUtil::formatForDisplay($item['autor']), 50, 50);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // T�tol
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Title')));
          $row[] = $output->FormText('titol', DataUtil::formatForDisplay($item['titol']), 50, 50);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Editorial
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Editorial')));
          $row[] = $output->FormText('editorial', DataUtil::formatForDisplay($item['editorial']), 50, 50);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Any de publicaci�
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Release Year')));
          $row[] = $output->FormText('any_publi', DataUtil::formatForDisplay($item['any_publi']), 4, 4);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // ISBN
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('ISBN')));
          $row[] = $output->FormText('isbn', DataUtil::formatForDisplay($item['isbn']), 20, 20);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Codi de mat�ria
          $data0 = ModUtil::apiFunc('IWbooks', 'user', 'materies', array('nou' => '1'));
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Subject')));
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
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Academic Year')));
          $row[] = $output->FormText('any', DataUtil::formatForDisplay($noucurs), 4, 4);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Etapa
          // Obtenim array amb els plans entrats
          $data = ModUtil::apiFunc('IWbooks', 'user', 'plansselec', array('etapa' => $item['etapa']));
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Plan (Key 'Ctrl' for choose more plans)')));
          $row[] = $output->FormSelectMultiple('etapa[]', $data, 1, 6);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Nivell
          // Obtenir array amb els plans possibles
          $data2 = ModUtil::apiFunc('IWbooks', 'user', 'nivells');
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Level')));
          $row[] = $output->FormSelectMultiple('nivell', $data2, 0, 1,DataUtil::formatForDisplay($item['nivell']));
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          //Optativa?
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Optional?')));
          $row[] = $output->FormCheckbox('optativa', $item['optativa']);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          //Lectura?
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Read?')));
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
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Evaluation (Read Only)')));
          $row[] = $output->FormSelectMultiple('avaluacio', $data3, 0, 1,DataUtil::formatForDisplay($item['avaluacio']));
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Observacions
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Comment')));
          $row[] = $output->FormText('obervacions', DataUtil::formatForDisplay($item['observacions']), 50, 100);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Complements (materials complementari)
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Home course materials (books, brushes ...)')));
          $row[] = $output->FormTextArea('materials', DataUtil::formatForDisplay($item['materials']), 5, 60);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          $output->TableEnd();

          // End form
          $output->Linebreak(2);
          $output->FormSubmit($this->__('Copy this book'));
          $output->FormEnd();

          return $output->GetOutput();
         */
    }

    public function new_mat() {
        if (!SecurityUtil::checkPermission('IWbooks::', '::', ACCESS_ADD)) {
            return DataUtil::formatForDisplayHTML($this->__('Sorry! No authorization to access this module.'));
        }

        $view = Zikula_View::getInstance('IWbooks', false);

        return $view->fetch('IWbooks_admin_new_mat.htm');


        /*
          include('pnfpdf.php');

          $output =& new pnHTML();

          if (!SecurityUtil::checkPermission*(0, 'IWbooks::Item', '::', ACCESS_ADMIN)) {
          $output->Text($this->__('You are not allowed to enter this module'));
          return $output->GetOutput();
          }

          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->Text(IWbooks_adminmenu());
          $output->SetInputMode(_PNH_PARSEINPUT);

          $output->Title($this->__('Enter new subject'));

          $output->FormStart(ModUtil::url('IWbooks', 'admin', 'create_mat'));

          $output->FormHidden('authid', SecurityUtil::generateAuthKey());

          $output->TableStart();

          // Codi mat�ria
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Code subject')));
          $row[] = $output->FormText('codimat', '', 3, 3);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          // Mat�ria
          $row = array();
          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $row[] = $output->Text(DataUtil::formatForDisplay($this->__('Subject')));
          $row[] = $output->FormText('materia', '', 50, 50);
          $output->SetOutputMode(_PNH_KEEPOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->TableAddrow($row, 'left');
          $output->SetInputMode(_PNH_PARSEINPUT);

          $output->TableEnd();

          // End form
          $output->Linebreak(2);
          $output->FormSubmit($this->__('Enter new subject'));
          $output->FormEnd();

          return $output->GetOutput();

         */
    }

    public function create_mat($args) {
        $item = FormUtil::getPassedValue('item');

        if (!SecurityUtil::checkPermission('IWbooks::', "$item[materia]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWbooks', 'admin', 'view_mat'));
        }

        $tid = ModUtil::apiFunc('IWbooks', 'admin', 'create_mat', array('item' => $item));
        if ($tid) {
            // Success
            LogUtil::registerStatus($this->__('Done! Item created.'));
        }

        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));


        /*

          list($name,
          $number) = FormUtil::getPassedValue('name',
          'number');
          extract($args);

          if (!SecurityUtil::confirmAuthKey()) {
          SessionUtil::setVar('errormsg', $this->__('Invalid 'authkey':  this probably means that you pressed the 'Back' button, or that the page 'authkey' expired. Please refresh the page and try again.'));
          System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));
          return true;
          }
          if (!ModUtil::loadApi('IWbooks', 'admin')) {
          SessionUtil::setVar('errormsg', $this->__('Error! Could not load module.'));
          return $output->GetOutput();
          }
          $tid = ModUtil::apiFunc('IWbooks',
          'admin',
          'create_mat',
          array('name' => $name,
          'number' => $number));
          if ($tid != false) {
          // Success
          SessionUtil::setVar('statusmsg', $this->__('The new suject has been created'));
          }

          System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));

          return true;
         */
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


        /* 	
         * 	$startnum = FormUtil::getPassedValue('startnum');


          $output =& new pnHTML();

          if (!SecurityUtil::checkPermission*(0, 'IWbooks::', '::', ACCESS_READ)) {
          $output->Text($this->__('You are not allowed to enter this module'));
          return $output->GetOutput();
          }

          $output->SetInputMode(_PNH_VERBATIMINPUT);
          $output->Text(IWbooks_adminmenu());
          $output->SetInputMode(_PNH_PARSEINPUT);

          $output->Title($this->__('Show subjects'));

          if (!ModUtil::loadApi('IWbooks', 'user')) {
          $output->Text($this->__('Error! Could not load module.'));
          return $output->GetOutput();
          }

          // $numelem = nombre d'elements que retorna la selecci� sol�licitada
          $numelem   = ModUtil::apiFunc('IWbooks', 'user', 'countitemsmat');

          $items = ModUtil::apiFunc('IWbooks',
          'user',
          'getall_mat',
          array('startnum' => $startnum,
          'codi_mat' => $codi_mat,
          'materia'  => $materia,
          'numitems' => ModUtil::getVar('IWbooks',
          'itemsperpage')));

          $cap = array($this->__('Code subject'),
          $this->__('Subject'));

          if (SecurityUtil::checkPermission*(0, 'IWbooks::', "$item[autor]::$item[tid]", ACCESS_ADMIN)) {
          $cap[] = $this->__('Options');
          }

          $output->TableStart('', $cap, 3);

          foreach ($items as $item) {

          if (SecurityUtil::checkPermission*(0, 'IWbooks::', "$item[titol]::$item[tid]", ACCESS_OVERVIEW)) {

          $row = array();
          $row[] = $item['codi_mat'];
          $row[] = $item['materia'];

          $output->SetOutputMode(_PNH_RETURNOUTPUT);
          $output->SetInputMode(_PNH_VERBATIMINPUT);

          if (SecurityUtil::checkPermission*(0, 'IWbooks::', "$item[autor]::$item[tid]", ACCESS_ADMIN)) {
          if ($item['codi_mat'] != "TOT"){
          $options = array();
          $options[] = $output->URL(DataUtil::formatForDisplay(
          ModUtil::url('IWbooks',
          'admin',
          'modify_mat',
          array('tid' => $item['tid']))),
          '<img src="modules/IWbooks/pnimages/edit.gif" alt="'.$this->__('Edit').'" title="'.$this->__('Edit').'">');

          if (SecurityUtil::checkPermission*(0, 'IWbooks::', "$item[name]::$item[tid]", ACCESS_ADMIN)) {
          $options[] = $output->URL(DataUtil::formatForDisplay(
          ModUtil::url('IWbooks',
          'admin',
          'delete_mat',
          array('tid' => $item['tid']))),
          '<img src="modules/IWbooks/pnimages/edit_remove.gif" alt="'.$this->__('Delete').'" title="'.$this->__('Delete').'">');
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
          ModUtil::url('IWbooks',
          'admin',
          'view_mat',
          array('startnum' => '%%')),
          ModUtil::getVar('IWbooks', 'itemsperpage'));
          $output->Text('  (Total: '.$numelem.' mat�ries)');

          // Return the output that has been generated by this function
          return $output->GetOutput();


         */
    }

    public function delete_mat($args) {
        list($tid,
                $objectid,
                $confirmation) = FormUtil::getPassedValue('tid', 'objectid', 'confirmation');

        extract($args);
        if (!empty($objectid)) {
            $tid = $objectid;
        }

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

        if (!SecurityUtil::confirmAuthKey()) {
            SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
            System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));
            return true;
        }
        if (!ModUtil::loadApi('IWbooks', 'admin')) {
            $output->Text($this->__('Error! Could not load module.'));
            return $output->GetOutput();
        }

        if (ModUtil::apiFunc('IWbooks', 'admin', 'delete_mat', array('tid' => $tid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The subject has been cleared'));
        }
        System::redirect(ModUtil::url('IWbooks', 'admin', 'view_mat'));

        // Return
        return true;
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

        $view = Zikula_View::getInstance('IWbooks', false);
        $view->caching = false;

        //$view->assign('tid',$item['tid']);
        $view->assign($item);


        return $view->fetch('IWbooks_admin_modify_mat.htm');
    }

    public function update_mat($args) {
        $item = FormUtil::getPassedValue('item');
        if (isset($args['objectid']) && !empty($args['objectid'])) {
            $item['tid'] = $args['objectid'];
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWbooks', 'admin', 'view_mat'));
        }

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

        SessionUtil::setVar('statusmsg', "S'ha realitzat l'exportació del llibres de l'any $any al fitxer 'llibres$any.csv'");

        return true;
        //    return System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
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


        //$items = ModUtil::apiFunc('IWbooks', 'user', 'getall');

        $where = " WHERE pn_any = '$any' ";
        $orderBy = "";

        $items = DBUtil::selectObjectArray('IWbooks', $where, $orderBy);

        $total = 0;
        foreach ($items as $item) {
            $total++;
            $item['any'] = $noucurs;
            $result = DBUtil::insertObject($item, 'IWbooks', 'tid');
        }

        //SessionUtil::setVar('errormsg', "S'ha copiat la totalitat dels llibres ($total) de l'any ".$any." a l'any ".$noucurs);
        SessionUtil::setVar('statusmsg', "S'ha copiat la totalitat dels llibres ($total) de l'any " . $any . " a l'any " . $noucurs);

        return System::redirect(ModUtil::url('IWbooks', 'admin', 'view'));
    }

}