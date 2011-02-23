<?php
class IWbooks_Version extends Zikula_Version
{
    public function getMetaData() {
        $meta = array();
        $meta['displayname'] = $this->__("Llibres");
        $meta['description'] = $this->__("Llibres de text, lectures i materials");
        $meta['url'] = $this->__("IWbooks");
        $meta['version'] = '3.0.0';
        $meta['securityschema'] = array('iw_books::Item' => 'iw_books item name::iw_books item ID');
        /*
        $meta['dependencies'] = array(array('modname' => 'IWmain',
                                            'minversion' => '3.0.0',
                                            'maxversion' => '',
                                            'status' => ModUtil::DEPENDENCY_REQUIRED));
         *
         */
        return $meta;
    }

}