<?php

include_once("arc/ARC2.php");
include_once("Graphite.php");

class SKOS_Manager
{
    public static $graph;

    public static function getChildren($source, $uri)
    {
        self::init($source);
        $children = self::$graph->resource($uri)->all("-skos:broader");
        $children = $children->union(self::$graph->resource($uri)->all("skos:narrower"));

        return $children;
    }

    public static function clearCache(){
        self::emptyDirectory(sys_get_temp_dir() . '/organic_skos/');

    }

    private static function init($source)
    {
        mkdir(sys_get_temp_dir() . '/organic_skos/');
        $temp_file = sys_get_temp_dir() . '/organic_skos/' . md5($source);

        if (!file_exists($temp_file)) {
            self::$graph->load($source);
            self::$graph->freeze($temp_file);
        } else{
            self::$graph = Graphite::thaw($temp_file);

            if(count(self::$graph->allSubjects())<1){
                self::$graph->load($source);
                self::$graph->freeze($temp_file);}
            }
    }

    private static function emptyDirectory($dirname) {
    if (is_dir($dirname))
           $dir_handle = opendir($dirname);
     if (!$dir_handle)
          return false;
     while($file = readdir($dir_handle)) {
           if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file))
                     unlink($dirname."/".$file);
                else
                     delete_directory($dirname.'/'.$file);
           }
     }
     closedir($dir_handle);
     
     return true;
    }


    private static function getAncestryPath($resource){

        $parents = $resource->all("skos:broader")->union($resource->all("-skos:narrower"));

        foreach ($parents as $parent) {
            $parents = $parents->union(self::getAncestryPath($parent));
        }

        return $parents;

    }

    public static function search($source, $term){
        self::init($source);

        $concepts = self::$graph->allOfType("skos:Concept");
        $found = new Graphite_ResourceList(self::$graph);

        foreach ($concepts as $concept) {
            if(strstr($concept->label(), $term, true)!== FALSE){
                $found = $found->union($concept)->union(self::getAncestryPath($concept));

            }
        }

        return $found;

    }


    public static function hasChildren($resource)
    {
        return $resource->has("-skos:broader") || $resource->has("skos:narrower");
    }

    public static function getLabels($source){
        self::init($source);
        return self::$graph->allOfType("skos:Concept")->label();
    }


    public static function getRoots($source)
    {
        self::init($source);

        $concepts = self::$graph->allOfType("skos:Concept");
        $children = $concepts->all("skos:narrower");
        $parented = $concepts->all("-skos:broader");



        $topConcepts= $concepts->except($children)->except($parented);



        return $topConcepts;
    }


    public static  function getConcept($source, $uri){
        self::init($source);
        return self::$graph->resource($uri);
    }

    /**
     * Extracts the namespace prefix out of a URI.
     *
     * @param	String	$uri
     * @return	string
     * @access	public
     */
    public static  function guessNamespace($uri) {
        $l = self::getNamespaceEnd($uri);
        return $l > 1 ? substr($uri ,0, $l) : "";
    }

    /**
     * Delivers the name out of the URI (without the namespace prefix).
     *
     * @param	String	$uri
     * @return	string
     * @access	public
     */
    public static  function guessName($uri) {
        return substr($uri,self::getNamespaceEnd($uri));
    }


    /**
     * Position of the namespace end
     * Method looks for # : and /
     * @access	private
     */
    static  function getNamespaceEnd($uri) {
        $l = strlen($uri)-1;
        do {
            $c = substr($uri, $l, 1);
            if($c == '#' || $c == ':' || $c == '/')
                break;
            $l--;
        } while ($l >= 0);
        $l++;
        return $l;
    }




}

SKOS_Manager::$graph = new Graphite();
