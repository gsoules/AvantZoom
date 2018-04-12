<?php

class AvantZoomPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'after_delete_record',
        'public_head'
    );

    protected $_filters = array(
    );

    public function hookAdminHead($args)
    {
        $this->head();
    }

    public function hookAfterDeleteRecord($args)
    {
        $item = $args['record'];

        // This code is only for Item objects, but it gets called when other kinds of records get deleted
        // such as an item's search_text table record. Ignore those other objects.
        if (!($item instanceof Item))
            return;

        $identifier = ItemView::getItemIdentifier($item);
        $zoomDataDirName = ImageZoom::getZoomDataDirName($identifier);
        if (file_exists($zoomDataDirName))
        {
            ImageZoom::removeDirectory($zoomDataDirName);
        }
    }

    public function hookPublicHead($args)
    {
        $this->head();
    }

    protected function head()
    {
        queue_css_file('avantzoom');
    }
}
