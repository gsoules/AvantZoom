<?php

class ImageZoom
{
    public static function emitZoomScript($identifier, $zoomDataProperties)
    {
        $zoomDataPath = self::getZoomDataPath($identifier);

        $tileSources = '[';
        foreach ($zoomDataProperties as $property)
        {
            $tileSources .= '{' . PHP_EOL;
            $tileSources .= 'type: "zoomifytileservice",' . PHP_EOL;
            $tileSources .= 'width: ' . $property['width'] . ',' . PHP_EOL;
            $tileSources .= 'height: ' . $property['height'] . ',' . PHP_EOL;
            $tileSources .= 'tilesUrl: "' . $property['url'] . '"' . PHP_EOL;
            $tileSources .= '},' . PHP_EOL;

        }
        $tileSources .= ']';

        $collectionOption = '';
        $tileSourceCount = count($zoomDataProperties);
        if ($tileSourceCount >= 2)
        {
            if ($tileSourceCount > 24)
                $rows = $tileSourceCount / 7;
            else if ($tileSourceCount > 16)
                $rows = 4;
            else if ($tileSourceCount > 8)
                $rows = 3;
            else if ($tileSourceCount > 4)
                $rows = 2;
            else
                $rows = 1;

            $collectionOption .= 'sequenceMode: false,' . PHP_EOL;
            $collectionOption .= 'collectionMode: true,' . PHP_EOL;
            $collectionOption .= 'collectionRows: ' . $rows . ',' . PHP_EOL;
            $collectionOption .= 'collectionTileSize: 1024,' . PHP_EOL;
            $collectionOption .= 'collectionTileMargin: 256,' . PHP_EOL;
        }

        $script = '
            var viewer = OpenSeadragon({
            //debugMode: true,
            id: "openseadragon",
            showNavigator: true,
            prefixUrl: "' . $zoomDataPath . 'images/",' .
            $collectionOption . '
            tileSources: ' . $tileSources . '
            })';

        return $script;
    }

    public static function generateOpenSeadragonViewer($item)
    {
        $dependentPluginsActive = plugin_is_active('AvantZoom');

        $identifier = ItemView::getItemIdentifier($item);
        $zoomDataSources = array();

        if (count($item->Files) >= 1 && $dependentPluginsActive)
        {
            $zoomDataSources = self::getZoomDataSources($identifier);
        }

        if (count($zoomDataSources) >= 1)
        {
            queue_js_file('openseadragon.min');
        }
        echo head(array('title' => metadata($item, array('Dublin Core', 'Title')), 'bodyclass' => 'items show'));

        $type = Custom::getItemBaseType($item);
        $class = empty($type) ? '' : " class=\"$type\"";
        echo '<h1' . $class . '>' . metadata($item, array('Dublin Core', 'Title'), array('no_filter' => true)) . '</h1>';

        $viewerScript = '';
        $zoomingEnabled = count($zoomDataSources) >= 1;
        if ($zoomingEnabled)
        {
            $viewerScript = self::emitZoomScript($identifier, $zoomDataSources);
            echo '<div id="openseadragon"></div>';
        }
        return $viewerScript;
    }

    public static function getZoomDataDirName($identifier)
    {
        return FILES_DIR . DIRECTORY_SEPARATOR . 'zoom' . DIRECTORY_SEPARATOR . $identifier;
    }

    public static function getZoomDataPath($identifier)
    {
        $currentPagePath = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/');
        return "/$currentPagePath/files/zoom/";
    }

    public static function getZoomDataProperties($dirName, $url)
    {
        $xmlFileName = $dirName . DIRECTORY_SEPARATOR . 'ImageProperties.xml';
        $xml = simplexml_load_file($xmlFileName);

        if ($xml)
        {
            $width = (string)$xml[0]['WIDTH'];
            $height = (string)$xml[0]['HEIGHT'];
            return array('url' => $url, 'width' => $width, 'height' => $height);
        }
        return null;
    }

    public static function getZoomDataSources($identifier)
    {
        $sources = array();
        if (empty($identifier))
            return $sources;

        $dirName = self::getZoomDataDirName($identifier);
        $pathName = self::getZoomDataPath($identifier) . $identifier . '/';

        $xmlFileName = $dirName . DIRECTORY_SEPARATOR . 'ImageProperties.xml';
        if (file_exists($xmlFileName))
        {
            // There is a single folder of tiles for one image.
            $sources[] = self::getZoomDataProperties($dirName, $pathName);
        }
        else
        {
            // There is a folder of folders of tiles containing multiple images for a single item.
            $dirs = glob($dirName . DIRECTORY_SEPARATOR . '*');

            foreach ($dirs as $dirName)
            {
                if (is_dir($dirName))
                {
                    $properties = self::getZoomDataProperties($dirName, $pathName . basename($dirName) . '/');
                    if ($properties)
                        $sources[] = $properties;
                }
            }
        }

        return $sources;
    }
}