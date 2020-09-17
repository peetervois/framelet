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

    protected $iframe_params = "";
    protected $iframe_href = "lib/plugins/framelet/test/index.html";
    
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
            $keywords = preg_split("/[\s>]+/", $match);
            $res = ' style=" ';
            foreach( $keywords as $kw ){
                $nv = explode("=",$kw);
                if( $nv[0] == 'width' ){
                    $res .= "min-width:" .$nv[1]. "; ";
                }
                elseif( $nv[0] == 'height' ){
                    $res .= "min-height:" .$nv[1]. "; ";
                }
                elseif( $nv[0] == 'scale' ){
                    $res .= "zoom:" .$nv[1]. "; ";
                    $res .= "-moz-transform:scale(" .$nv[1]. "); ";
                    $res .= "-moz-transform-origin: 0 0; ";
                    $res .= "-o-transform:scale(" .$nv[1]. "); ";
                    $res .= "-o-transform-origin: 0 0; ";
                    $res .= "-webkit-transform:scale(" .$nv[1]. "); ";
                    $res .= "-webkit-transform-origin: 0 0;";
                }
                elseif( $nv[0] == 'href' ){
                    $this->iframe_href = $nv[1];
                }
            }
            $this->iframe_params = $res .' " ';
        }
        
        
        if( $state == DOKU_LEXER_UNMATCHED ){
            $data["database"] = $match;
            $data["render"] = true;
            $data["divid"] = "framelet";
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
        if ($mode !== 'xhtml') {
            return false;
        }
        if( ! $data['render'] ){
            return false;
        }
        
        $sectionEditData = [
            'target' => 'plugin_framelet',
            //'name' => $data['divid'],
            'iframeparams' => base64_encode( $this->iframe_params ),
            'iframedivid' => $data['divid'],
            'database' => $data["database"],
            'iframehref' => $this->iframe_href,
            'bytepos_start' => $data['bytepos_start'],
            'bytepos_end' => $data['bytepos_end']
        ];
        $class = $renderer->startSectionEdit($data['bytepos_start'], $sectionEditData);
        $renderer->doc .= '<div class="' . $class . '">';
        
        $renderer->doc .= framemaker($sectionEditData);
        
        $renderer->doc .= '</div>';
        $renderer->finishSectionEdit($data['bytepos_end']);
        return true;
    }
}

