<?php
 
namespace Custom\Models;
use RightNow\Connect\v1_2 as RNCPHP;

use \RightNow\Libraries\ThirdParty\SimpleHtmlDom;
require_once CPCORE . 'Libraries/ThirdParty/SimpleHtmlDom.php';
 
class XMLProcessor extends \RightNow\Models\Base
{
	
	function __construct()
    {
        parent::__construct();
    }
	
    public function process()
    {
        //Grab XML and parse it. An HTML DOM is essentially XML
        //We can pretend our XML is HTML and use SimpleHtmlDom to parse it
        $fireflyXML = file_get_contents(APPPATH . 'libraries/firefly.xml');
        $fireflyDOM = SimpleHtmlDom\str_get_html($fireflyXML);
         
        echo "<h1>Firefly</h1>";
         
        //Grab and print Series Creator
        $createdBy = $fireflyDOM->find("createdBy", 0)->innertext;
        echo sprintf("<h2>Created By: %s</h2>", $createdBy);
         
        //Print number and title from each Episode
        $episodes = $fireflyDOM->find("episodes episode");
        foreach($episodes as $episode)
        {
            $episodeNumber = $episode->find("number", 0)->innertext;
            $episodeTitle = $episode->find("title", 0)->innertext;
             
            echo sprintf("<p><strong>Number:</strong> %s <strong>Title:</strong> %s</p>", $episodeNumber, $episodeTitle);
        }
    }
}