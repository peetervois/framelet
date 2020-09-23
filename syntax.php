<?php
/**
 * DokuWiki Plugin framelet (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Peeter Vois <peeter@tauria.ee>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

require_once 'framemaker.php';

require_once 'vendor/lz-string-php/src/LZCompressor/LZContext.php';
require_once 'vendor/lz-string-php/src/LZCompressor/LZData.php';
require_once 'vendor/lz-string-php/src/LZCompressor/LZReverseDictionary.php';
require_once 'vendor/lz-string-php/src/LZCompressor/LZString.php';
require_once 'vendor/lz-string-php/src/LZCompressor/LZUtil.php';
require_once 'vendor/lz-string-php/src/LZCompressor/LZUtil16.php';

/**
 * Simple helper to debug to the console
 *
 * @param $data object, array, string $data
 * @param $context string  Optional a description.
 *
 * @return string
 *
function debug_to_console($data, $context = 'Debug in Console') {
    
    // Buffering to solve problems frameworks, like header() in this and not a solid return.
    ob_start();
    
    $output  = 'console.info(\'' . $context . ':\');';
    $output .= 'console.log(' . json_encode($data) . ');';
    $output  = sprintf('<script>%s</script>', $output);
    
    echo $output;
}
*/

class syntax_plugin_framelet extends DokuWiki_Syntax_Plugin
{
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 200; // FIXME: what is the best numeber?
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
       $this->Lexer->addEntryPattern('<framelet\b.*?>', $mode, 'plugin_framelet');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</framelet>', 'plugin_framelet');
    }

    protected $iframe_params_default = ' style="min-width:90%; min-height:300px; background-color:lightcoral;" ';
    protected $iframe_href_default = "lib/plugins/framelet/information.html";
    protected $iframe_params = ' style="min-width:90%; min-height:300px; background-color:lightcoral;" ';
    protected $iframe_href = "lib/plugins/framelet/information.html";
    protected $iframe_counter = 0;
    protected $iframe_width_default = 0;
    protected $iframe_height_default = 300;
    protected $iframe_width = 0;
    protected $iframe_height = 300;
    
    /**
     * Handle matches of the framelet syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        // bypassing the data to renderer
        $data = array();
        
        $data["render"] = false;
        
        if( $state == DOKU_LEXER_ENTER){
            $this->iframe_params = $this->iframe_params_default;
            $this->iframe_href = $this->iframe_href_default;
            $this->iframe_width = $this->iframe_width_default;
            $this->iframe_height = $this->iframe_height_default;
            
            $this->iframe_counter ++;
            $keywords = preg_split("/[\s>]+/", $match);
            $res = ' style=" ';
            foreach( $keywords as $kw ){
                $nv = explode("=",$kw);
                if( $nv[0] == 'width' ){
                    $res .= "min-width:" .$nv[1]. "; ";
                    $unit = "";
                    sscanf(  $nv[1], "%d%s", $this->iframe_width, $unit );
                    if( $unit != "px" ){
                        // do not autoscale
                        $this->iframe_width = 0;
                    }
                }
                elseif( $nv[0] == 'height' ){
                    $res .= "min-height:" .$nv[1]. "; ";
                    $unit = "";
                    sscanf(  $nv[1], "%d%s", $this->iframe_height, $unit );
                    if( $unit != "px" ){
                        // do not autoscale
                        $this->iframe_height = 0;
                    }
                }
                elseif( $nv[0] == 'scale' ){
                    $res .= "-webkit-transform:scale(" .$nv[1]. "); ";
                    $res .= "-webkit-transform-origin: 0 0;";
                }
                elseif( $nv[0] == 'href' ){
                    $this->iframe_href = $nv[1];
                }
            }
            if( sizeof( $keywords )>2 )
                $this->iframe_params = $res .' " ';
        }
        
        
        if( $state == DOKU_LEXER_UNMATCHED ){
            $data["database"] = \LZCompressor\LZString::compressToEncodedURIComponent($match);
            $data["render"] = true;
            $data["divid"] = "framelet".$this->iframe_counter;
            $data["iframe_params"] = base64_encode( $this->iframe_params );
            $data["iframe_href"] = $this->iframe_href;
            $data["framewidth"] = $this->iframe_width;
            $data["frameheight"] = $this->iframe_height;
        }
        
        if( $state == DOKU_LEXER_EXIT ){
            $this->iframe_params = $this->iframe_params_default;
            $this->iframe_href = $this->iframe_href_default;
            $this->iframe_width = $this->iframe_width_default;
            $this->iframe_height = $this->iframe_height_default;
        }
        
        $data += array(
            'bytepos_start' => $pos,
            'bytepos_end'   => $pos + strlen($match)
        );

        
        return $data ;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        global $INFO;
        
        if ($mode !== 'xhtml') {
            return false;
        }
        if( ! $data['render'] ){
            return false;
        }
        
        $sectionEditData = [
            'target' => 'plugin_framelet',
            //'name' => $data['divid'],
            'iframeparams' => $data['iframe_params'],
            'iframedivid' => $data['divid'],
            'database' => $data["database"],
            'iframehref' => $data['iframe_href'],
            'bytepos_start' => $data['bytepos_start'],
            'bytepos_end' => $data['bytepos_end'],
            'rev' => $INFO['lastmod'],
            'framewidth' => $data['framewidth'],
            'frameheight' => $data['frameheight']
        ];
        
        
        $class = $renderer->startSectionEdit($data['bytepos_start'], $sectionEditData);
        $renderer->doc .= '<div class="' . $class . '">';
        
        $renderer->doc .= framemaker($sectionEditData);
        
        $renderer->doc .= '</div>';
        $renderer->finishSectionEdit($data['bytepos_end']);
        return true;
    }
}

?>