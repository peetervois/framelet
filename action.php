<?php
/**
 * DokuWiki Plugin framelet (Action Component)
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

class action_plugin_framelet extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     *
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_tpl_metaheader_output');
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, '_editform');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, '_editpost');
    }

    /**
     * [Custom event handler which performs action]
     *
     * Called for event:
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     *
     * @return void
     */
    public function handle_tpl_metaheader_output(Doku_Event $event, $param)
    {
        $event->data['script'][] = array(
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data'   => '',
            'src' => DOKU_BASE."lib/plugins/framelet/script.js");
        $event->data['script'][] = array(
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data'   => '',
            'src' => DOKU_BASE."lib/plugins/framelet/vendor/lz-string/libs/lz-string.min.js");
     //   $event->data['script'][] = array(
     //       'type'    => 'text/javascript',
     //       'charset' => 'utf-8',
     //       '_data'   => '',
     //       'src' => DOKU_BASE."lib/plugins/framelet/vendor/js-base64/base64.js");
    }

    public function _editform(Doku_Event $event, $param) {
        global $TEXT;
        
        if ($event->data['target'] !== 'plugin_framelet') {
            return;
        }
        $event->preventDefault();
        
        // No default edit intro
        unset($event->data['intro_locale']);
        
        // No media manager fallback link
        // You will probably want a media link if you want a normal toolbar
        $event->data['media_manager'] = false;
        
        // FIXME: Create the lang files edit_intro.txt
        //echo $this->locale_xhtml('edit_intro');
        
        $form =& $event->data['form'];
        
        // Make fake edit form that can be driven from the framelet application
        $form->addHidden( "do", "cancel");
        // Prepare the data for framemaker
        $data = Array(
            'iframedivid' => $_POST['iframedivid'],
            'iframeparams' => $_POST['iframeparams'],
            'iframehref' => $_POST['iframehref'],
            'database' => \LZCompressor\LZString::compressToEncodedURIComponent($TEXT)
        );
        $form->addElement( frameedit($data) );
    }
    
    public function _editpost(Doku_Event $event) {
        // When we find inside POST the field 'B64JSON' we will decompress it
        if( !isset($_POST['B64JSON']) ) {
            return;
        }
        
        global $TEXT;
        
        // Create wikitext from post
        $TEXT = \LZCompressor\LZString::decompressFromEncodedURIComponent( $_POST['B64JSON'] );
    }
}

