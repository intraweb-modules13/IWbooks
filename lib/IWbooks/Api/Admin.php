<?php

class IWbooks_Api_Admin extends Zikula_AbstractApi {

    public function create($args) {
        $dom = ZLanguage::getModuleDomain('IWbooks');
        $item = FormUtil::getPassedValue('item');
        $item[etapa] = implode("|", $item[etapa]);

        if (!isset($item['lectura']))
            $item['lectura'] = 0;

        if (!isset($item['optativa']))
            $item['optativa'] = 0;

        if (!SecurityUtil::checkPermission('IWbooks::Item', "$autor::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $result = DBUtil::insertObject($item, 'IWbooks', 'tid');
        if (!$result) {
            return LogUtil::registerError(__('Error! Creation attempt failed.', $dom));
        }

        ModUtil::callHooks('item', 'create', $item['tid'], array('module' => 'IWbooks'));

        return $item['tid'];
    }

// Esborrar llibre
    public function delete($args) {
        $dom = ZLanguage::getModuleDomain('IWbooks');
        extract($args);

        if (!isset($tid)) {
            SessionUtil::setVar('errormsg', __('Error! Could not do what you wanted. Please check your input.', $dom));
            return false;
        }

        $item = ModUtil::apiFunc('IWbooks', 'user', 'get', array('tid' => $tid));

        if ($item == false) {
            $output->Text(_LLIBRESNOSUCHITEM);
            return $output->GetOutput();
        }

        if (!SecurityUtil::checkPermission('IWbooks::Item', "$item[etapa]::$tid", ACCESS_DELETE)) {
            SessionUtil::setVar('errormsg', __('You are not allowed to enter this module', $dom));
            return false;
        }

        //$dbconn =& DBConnectionStack::getConnection*(true);
        $table = & DBUtil::getTables();

        $llibrestable = $table['IWbooks'];
        $llibrescolumn = &$table['IWbooks_column'];

        $sql = "DELETE FROM $llibrestable
            WHERE $llibrescolumn[tid] = '" . DataUtil::formatForStore($tid) . "'";
        $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            SessionUtil::setVar('errormsg', __('Error! Sorry! Deletion attempt failed.', $dom));
            return false;
        }

        ModUtil::callHooks('item', 'delete', $tid, '');

        return true;
    }

// Actualitzar llibre
    public function update($args) {
        $dom = ZLanguage::getModuleDomain('IWbooks');
        extract($args);

        $item = FormUtil::getPassedValue('item');
        $item[etapa] = implode("|", $item[etapa]);

        if (!isset($item['lectura'])) {
            $item['lectura'] = 0;
        }
        if (!isset($item['optativa'])) {
            $item['optativa'] = 0;
        }

        if (!SecurityUtil::checkPermission('IWbooks::Item', "$autor::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $result = DBUtil::updateObject($item, 'IWbooks', '', 'tid');

        if (!$result) {
            return LogUtil::registerError(__('Error! Creation attempt failed.', $dom));
        }

        ModUtil::callHooks('item', 'update', $item['tid'], array('module' => 'IWbooks'));
        echo "WWWWWWWWWWWWWWWWWWWWWWWWWWW !." . $item['tid'];
        return $item['tid'];
    }

// Crear nova matèria
    public function create_mat($args) {
        $dom = ZLanguage::getModuleDomain('IWbooks');
        //print_r ($args);
        $item = FormUtil::getPassedValue('item');


        if (!SecurityUtil::checkPermission('IWbooks::', "$item[materia]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        //$result = DBUtil::insertObject ($item, 'materies', 'tid');
        $result = DBUtil::insertObject($item, 'IWbooks_materies', 'tid');

        if (!$result) {
            return LogUtil::registerError(__('Error! Creation attempt failed.', $dom));
        }

        ModUtil::callHooks('item', 'create_mat', $item['tid'], array('module' => 'IWbooks'));

        // Return the id of the newly created item to the calling process
        return $item['tid'];
    }

// Esborrar matèria
    public function delete_mat($args) {
        $dom = ZLanguage::getModuleDomain('IWbooks');
        extract($args);

        if (!isset($tid)) {
            SessionUtil::setVar('errormsg', __('Error! Could not do what you wanted. Please check your input.', $dom));
            return false;
        }

        if (!ModUtil::loadApi('IWbooks', 'user')) {
            $output->Text(__('Error! Could not load module.', $dom));
            return $output->GetOutput();
        }

        $item = ModUtil::apiFunc('IWbooks', 'user', 'get_mat', array('tid' => $tid));

        if ($item == false) {
            $output->Text(_LLIBRESNOSUCHITEM);
            return $output->GetOutput();
        }

        if (!SecurityUtil::checkPermission('IWbooks::Item', "$item[name]::$tid", ACCESS_DELETE)) {
            SessionUtil::setVar('errormsg', __('You are not allowed to enter this module', $dom));
            return false;
        }

        //$dbconn =& DBConnectionStack::getConnection*(true);
        $table = & DBUtil::getTables();

        $materiestable = $table['materies'];
        $materiescolumn = &$table['materies_column'];

        $sql = "DELETE FROM $materiestable
            WHERE $materiescolumn[tid] = '" . DataUtil::formatForStore($tid) . "'";
        $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            SessionUtil::setVar('errormsg', __('Error! Sorry! Deletion attempt failed.', $dom));
            return false;
        }

        ModUtil::callHooks('item', 'delete', $tid, '');

        return true;
    }

// Actualitzar matèria
    public function update_mat($args) {

        $dom = ZLanguage::getModuleDomain('IWbooks');
        extract($args);

        $item = FormUtil::getPassedValue('item');


        if (!SecurityUtil::checkPermission('IWbooks::', "$item[materia]::$item[tid]", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $result = DBUtil::updateObject($item, 'IWbooks_materies', '', 'tid');

        if (!$result) {
            return LogUtil::registerError(__('Error! Update attempt failed.', $dom));
        }

        ModUtil::callHooks('item', 'update_mat', $item['tid'], array('module' => 'IWbooks'));

        return $item['tid'];
    }

}